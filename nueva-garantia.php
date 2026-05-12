<?php
session_start();
require 'config/bd.php';
require 'gemini_ticket.php';
require 'includes/preferencias_usuario.php';

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit();
}

$mensaje = '';
$tipo_alerta = '';

$nombre_producto = trim($_POST['nombre_producto'] ?? '');
$tienda = trim($_POST['tienda'] ?? '');
$fecha_compra = trim($_POST['fecha_compra'] ?? '');
$fecha_vencimiento = trim($_POST['fecha_vencimiento'] ?? '');
$comentarios = trim($_POST['comentarios'] ?? '');

$archivo_ticket = trim($_POST['archivo_ticket_actual'] ?? '');
$foto_producto = trim($_POST['foto_producto_actual'] ?? '');

function calcularEstado($fechaVencimiento) {
    $hoy = new DateTime();
    $vencimiento = new DateTime($fechaVencimiento);

    $diferencia = (int)$hoy->diff($vencimiento)->format('%r%a');

    if ($diferencia < 0) {
        return 'Caducada';
    } elseif ($diferencia <= 30) {
        return 'Expira pronto';
    } else {
        return 'Vigente';
    }
}

function guardarArchivoSubido(string $nombreInput, string $directorio, array $permitidas)
{
    if (!isset($_FILES[$nombreInput]) || $_FILES[$nombreInput]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$nombreInput]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    $nombre_original = $_FILES[$nombreInput]['name'];
    $tmp_name = $_FILES[$nombreInput]['tmp_name'];
    $tamano = $_FILES[$nombreInput]['size'];

    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

    if (!in_array($extension, $permitidas, true)) {
        return false;
    }

    if ($tamano > 5 * 1024 * 1024) {
        return false;
    }

    $nuevo_nombre = uniqid($nombreInput . '_', true) . '.' . $extension;
    $ruta_final = $directorio . $nuevo_nombre;

    if (!move_uploaded_file($tmp_name, $ruta_final)) {
        return false;
    }

    return $ruta_final;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    $ticketSubido = guardarArchivoSubido('archivo_ticket', 'uploads/tickets/', ['jpg', 'jpeg', 'png', 'webp', 'pdf']);
    if ($ticketSubido === false) {
        $mensaje = 'Error al subir el ticket. Solo se permiten JPG, JPEG, PNG, WEBP o PDF de hasta 5 MB.';
        $tipo_alerta = 'danger';
    } elseif ($ticketSubido !== null) {
        $archivo_ticket = $ticketSubido;
    }

    $fotoSubida = guardarArchivoSubido('foto_producto', 'uploads/productos/', ['jpg', 'jpeg', 'png', 'webp']);
    if ($fotoSubida === false && $mensaje === '') {
        $mensaje = 'Error al subir la foto del producto. Solo se permiten JPG, JPEG, PNG o WEBP de hasta 5 MB.';
        $tipo_alerta = 'danger';
    } elseif ($fotoSubida !== null) {
        $foto_producto = $fotoSubida;
    }

    if ($accion === 'escanear_ticket' && $mensaje === '') {
        if ($archivo_ticket === '') {
            $mensaje = 'Debes subir un ticket para escanearlo.';
            $tipo_alerta = 'warning';
        } else {
            $ia = procesarTicketGemini($archivo_ticket);

            if ($ia['ok']) {
                if ($ia['nombre_producto'] !== '') {
                    $nombre_producto = $ia['nombre_producto'];
                }

                if ($ia['tienda'] !== '') {
                    $tienda = $ia['tienda'];
                }

                if ($ia['fecha_compra'] !== '') {
                    $fecha_compra = $ia['fecha_compra'];
                }

                if ($nombre_producto === '') {
                    $mensaje = 'Ticket escaneado. Se han rellenado los datos detectados, pero el nombre del producto no se ha podido identificar con claridad.';
                    $tipo_alerta = 'warning';
                } else {
                    $mensaje = 'Ticket escaneado correctamente. Revisa los datos antes de guardar.';
                    $tipo_alerta = 'success';
                }
            } else {
                $mensaje = $ia['error'] ?? 'No se pudo interpretar el ticket con IA.';
                $tipo_alerta = 'warning';
            }
        }
    }

    if ($accion === 'guardar_garantia' && $mensaje === '') {
        if ($nombre_producto === '' || $fecha_compra === '' || $fecha_vencimiento === '') {
            $mensaje = 'Por favor, rellena nombre del producto, fecha de compra y fecha de vencimiento.';
            $tipo_alerta = 'danger';
        } elseif ($fecha_vencimiento < $fecha_compra) {
            $mensaje = 'La fecha de vencimiento no puede ser anterior a la fecha de compra.';
            $tipo_alerta = 'danger';
        } else {
            $estado = calcularEstado($fecha_vencimiento);

            try {
                $sql = "INSERT INTO garantias 
                        (id_usuario, nombre_producto, tienda, fecha_compra, fecha_vencimiento, archivo_ticket, foto_producto, comentarios, estado)
                        VALUES
                        (:id_usuario, :nombre_producto, :tienda, :fecha_compra, :fecha_vencimiento, :archivo_ticket, :foto_producto, :comentarios, :estado)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':id_usuario' => $_SESSION['id_usuario'],
                    ':nombre_producto' => $nombre_producto,
                    ':tienda' => $tienda !== '' ? $tienda : null,
                    ':fecha_compra' => $fecha_compra,
                    ':fecha_vencimiento' => $fecha_vencimiento,
                    ':archivo_ticket' => $archivo_ticket !== '' ? $archivo_ticket : null,
                    ':foto_producto' => $foto_producto !== '' ? $foto_producto : null,
                    ':comentarios' => $comentarios !== '' ? $comentarios : null,
                    ':estado' => $estado
                ]);

                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                $mensaje = 'Error al guardar la garantía: ' . $e->getMessage();
                $tipo_alerta = 'danger';
            }
        }
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
    <title>Nueva garantía - TicKeep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/preferencias.css">
    <style>
        body {
            background-color: #efefef;
        }

        .topbar {
            background: #202bbf;
            min-height: 70px;
        }

        .topbar .brand {
            color: #fff;
            font-weight: 700;
            text-decoration: none;
        }

        .main-card {
            max-width: 820px;
            margin: 60px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            padding: 45px 35px;
        }

        .icon-box {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 10px;
        }

        .title-box {
            text-align: center;
            font-weight: 700;
            margin-bottom: 35px;
        }

        .btn-save {
            background: #0d7fc0;
            border: none;
        }

        .btn-save:hover {
            background: #0b6da4;
        }

        footer {
            background: #202bbf;
            color: #fff;
            text-align: center;
            padding: 18px 10px;
            margin-top: 80px;
            font-size: 0.8rem;
        }

        .preview-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 6px;
        }
    </style>
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
        <div class="icon-box">📦</div>
        <h2 class="title-box">Añadir una nueva garantía</h2>

        <?php if ($mensaje !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($tipo_alerta) ?> text-center">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="archivo_ticket_actual" value="<?= htmlspecialchars($archivo_ticket) ?>">
            <input type="hidden" name="foto_producto_actual" value="<?= htmlspecialchars($foto_producto) ?>">

            <div class="mb-3">
                <label for="nombre_producto" class="form-label">Nombre del producto *</label>
                <input
                    type="text"
                    class="form-control"
                    id="nombre_producto"
                    name="nombre_producto"
                    value="<?= htmlspecialchars($nombre_producto) ?>"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="tienda" class="form-label">Tienda</label>
                <input
                    type="text"
                    class="form-control"
                    id="tienda"
                    name="tienda"
                    value="<?= htmlspecialchars($tienda) ?>"
                >
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="fecha_compra" class="form-label">Fecha de compra *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_compra"
                        name="fecha_compra"
                        value="<?= htmlspecialchars($fecha_compra) ?>"
                        required
                    >
                </div>

                <div class="col-md-6 mb-3">
                    <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="fecha_vencimiento"
                        name="fecha_vencimiento"
                        value="<?= htmlspecialchars($fecha_vencimiento) ?>"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label for="archivo_ticket" class="form-label">Subir ticket</label>
                <input
                    type="file"
                    class="form-control"
                    id="archivo_ticket"
                    name="archivo_ticket"
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                >
                <?php if ($archivo_ticket !== ''): ?>
                    <div class="preview-text">Ticket actual: <?= htmlspecialchars(basename($archivo_ticket)) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <button type="submit" name="accion" value="escanear_ticket" class="btn btn-secondary w-100" formnovalidate>
                    Escanear ticket
                </button>
            </div>

            <div class="mb-3">
                <label for="foto_producto" class="form-label">Seleccionar foto del producto</label>
                <input
                    type="file"
                    class="form-control"
                    id="foto_producto"
                    name="foto_producto"
                    accept=".jpg,.jpeg,.png,.webp"
                >
                <?php if ($foto_producto !== ''): ?>
                    <div class="preview-text">Foto actual: <?= htmlspecialchars(basename($foto_producto)) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="comentarios" class="form-label">Comentarios</label>
                <textarea
                    class="form-control"
                    id="comentarios"
                    name="comentarios"
                    rows="4"
                ><?= htmlspecialchars($comentarios) ?></textarea>
            </div>

            <button type="submit" name="accion" value="guardar_garantia" class="btn btn-save text-white w-100">
                Añadir producto
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