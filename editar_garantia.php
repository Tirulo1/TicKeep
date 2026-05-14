<?php
session_start();
require 'config/bd.php';
require 'includes/preferencias_usuario.php';
require 'config/lang.php'; // Cargamos las traducciones

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

    // --- CÁLCULO DEL NUEVO ESTADO SEGÚN LA FECHA DE VENCIMIENTO ---
    $hoy = new DateTime();
    $vencimiento = new DateTime($fecha_vencimiento);
    $diferencia = (int)$hoy->diff($vencimiento)->format('%r%a');

    if ($diferencia < 0) {
        $estado_actualizado = 'Caducada';
    } elseif ($diferencia <= 30) {
        $estado_actualizado = 'Expira pronto';
    } else {
        $estado_actualizado = 'Vigente';
    }
    // --------------------------------------------------------------

    try {

        $sql = "UPDATE garantias
                SET nombre_producto = :nombre,
                    tienda = :tienda,
                    fecha_compra = :fecha_compra,
                    fecha_vencimiento = :fecha_vencimiento,
                    comentarios = :comentarios,
                    estado = :estado
                WHERE id_garantia = :id AND id_usuario = :user";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':nombre' => $nombre,
            ':tienda' => $tienda,
            ':fecha_compra' => $fecha_compra,
            ':fecha_vencimiento' => $fecha_vencimiento,
            ':comentarios' => $comentarios,
            ':estado' => $estado_actualizado,
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
    <title><?= $t['titulo_editar'] ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/nueva-garantia.css">
    <link rel="stylesheet" href="assets/css/preferencias.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>

    <?php require 'partials/header_main.php'; ?>

    <div class="container">
        <div class="main-card">

            <div class="icon-box">✏️</div>
            <h2 class="title-box"><?= $t['editar_titulo'] ?></h2>

            <?php if ($mensaje !== ""): ?>
                <div class="alert alert-<?= $tipo_alerta ?> text-center">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label"><?= $t['nombre_producto'] ?></label>
                    <input type="text" class="form-control" name="nombre_producto"
                        value="<?= htmlspecialchars($garantia['nombre_producto']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><?= $t['tienda'] ?></label>
                    <input type="text" class="form-control" name="tienda"
                        value="<?= htmlspecialchars($garantia['tienda']) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $t['fecha_compra'] ?></label>
                        <input type="date" class="form-control" name="fecha_compra"
                            value="<?= $garantia['fecha_compra'] ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?= $t['fecha_vencimiento'] ?></label>
                        <input type="date" class="form-control" name="fecha_vencimiento"
                            value="<?= $garantia['fecha_vencimiento'] ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label"><?= $t['comentarios'] ?></label>
                    <textarea class="form-control" name="comentarios" rows="4"><?= htmlspecialchars($garantia['comentarios'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-save text-white w-100">
                    <?= $t['guardar_cambios'] ?>
                </button>

            </form>

        </div>
    </div>

    <footer>
        <?= $t['footer'] ?><br>
        <?= $t['footer_sub'] ?>
    </footer>

</body>

</html>