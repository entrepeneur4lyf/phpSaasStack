<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\OfferService;
use App\Services\AuthService;

class OfferController extends BaseController
{
    protected $twig;
    protected $offerService;
    protected $authService;

    public function __construct(Environment $twig, OfferService $offerService, AuthService $authService)
    {
        $this->twig = $twig;
        $this->offerService = $offerService;
        $this->authService = $authService;
    }

    public function index()
    {
        $offers = $this->offerService->getAllOffers();
        return $this->twig->render('offer/index.twig', ['offers' => $offers]);
    }

    public function create()
    {
        return $this->twig->render('offer/create.twig');
    }

    public function store()
    {
        $user = $this->authService->getUser();
        $data = $this->request->getPost();
        $data['user_id'] = $user->id;

        $result = $this->offerService->createOffer($data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Offer created successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to create offer']);
        }
    }

    public function view($id)
    {
        $offer = $this->offerService->getOfferById($id);
        if (!$offer) {
            return $this->response->setStatusCode(404)->setBody('Offer not found');
        }
        return $this->twig->render('offer/view.twig', ['offer' => $offer]);
    }

    public function edit($id)
    {
        $user = $this->authService->getUser();
        $offer = $this->offerService->getOfferById($id);

        if (!$offer) {
            return $this->response->setStatusCode(404)->setBody('Offer not found');
        }

        if ($offer->user_id != $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        return $this->twig->render('offer/edit.twig', ['offer' => $offer]);
    }

    public function update($id)
    {
        $user = $this->authService->getUser();
        $offer = $this->offerService->getOfferById($id);

        if (!$offer) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Offer not found']);
        }

        if ($offer->user_id != $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $data = $this->request->getPost();
        $result = $this->offerService->updateOffer($id, $data);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Offer updated successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to update offer']);
        }
    }

    public function delete($id)
    {
        $user = $this->authService->getUser();
        $offer = $this->offerService->getOfferById($id);

        if (!$offer) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Offer not found']);
        }

        if ($offer->user_id != $user->id && !$user->isAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $result = $this->offerService->deleteOffer($id);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => 'Offer deleted successfully']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Failed to delete offer']);
        }
    }
}