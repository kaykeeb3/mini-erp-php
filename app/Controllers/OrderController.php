<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\Stock;
use App\Models\Coupon;
use App\Services\EmailService;

class OrderController
{
    public static function checkout()
    {
        session_start();

        $input = json_decode(file_get_contents('php://input'), true);
        $cep = $input['cep'] ?? ($_POST['cep'] ?? null);
        $email = $input['email'] ?? ($_POST['email'] ?? null);
        $couponCode = $input['coupon'] ?? ($_POST['coupon'] ?? null);

        if (!$cep || !$email) {
            http_response_code(400);
            echo json_encode(['error' => 'CEP e e-mail são obrigatórios']);
            exit;
        }

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            http_response_code(400);
            echo json_encode(['error' => 'O carrinho está vazio']);
            exit;
        }

        $subtotal = 0;

        foreach ($cart as $index => $item) {
            if (!isset($item['product'], $item['variation'], $item['quantity'], $item['price'])) {
                http_response_code(400);
                echo json_encode(['error' => "Item #$index inválido ou incompleto no carrinho"]);
                exit;
            }

            $productId = (int) $item['product'];
            $variation = $item['variation'];
            $quantity = (int) $item['quantity'];
            $price = (float) $item['price'];

            $stockInfo = Stock::getStockByProductAndVariation($productId, $variation);

            if (!$stockInfo) {
                http_response_code(400);
                echo json_encode(['error' => "Estoque não encontrado para o produto $productId com variação $variation"]);
                exit;
            }

            if ($stockInfo['quantity'] < $quantity) {
                http_response_code(400);
                echo json_encode(['error' => "Estoque insuficiente para o produto $productId com variação $variation"]);
                exit;
            }

            $subtotal += $price * $quantity;
        }

        // Regras de frete
        $shipping = 20;
        if ($subtotal >= 52 && $subtotal <= 166.59) $shipping = 15;
        if ($subtotal > 200) $shipping = 0;

        $discount = 0;
        $coupon = null;

        if ($couponCode) {
            $couponModel = new Coupon();
            $coupon = $couponModel->findByCode($couponCode);

            if (!$coupon) {
                http_response_code(400);
                echo json_encode(['error' => 'Cupom não encontrado']);
                exit;
            }

            $now = new \DateTime();

            if (isset($coupon['expires_at']) && $coupon['expires_at'] && $now > new \DateTime($coupon['expires_at'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Cupom expirado']);
                exit;
            }

            if (isset($coupon['active']) && !$coupon['active']) {
                http_response_code(400);
                echo json_encode(['error' => 'Cupom inativo']);
                exit;
            }

            if (isset($coupon['min_amount']) && $subtotal < $coupon['min_amount']) {
                http_response_code(400);
                echo json_encode(['error' => 'Subtotal abaixo do mínimo para usar o cupom']);
                exit;
            }

            if ($coupon['type'] === 'percentage') {
                $discount = $subtotal * ($coupon['value'] / 100);
            } elseif ($coupon['type'] === 'fixed') {
                $discount = $coupon['value'];
            }

            if ($discount > $subtotal) {
                $discount = $subtotal;
            }
        }

        $total = $subtotal + $shipping - $discount;

        $address = self::getAddressByCep($cep);
        if (!$address) {
            http_response_code(400);
            echo json_encode(['error' => 'CEP inválido']);
            exit;
        }

        $orderId = Order::create([
            'customer_email' => $email,
            'cep' => $cep,
            'address' => $address,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'coupon_code' => $coupon['code'] ?? null,
            'total' => $total,
            'items' => $cart
        ]);

        foreach ($cart as $item) {
            Stock::decrease((int)$item['product'], $item['variation'], (int)$item['quantity']);
        }

        $emailSent = EmailService::send(
            $email,
            "Pedido #$orderId confirmado",
            "Seu pedido foi realizado com sucesso. Total: R$ " . number_format($total, 2, ',', '.') . "\nEndereço: $address"
        );

        if (!$emailSent) {
            error_log("Falha ao enviar e-mail para $email do pedido #$orderId");
        }

        unset($_SESSION['cart']);

        echo json_encode([
            'message' => 'Pedido realizado com sucesso',
            'order_id' => $orderId,
            'total' => round($total, 2)
        ]);
    }

    private static function getAddressByCep($cep)
    {
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $json = @file_get_contents($url);
        if (!$json) return null;
        $data = json_decode($json, true);
        if (isset($data['erro']) && $data['erro'] === true) return null;
        return "{$data['logradouro']}, {$data['bairro']}, {$data['localidade']}-{$data['uf']}";
    }

   public static function addFakeCart()
    {
        session_start();
        $_SESSION['cart'] = [
            [
                'product' => 3,
                'variation' => 'Mescla - P',
                'quantity' => 1,
                'price' => 89.90
            ],
            [
                'product' => 2,
                'variation' => 'Verde Militar - M',
                'quantity' => 1,
                'price' => 129.90
            ]
        ];

        echo json_encode(['message' => 'Carrinho preenchido para testes']);
    }
}
