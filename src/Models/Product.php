<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Database\Database;

class Product
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO products (name, description, price, category_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['name'], $data['description'], $data['price'], $data['category_id']]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['description'], $data['price'], $data['category_id'], $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function addRelatedProduct(int $productId, int $relatedProductId): bool
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO related_products (product_id, related_product_id) VALUES (?, ?)");
        return $stmt->execute([$productId, $relatedProductId]);
    }

    public function removeRelatedProduct(int $productId, int $relatedProductId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM related_products WHERE product_id = ? AND related_product_id = ?");
        return $stmt->execute([$productId, $relatedProductId]);
    }

    public function getRelatedProducts(int $productId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM products p
            JOIN related_products rp ON p.id = rp.related_product_id
            WHERE rp.product_id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function addFAQ(int $productId, string $question, string $answer): bool
    {
        $stmt = $this->db->prepare("INSERT INTO product_faqs (product_id, question, answer) VALUES (?, ?, ?)");
        return $stmt->execute([$productId, $question, $answer]);
    }

    public function updateFAQ(int $faqId, string $question, string $answer): bool
    {
        $stmt = $this->db->prepare("UPDATE product_faqs SET question = ?, answer = ? WHERE id = ?");
        return $stmt->execute([$question, $answer, $faqId]);
    }

    public function deleteFAQ(int $faqId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_faqs WHERE id = ?");
        return $stmt->execute([$faqId]);
    }

    public function getFAQs(int $productId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM product_faqs WHERE product_id = ? ORDER BY created_at ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
}