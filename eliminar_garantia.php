<?php
session_start();
require 'config/bd.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_garantia = $_GET['id'] ?? null;

if (!$id_garantia) {
    header("Location: index.php");
    exit();
}

try {

    $stmt = $pdo->prepare("DELETE FROM garantias
                           WHERE id_garantia = :id
                           AND id_usuario = :user");

    $stmt->execute([
        ':id' => $id_garantia,
        ':user' => $id_usuario
    ]);

    header("Location: index.php");
    exit();

} catch (PDOException $e) {
    die("Error al eliminar garantía.");
}