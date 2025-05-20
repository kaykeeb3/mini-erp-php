<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Order
{
    public static function create(array $data): int
    {
        $db = Database::connection();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO orders
                (customer_email, cep, address, subtotal, shipping, discount, coupon_code, total, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $data['customer_email'],
                $data['cep'],
                $data['address'],
                $data['subtotal'],
                $data['shipping'],
                $data['discount'],
                $data['coupon_code'],
                $data['total']
            ]);

            $orderId = (int) $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_name, variation_name, quantity, price)
                VALUES (?, ?, ?, ?, ?)");

            foreach ($data['items'] as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product'],
                    $item['variation'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $db->commit();
            return $orderId;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function updateStatus(int $orderId, string $status): void
    {
        $db = Database::connection();
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
    }

    public static function delete(int $orderId): void
    {
        $db = Database::connection();

        try {
            $db->beginTransaction();

            $stmtItems = $db->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmtItems->execute([$orderId]);

            $stmtOrder = $db->prepare("DELETE FROM orders WHERE id = ?");
            $stmtOrder->execute([$orderId]);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function findById(int $orderId): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        $stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmtItems->execute([$orderId]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $order['items'] = $items;

        return $order;
    }
}
