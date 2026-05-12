<?php
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=tickeepdb;charset=utf8mb4',
        'root',
        ''
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $p) {
    echo "Error de conexión: " . $p->getMessage();
    exit();
}