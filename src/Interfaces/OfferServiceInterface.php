<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface OfferServiceInterface
{
    public function getOfferById(int $offerId): ?array;
    public function getOffersByUserId(int $userId): array;
    public function createOffer(array $offerData): int;
    public function updateOffer(int $offerId, array $offerData): bool;
    public function deleteOffer(int $offerId): bool;
    public function searchOffers(array $filters): array;
    public function getOfferAnalytics(int $offerId): array;
    public function getOffersWithImages(int $limit = 20, int $offset = 0): array;
}