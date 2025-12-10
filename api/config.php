<?php
// api/config.php

$DB_HOST = 'localhost';
$DB_NAME = 'box_progressive';
$DB_USER = 'root';
$DB_PASS = ''; // en XAMPP por defecto suele estar vacío

function getPDO() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "ok" => false,
            "error" => "Error de conexión a la base de datos"
        ]);
        exit;
    }
}
