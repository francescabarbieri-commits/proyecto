<?php
$host      = 'localhost';
$base      = 'teatro';
$usuarioDb = 'root';
$claveDb   = '';

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base;charset=utf8mb4",
        $usuarioDb,
        $claveDb,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}