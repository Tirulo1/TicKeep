<?php
session_start();
require 'config/bd.php';
require 'includes/preferencias_usuario.php';

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
    $stmt = $pdo->prepare("SELECT * FROM garantias WHERE id_garantia = :id AND id_usuario = :user");
    $stmt->execute([
        ':id' => $id_garantia,
        ':user' => $id_usuario
    ]);

    $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$garantia) {
        die("Garantía no encontrada.");
    }

} catch (PDOException $e) {
    die($e->getMessage());
}

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST['nombre_producto']);
    $tienda = trim($_POST['tienda']);
    $fecha_compra = $_POST['fecha_compra'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $comentarios = trim($_POST['comentarios']);

    try {

        $sql = "UPDATE garantias
                SET nombre_producto = :nombre,
                    tienda = :tienda,
                    fecha_compra = :fecha_compra,
                    fecha_vencimiento = :fecha_vencimiento,
                    comentarios = :comentarios
                WHERE id_garantia = :id AND id_usuario = :user";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre,
            ':tienda' => $tienda,
            ':fecha_compra' => $fecha_compra,
            ':fecha_vencimiento' => $fecha_vencimiento,
            ':comentarios' => $comentarios,
            ':id' => $id_garantia,
            ':user' => $id_usuario
        ]);

        header("Location: index.php");
        exit();

    } catch (PDOException $e) {
        $mensaje = "Error al actualizar.";
        $tipo_alerta = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="<?= $preferencias['idioma'] === 'Inglés' ? 'en' : 'es' ?>"
      data-theme="<?= htmlspecialchars($preferencias['tema']) ?>"
      data-animations="<?= (int)$preferencias['animaciones_ui'] ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar garantía - TicKeep</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/nueva-garantia.css">
    <link rel="stylesheet" href="assets/css/preferencias.css">
</head>

<body>

<nav class="topbar d-flex align-items-center">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="brand">TicKeep</a>
        <div class="text-white">
            <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?>
        </div>
    </div>
</nav>

<div class="container">
    <div class="main-card">

        <div class="icon-box">✏️</div>
        <h2 class="title-box">Editar garantía</h2>

        <?php if ($mensaje !== ""): ?>
            <div class="alert alert-<?= $tipo_alerta ?> text-center">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Nombre del producto</label>
                <input type="text" class="form-control" name="nombre_producto"
                    value="<?= htmlspecialchars($garantia['nombre_producto']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Tienda</label>
                <input type="text" class="form-control" name="tienda"
                    value="<?= htmlspecialchars($garantia['tienda']) ?>">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha compra</label>
                    <input type="date" class="form-control" name="fecha_compra"
                        value="<?= $garantia['fecha_compra'] ?>" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha vencimiento</label>
                    <input type="date" class="form-control" name="fecha_vencimiento"
                        value="<?= $garantia['fecha_vencimiento'] ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Comentarios</label>
                <textarea class="form-control" name="comentarios" rows="4"><?= htmlspecialchars($garantia['comentarios']) ?></textarea>
            </div>

            <!-- 🔥 BOTÓN CON MISMO ESTILO -->
            <button type="submit" class="btn btn-save text-white w-100">
                Guardar cambios
            </button>

        </form>

    </div>
</div>

<footer>
    © 2025 TicKeep. Todos los derechos reservados.<br>
    Tu tranquilidad, garantizada.
</footer>

</body>
</html>