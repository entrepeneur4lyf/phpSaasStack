<?php

namespace Src\Models;

use Src\Core\Database;

class Seller extends User
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getSellerProfile($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM seller_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function createSellerProfile($userId, $businessName, $description)
    {
        $stmt = $this->db->prepare("INSERT INTO seller_profiles (user_id, business_name, description) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $businessName, $description]);
    }

    public function updateSellerProfile($userId, $businessName, $description)
    {
        $stmt = $this->db->prepare("UPDATE seller_profiles SET business_name = ?, description = ? WHERE user_id = ?");
        return $stmt->execute([$businessName, $description, $userId]);
    }

    public function getServices($sellerId)
    {
        $stmt = $this->db->prepare("SELECT * FROM seller_services WHERE seller_id = ?");
        $stmt->execute([$sellerId]);
        return $stmt->fetchAll();
    }

    public function updateServices($sellerId, $services)
    {
        // First, delete all existing services for this seller
        $stmt = $this->db->prepare("DELETE FROM seller_services WHERE seller_id = ?");
        $stmt->execute([$sellerId]);

        // Then, insert the new services
        $stmt = $this->db->prepare("INSERT INTO seller_services (seller_id, service_name, description, price) VALUES (?, ?, ?, ?)");
        foreach ($services as $service) {
            $stmt->execute([$sellerId, $service['name'], $service['description'], $service['price']]);
        }

        return true;
    }

    // Add more seller-specific methods as needed
}