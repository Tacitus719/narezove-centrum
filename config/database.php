<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'proma_db'); // Zmeňte, ak sa vaša DB volá inak
define('DB_USER', 'root');     // V XAMPP je predvolený user 'root'
define('DB_PASS', 'N34malo#719');         // V XAMPP je heslo zvyčajne prázdne

class Database {
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die("Chyba pripojenia k databáze: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}