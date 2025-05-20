<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Coupon
{
    public function all(): array
    {
        $stmt = Database::connection()->query("SELECT * FROM coupons ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool
    {
        $stmt = Database::connection()->prepare("
            INSERT INTO coupons (code, type, value, min_amount, expires_at, active)
            VALUES (:code, :type, :value, :min_amount, :expires_at, :active)
        ");

        return $stmt->execute([
            'code'       => $data['code'],
            'type'       => $data['type'],
            'value'      => $data['value'],
            'min_amount' => $data['min_amount'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'active'     => $data['active'] ?? 1
        ]);
    }

    public function findByCode(string $code): ?array
    {
        $stmt = Database::connection()->prepare("SELECT * FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        return $coupon ?: null;
    }
}
