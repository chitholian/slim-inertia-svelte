<?php

namespace App\Database;

use PDO;

class DB extends PDO
{
    private static DB $instance;

    private function __construct()
    {
        $c = config('db', []);
        $host = $c['host'] ?? 'localhost';
        $port = $c['port'] ?? 3306;
        $user = $c['username'] ?? 'root';
        $pass = $c['password'] ?? '';
        $db = $c['database'];
        $attrs = [
            self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION,
            self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC,
            self::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
        ];
        parent::__construct("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, $attrs);
    }

    public static function getInstance(): DB
    {
        if (!isset(static::$instance)) static::$instance = new DB();
        return static::$instance;
    }

    public function execute($query, $bindings = [])
    {
        $stmt = $this->prepare($query);
        $stmt->execute($bindings);
        return $stmt;
    }
}
