<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Variation
{
    public static function create(int $productId, string $name): int
    {
        $stmt = Database::connection()->prepare("INSERT INTO variations (product_id, name) VALUES (?, ?)");
        $stmt->execute([$productId, $name]);
        return Database::connection()->lastInsertId();
    }

    public static function getByProduct(int $productId): array
    {
        $stmt = Database::connection()->prepare("SELECT * FROM variations WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteByProduct(int $productId): void
    {
        $stmt = Database::connection()->prepare("SELECT id FROM variations WHERE product_id = ?");
        $stmt->execute([$productId]);
        $variations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($variations as $variation) {
            Stock::deleteByVariation($variation['id']);
        }

        $stmt = Database::connection()->prepare("DELETE FROM variations WHERE product_id = ?");
        $stmt->execute([$productId]);
    }

    public static function getIdByProductAndName(int $productId, string $name): ?int
    {
        $stmt = Database::connection()->prepare("SELECT id FROM variations WHERE product_id = ? AND name = ?");
        $stmt->execute([$productId, $name]);
        $variationId = $stmt->fetchColumn();
        return $variationId !== false ? (int)$variationId : null;
    }
}
