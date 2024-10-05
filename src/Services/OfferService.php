<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\OfferServiceInterface;
use Src\Models\Offer;

class OfferService implements OfferServiceInterface
{
    public function __construct(
        private readonly Offer $offerModel
    ) {}

    public function getOfferById(int $offerId): ?array
    {
        return $this->offerModel->getById($offerId);
    }

    public function getOffersByUserId(int $userId): array
    {
        return $this->offerModel->getByUserId($userId);
    }

    public function createOffer(array $offerData): int
    {
        return $this->offerModel->create($offerData);
    }

    public function updateOffer(int $offerId, array $offerData): bool
    {
        return $this->offerModel->update($offerId, $offerData);
    }

    public function deleteOffer(int $offerId): bool
    {
        return $this->offerModel->delete($offerId);
    }

    public function searchOffers(array $filters): array
    {
        return $this->offerModel->search($filters);
    }

    public function getOfferAnalytics(int $offerId): array
    {
        return $this->offerModel->getAnalytics($offerId);
    }
}