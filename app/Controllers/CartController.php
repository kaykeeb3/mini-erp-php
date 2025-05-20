<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Coupon;

class CartController
{
    public function add()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $productId = isset($data['product']) ? (int)$data['product'] : null;
        $variationName = $data['variation'] ?? null;
        $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        if (!$productId || !$variationName || $quantity <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos']);
            return;
        }

        $variationId = $this->getVariationId($productId, $variationName);
        if (!$variationId) {
            http_response_code(404);
            echo json_encode(['error' => 'Variação não encontrada']);
            return;
        }

        $stockQuantity = Stock::getByVariation($variationId);

        if ($stockQuantity < $quantity) {
            http_response_code(400);
            echo json_encode(['error' => 'Estoque insuficiente']);
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $key = $productId . '-' . $variationName;

        if (isset($_SESSION['cart'][$key])) {
            $newQuantity = $_SESSION['cart'][$key]['quantity'] + $quantity;
            if ($newQuantity > $stockQuantity) {
                http_response_code(400);
                echo json_encode(['error' => 'Quantidade no carrinho ultrapassa o estoque disponível']);
                return;
            }
            $_SESSION['cart'][$key]['quantity'] = $newQuantity;
        } else {
            $productModel = new Product();
            $product = $productModel->find($productId);

            if (!$product) {
                http_response_code(404);
                echo json_encode(['error' => 'Produto não encontrado']);
                return;
            }

            $_SESSION['cart'][$key] = [
                'product_id' => $productId,
                'variation' => $variationName,
                'quantity' => $quantity,
                'name' => $product['name'],
                'price' => $product['price'],
            ];
        }

        echo json_encode([
            'message' => 'Produto adicionado ao carrinho',
            'cart' => $_SESSION['cart']
        ]);
    }

    public function show()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cart = $_SESSION['cart'] ?? [];
        $couponCode = $_SESSION['coupon_code'] ?? null;
        $coupon = null;
        $discountValue = 0;

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        if ($couponCode) {
            $couponModel = new Coupon();
            $coupon = $couponModel->findByCode($couponCode);

            if ($coupon) {
                if ($subtotal >= ($coupon['min_amount'] ?? 0) && $coupon['active']) {
                    if ($coupon['type'] === 'percentage') {
                        $discountValue = $subtotal * ($coupon['value'] / 100);
                    } else {
                        $discountValue = $coupon['value'];
                    }
                    if ($discountValue > $subtotal) {
                        $discountValue = $subtotal;
                    }
                } else {
                    unset($_SESSION['coupon_code']);
                    $couponCode = null;
                    $coupon = null;
                    $discountValue = 0;
                }
            } else {
                unset($_SESSION['coupon_code']);
                $couponCode = null;
            }
        }

        $shipping = $subtotal >= 200 ? 0 : 15;

        $total = $subtotal - $discountValue + $shipping;

        header('Content-Type: application/json');
        echo json_encode([
            'items' => array_values($cart),
            'subtotal' => round($subtotal, 2),
            'coupon' => $couponCode ? [
                'code' => $couponCode,
                'type' => $coupon['type'],
                'value' => $coupon['value'],
                'discount' => round($discountValue, 2),
            ] : null,
            'shipping' => round($shipping, 2),
            'total' => round($total, 2),
        ]);
    }

    private function getVariationId(int $productId, string $variationName): ?int
    {
        $db = \App\Core\Database::connection();

        $stmt = $db->prepare("SELECT id FROM variations WHERE product_id = ? AND name = ?");
        $stmt->execute([$productId, $variationName]);

        $variationId = $stmt->fetchColumn();

        return $variationId !== false ? (int)$variationId : null;
    }
}
