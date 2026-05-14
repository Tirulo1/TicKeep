<?php
session_start();
require 'config/bd.php';
require 'includes/preferencias_usuario.php';
require 'config/lang.php'; // Cargamos las traducciones

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_garantia = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id_garantia <= 0) {
    header('Location: mis_garantias.php');
    exit();
}

$mensaje = '';
$tipo_alerta = '';

// Añadir comentario a la garantía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_comentario'])) {
    $nuevo_comentario = trim($_POST['nuevo_comentario']);

    if ($nuevo_comentario === '') {
        $mensaje = $t['comentario_vacio'];
        $tipo_alerta = 'danger';
    } else {
        try {
            $sql = "SELECT comentarios
                    FROM garantias
                    WHERE id_garantia = :id_garantia
                      AND id_usuario = :id_usuario";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_garantia' => $id_garantia,
                ':id_usuario' => $id_usuario
            ]);

            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                header('Location: mis_garantias.php');
                exit();
            }

            $comentarioConFecha = "[" . date('d/m/Y') . "] " . $nuevo_comentario;
            $comentariosActuales = trim($fila['comentarios'] ?? '');

            if ($comentariosActuales !== '') {
                $comentariosFinales = $comentariosActuales . "\n\n" . $comentarioConFecha;
            } else {
                $comentariosFinales = $comentarioConFecha;
            }

            $sqlUpdate = "UPDATE garantias
                          SET comentarios = :comentarios
                          WHERE id_garantia = :id_garantia
                            AND id_usuario = :id_usuario";
            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':comentarios' => $comentariosFinales,
                ':id_garantia' => $id_garantia,
                ':id_usuario' => $id_usuario
            ]);

            $mensaje = $t['comentario_ok'];
            $tipo_alerta = 'success';
        } catch (PDOException $e) {
            $mensaje = 'Error al guardar el comentario: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

// Cargar garantía
try {
    $sql = "SELECT id_garantia, id_usuario, nombre_producto, tienda, fecha_compra,
                   fecha_vencimiento, archivo_ticket, foto_producto,  comentarios, estado
            FROM garantias
            WHERE id_garantia = :id_garantia
              AND id_usuario = :id_usuario";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_garantia' => $id_garantia,
        ':id_usuario' => $id_usuario
    ]);

    $garantia = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$garantia) {
        header('Location: mis_garantias.php');
        exit();
    }
} catch (PDOException $e) {
    die("Error al cargar la garantía: " . $e->getMessage());
}

$claseEstado = 'secondary';
$estadoTexto = $garantia['estado'];

if ($garantia['estado'] === 'Vigente') {
    $claseEstado = 'success';
    $estadoTexto = $t['vigente'];
} elseif ($garantia['estado'] === 'Expira pronto') {
    $claseEstado = 'warning';
    $estadoTexto = $t['expira_pronto'];
} elseif ($garantia['estado'] === 'Caducada') {
    $claseEstado = 'danger';
    $estadoTexto = $t['caducada'];
}

// Comprobar si el archivo es una imagen
// Determinar imagen a mostrar: foto_producto > archivo_ticket > default
$imagenesPermitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!empty($garantia['foto_producto'])) {
    $rutaImagen = htmlspecialchars($garantia['foto_producto']);
    $mostrarImagen = true;
} elseif (!empty($garantia['archivo_ticket'])) {
    $extension = strtolower(pathinfo($garantia['archivo_ticket'], PATHINFO_EXTENSION));
    if (in_array($extension, $imagenesPermitidas, true)) {
        $rutaImagen = htmlspecialchars($garantia['archivo_ticket']);
        $mostrarImagen = true;
    } else {
        $rutaImagen = 'assets/img/producto-default.png';
        $mostrarImagen = true;
    }
} else {
    $rutaImagen = 'assets/img/producto-default.png';
    $mostrarImagen = true;
}
?>
<!DOCTYPE html>
<html lang="<?= $preferencias['idioma'] === 'Inglés' ? 'en' : 'es' ?>"
    data-theme="<?= htmlspecialchars($preferencias['tema']) ?>"
    data-animations="<?= (int)$preferencias['animaciones_ui'] ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['titulo_detalle'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/detalle.css">
    <link rel="stylesheet" href="assets/css/preferencias.css">
</head>

<body>

   <?php require 'partials/header_main.php'; ?>
    <div class="container">
        <div class="mx-auto" style="max-width: 820px; margin-top: 40px;">
            <a href="index.php" class="back-link"><?= $t['volver'] ?></a>
        </div>

        <div class="main-card">
            <?php if ($mensaje !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($tipo_alerta) ?> text-center">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 align-items-start">
                <div class="col-md-auto">
                    <img src="<?= $rutaImagen ?>" alt="Imagen del producto" class="product-image">
                </div>
                <div class="col">
                    <h3 class="fw-bold mb-2"><?= htmlspecialchars($garantia['nombre_producto']) ?></h3>

                    <span class="badge text-bg-<?= $claseEstado ?> mb-3">
                        <?= htmlspecialchars($estadoTexto) ?>
                    </span>

                    <p class="mb-2">
                        <strong><?= $t['tienda'] ?></strong>
                        <?= htmlspecialchars($garantia['tienda'] ?: $t['no_indicada']) ?>
                    </p>

                    <p class="mb-2">
                        <strong><?= $t['fecha_compra'] ?></strong>
                        <?= date('d/m/Y', strtotime($garantia['fecha_compra'])) ?>
                    </p>

                    <p class="mb-0">
                        <strong><?= $t['fin_garantia'] ?></strong>
                        <?= date('d/m/Y', strtotime($garantia['fecha_vencimiento'])) ?>
                    </p>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="fw-bold"><?= $t['comentarios'] ?></h5>
                <p class="text-muted small">
                    <?= $t['comentarios_desc'] ?>
                </p>

                <div class="comment-box mb-3">
                    <?= htmlspecialchars($garantia['comentarios'] ?: $t['sin_comentarios']) ?>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <textarea name="nuevo_comentario" class="form-control" rows="3"
                            placeholder="<?= $t['nuevo_comentario'] ?>"></textarea>
                    </div>

                    <button type="submit" class="btn btn-light border btn-soft">
                        <?= $t['añadir_comentario'] ?>
                    </button>
                </form>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="editar_garantia.php?id=<?= (int) $garantia['id_garantia'] ?>" class="btn btn-primary btn-soft">
                    <?= $t['editar'] ?>
                </a>

                <a href="eliminar_garantia.php?id=<?= $garantia['id_garantia'] ?>"
                    class="btn btn-danger"
                    <?= !empty($preferencias['confirmar_eliminacion']) ? 'onclick="return confirm(\'' . $t['confirmar_borrar'] . '\')"' : '' ?>>
                    <?= $t['borrar'] ?>
                </a>
            </div>
        </div>
    </div>

    <footer>
        <?= $t['footer'] ?><br>
        <?= $t['footer_sub'] ?>
    </footer>

</body>

</html>