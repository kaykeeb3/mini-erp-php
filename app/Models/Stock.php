<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Stock
{
    public static function create(int $variationId, int $quantity): void
    {
        $stmt = Database::connection()->prepare("INSERT INTO stock (variation_id, quantity) VALUES (?, ?)");
        $stmt->execute([$variationId, $quantity]);
    }

    public static function getByVariation(int $variationId): int
    {
        $stmt = Database::connection()->prepare("SELECT quantity FROM stock WHERE variation_id = ?");
        $stmt->execute([$variationId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public static function deleteByVariation(int $variationId): void
    {
        $stmt = Database::connection()->prepare("DELETE FROM stock WHERE variation_id = ?");
        $stmt->execute([$variationId]);
    }

    public static function updateQuantity(int $variationId, int $quantity): void
    {
        $stmt = Database::connection()->prepare("UPDATE stock SET quantity = ? WHERE variation_id = ?");
        $stmt->execute([$quantity, $variationId]);
    }

    public static function decrease(int $productId, string $variationName, int $quantity): void
    {
        $db = Database::connection();

        $stmt = $db->prepare("SELECT id FROM variations WHERE product_id = ? AND name = ?");
        $stmt->execute([$productId, $variationName]);
        $variationId = $stmt->fetchColumn();

        if (!$variationId) {
            throw new \Exception("Variação não encontrada");
        }

        $currentQty = self::getByVariation((int)$variationId);
        if ($currentQty < $quantity) {
            throw new \Exception("Estoque insuficiente");
        }

        self::updateQuantity((int)$variationId, $currentQty - $quantity);
    }

    public static function getStockByProductAndVariation(int $productId, string $variationName): ?array
    {
        $db = Database::connection();

        $stmt = $db->prepare("SELECT v.id, s.quantity FROM variations v
            LEFT JOIN stock s ON s.variation_id = v.id
            WHERE v.product_id = ? AND v.name = ?");
        $stmt->execute([$productId, $variationName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'variation_id' => $row['id'],
            'quantity' => (int)$row['quantity']
        ];
    }
}
