<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Product
{
    public static function all()
    {
        $stmt = Database::connection()->query("SELECT * FROM products");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$product) {
            $product['variations'] = Variation::getByProduct($product['id']);
        }

        return $products;
    }

    public static function find($id)
    {
        $stmt = Database::connection()->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['variations'] = Variation::getByProduct($id);
        }

        return $product;
    }

    public static function create($data)
    {
        $stmt = Database::connection()->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
        $stmt->execute([$data['name'], $data['price']]);
        return Database::connection()->lastInsertId();
    }

    public static function update($id, $data)
    {
        $stmt = Database::connection()->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
        return $stmt->execute([$data['name'], $data['price'], $id]);
    }

    public static function delete($id)
    {
        $stmt = Database::connection()->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
