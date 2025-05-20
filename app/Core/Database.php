<?php
namespace App\Core;

use PDO;
use Dotenv\Dotenv;

class Database
{
    private static $connection;

    public static function connection()
    {
        if (!self::$connection) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}";
            self::$connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$connection;
    }
}
