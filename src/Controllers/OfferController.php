<?php

namespace Src\Controllers;

use Src\Core\TwigRenderer;
use Src\Services\OfferService;
use Src\Services\AuthService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class OfferController extends BaseController
{
    protected OfferService $offerService;
    protected AuthService $authService;

    public function __construct(TwigRenderer $twigRenderer, OfferService $offerService, AuthService $authService)
    {
        parent::__construct($twigRenderer);
        $this->offerService = $offerService;
        $this->authService = $authService;
    }

    public function index(Request $request, Response $response): void
    {
        $offers = $this->offerService->getAllOffers();
        $this->render($response, 'offer/index', ['offers' => $offers]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->render($response, 'offer/create');
    }

    public function store(Request $request, Response $response): void
    {
        $user = $this->authService->getUser();
        $data = $request->post;
        $data['user_id'] = $user->id;

        $result = $this->offerService->createOffer($data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Offer created successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to create offer'], 500);
        }
    }

    public function view(Request $request, Response $response, array $args): void
    {
        $offer = $this->offerService->getOfferById($args['id']);
        if (!$offer) {
            $this->jsonResponse($response, ['error' => 'Offer not found'], 404);
            return;
        }
        $this->render($response, 'offer/view', ['offer' => $offer]);
    }

    public function edit(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $offer = $this->offerService->getOfferById($args['id']);

        if (!$offer) {
            $this->jsonResponse($response, ['error' => 'Offer not found'], 404);
            return;
        }

        if ($offer->user_id != $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['error' => 'Unauthorized'], 403);
            return;
        }

        $this->render($response, 'offer/edit', ['offer' => $offer]);
    }

    public function update(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $offer = $this->offerService->getOfferById($args['id']);

        if (!$offer) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Offer not found'], 404);
            return;
        }

        if ($offer->user_id != $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $data = $request->post;
        $result = $this->offerService->updateOffer($args['id'], $data);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Offer updated successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to update offer'], 500);
        }
    }

    public function delete(Request $request, Response $response, array $args): void
    {
        $user = $this->authService->getUser();
        $offer = $this->offerService->getOfferById($args['id']);

        if (!$offer) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Offer not found'], 404);
            return;
        }

        if ($offer->user_id != $user->id && !$user->isAdmin()) {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $result = $this->offerService->deleteOffer($args['id']);

        if ($result) {
            $this->jsonResponse($response, ['success' => true, 'message' => 'Offer deleted successfully']);
        } else {
            $this->jsonResponse($response, ['success' => false, 'message' => 'Failed to delete offer'], 500);
        }
    }
}
