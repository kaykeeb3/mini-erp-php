<?php
namespace App\Controllers;

use App\Models\Order;

class WebhookController
{
    public static function statusUpdate()
    {
        header('Content-Type: application/json');

        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        if (!$data || !isset($data['order_id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Payload inválido. Esperado: { "order_id": int, "status": string }'
            ]);
            return;
        }

        $orderId = (int) $data['order_id'];
        $newStatus = strtolower(trim($data['status']));

        $order = Order::findById($orderId);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => "Pedido com ID $orderId não encontrado"]);
            return;
        }

        try {
            $cancelStatuses = ['cancelado', 'cancel', 'canceled', 'cancelled'];

            if (in_array($newStatus, $cancelStatuses)) {
                Order::delete($orderId);
                http_response_code(200);
                echo json_encode([
                    'message' => "Pedido #$orderId removido com sucesso. Status recebido: '$newStatus'"
                ]);
            } else {
                Order::updateStatus($orderId, $newStatus);
                http_response_code(200);
                echo json_encode([
                    'message' => "Status do pedido #$orderId atualizado para '$newStatus'"
                ]);
            }
        } catch (\Exception $e) {
            error_log("[Webhook Error] " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno ao processar webhook']);
        }
    }
}
