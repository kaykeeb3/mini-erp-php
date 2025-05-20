<?php

namespace App\Controllers;

use App\Models\Coupon;

class CouponController
{
    public function index()
    {
        $couponModel = new Coupon();
        $coupons = $couponModel->all();

        header('Content-Type: application/json');
        echo json_encode($coupons);
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['code', 'type', 'value'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Campo obrigatório: $field"]);
                return;
            }
        }

        $couponModel = new Coupon();
        $result = $couponModel->create($data);

        if ($result) {
            http_response_code(201);
            echo json_encode(['message' => 'Cupom criado com sucesso']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar cupom']);
        }
    }

    public function validate()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $code = $data['code'] ?? null;
        $subtotal = isset($data['subtotal']) ? (float)$data['subtotal'] : null;

        if (!$code || $subtotal === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Parâmetros inválidos: código e subtotal são obrigatórios']);
            return;
        }

        $couponModel = new Coupon();
        $coupon = $couponModel->findByCode($code);

        if (!$coupon) {
            http_response_code(404);
            echo json_encode(['error' => 'Cupom não encontrado']);
            return;
        }

        $now = new \DateTime();

        if (!empty($coupon['expires_at'])) {
            $expiresAt = new \DateTime($coupon['expires_at']);
            if ($now > $expiresAt) {
                http_response_code(400);
                echo json_encode(['error' => 'Cupom expirado']);
                return;
            }
        }

        if (isset($coupon['active']) && !$coupon['active']) {
            http_response_code(400);
            echo json_encode(['error' => 'Cupom inativo']);
            return;
        }

        if (isset($coupon['min_amount']) && $subtotal < (float)$coupon['min_amount']) {
            http_response_code(400);
            echo json_encode(['error' => 'Subtotal abaixo do valor mínimo para uso do cupom']);
            return;
        }

        $type = $coupon['type'] ?? null;
        $value = $coupon['value'] ?? null;

        if ($type === null || $value === null) {
            http_response_code(500);
            echo json_encode(['error' => 'Dados do cupom incompletos']);
            return;
        }

        $discount = 0;
        if ($type === 'percentage') {
            $discount = $subtotal * ($value / 100);
        } else if ($type === 'fixed') {
            $discount = $value;
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de cupom inválido']);
            return;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        echo json_encode([
            'code' => $coupon['code'],
            'type' => $type,
            'value' => $value,
            'discount' => round($discount, 2)
        ]);
    }
}
