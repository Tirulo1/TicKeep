<?php
$host   = getenv('MYSQLHOST')     ?: '127.0.0.1';
$puerto = getenv('MYSQLPORT')     ?: '3307';
$nombre = getenv('MYSQLDATABASE') ?: 'tickeepdb';
$usuario= getenv('MYSQLUSER')     ?: 'root';
$pass   = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$puerto};dbname={$nombre};charset=utf8mb4",
        $usuario,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}