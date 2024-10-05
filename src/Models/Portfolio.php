<?php

namespace Src\Models;

use Src\Database\Database;

class Portfolio extends User
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getItemsByUserId($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM portfolio_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addItem($userId, $itemData)
    {
        $stmt = $this->db->prepare("INSERT INTO portfolio_items (user_id, title, description, image_url) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $itemData['title'], $itemData['description'], $itemData['image_url']]);
    }

    public function updateItem($itemId, $itemData)
    {
        $stmt = $this->db->prepare("UPDATE portfolio_items SET title = ?, description = ?, image_url = ? WHERE id = ?");
        return $stmt->execute([$itemData['title'], $itemData['description'], $itemData['image_url'], $itemId]);
    }

    public function deleteItem($itemId)
    {
        $stmt = $this->db->prepare("DELETE FROM portfolio_items WHERE id = ?");
        return $stmt->execute([$itemId]);
    }

    public function getItemById($itemId)
    {
        $stmt = $this->db->prepare("SELECT * FROM portfolio_items WHERE id = ?");
        $stmt->execute([$itemId]);
        return $stmt->fetch();
    }

    public function getItemCount($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM portfolio_items WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    // You can add more portfolio-specific methods here
}