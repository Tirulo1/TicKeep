<?php
session_start();
require 'config/bd.php';
require 'includes/preferencias_usuario.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = '';
$tipo_alerta = '';

function valor($array, $clave, $defecto = '')
{
    return isset($array[$clave]) && $array[$clave] !== null ? $array[$clave] : $defecto;
}

try {
    $sql = "SELECT 
                u.nombre,
                u.email,
                u.contrasena,
                c.foto_perfil,
                c.notificaciones_email,
                c.notificaciones_app,
                c.aviso_vencimiento,
                c.dias_aviso,
                c.frecuencia_recordatorio,
                c.hora_recordatorio,
                c.notificar_caducadas,
                c.resumen_mensual,
                c.idioma,
                c.tema,
                c.color_acento,
                c.formato_fecha,
                c.animaciones_ui,
                c.orden_garantias,
                c.mostrar_dias_restantes,
                c.confirmar_eliminacion,
                c.modo_compacto
            FROM usuarios u
            LEFT JOIN opciones_configuracion c ON u.id_usuario = c.id_usuario
            WHERE u.id_usuario = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado.");
    }

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM opciones_configuracion WHERE id_usuario = :id");
    $stmtCheck->execute([':id' => $id_usuario]);
    $existeConfiguracion = (int)$stmtCheck->fetchColumn() > 0;
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$fotoPerfil = !empty($usuario['foto_perfil']) ? $usuario['foto_perfil'] : 'default-avatar.png';
$temaActual = valor($usuario, 'tema', 'claro');
$colorActual = valor($usuario, 'color_acento', '#202bbf');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $password_actual = trim($_POST['password_actual'] ?? '');
    $password_nueva = trim($_POST['password_nueva'] ?? '');
    $password_confirmar = trim($_POST['password_confirmar'] ?? '');

    $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;
    $notificaciones_app = isset($_POST['notificaciones_app']) ? 1 : 0;
    $aviso_vencimiento = isset($_POST['aviso_vencimiento']) ? 1 : 0;
    $dias_aviso = (int)($_POST['dias_aviso'] ?? 30);
    $frecuencia_recordatorio = trim($_POST['frecuencia_recordatorio'] ?? 'una_vez');
    $hora_recordatorio = trim($_POST['hora_recordatorio'] ?? '09:00');
    $notificar_caducadas = isset($_POST['notificar_caducadas']) ? 1 : 0;
    $resumen_mensual = isset($_POST['resumen_mensual']) ? 1 : 0;

    $idioma = trim($_POST['idioma'] ?? 'Español');
    $tema = trim($_POST['tema'] ?? 'claro');
    $color_acento = trim($_POST['color_acento'] ?? '#202bbf');
    $formato_fecha = trim($_POST['formato_fecha'] ?? 'd/m/Y');
    $animaciones_ui = (int)valor($usuario, 'animaciones_ui', 1);

    $orden_garantias = trim($_POST['orden_garantias'] ?? 'fecha_compra_desc');
    $mostrar_dias_restantes = isset($_POST['mostrar_dias_restantes']) ? 1 : 0;
    $confirmar_eliminacion = isset($_POST['confirmar_eliminacion']) ? 1 : 0;
    $modo_compacto = (int)valor($usuario, 'modo_compacto', 0);

    if ($dias_aviso < 1) {
        $dias_aviso = 1;
    }
    if ($dias_aviso > 365) {
        $dias_aviso = 365;
    }

    $idiomasPermitidos = ['Español', 'Inglés'];
    $temasPermitidos = ['claro', 'oscuro', 'sistema'];
    $frecuenciasPermitidas = ['una_vez', 'diario', 'semanal'];
    $formatosFechaPermitidos = ['d/m/Y', 'Y-m-d', 'm/d/Y'];
    $ordenesPermitidos = ['fecha_compra_desc', 'fecha_compra_asc', 'fecha_vencimiento_asc', 'fecha_vencimiento_desc', 'nombre_asc', 'nombre_desc'];

    if (!in_array($idioma, $idiomasPermitidos, true)) {
        $idioma = 'Español';
    }

    if (!in_array($tema, $temasPermitidos, true)) {
        $tema = 'claro';
    }

    if (!in_array($frecuencia_recordatorio, $frecuenciasPermitidas, true)) {
        $frecuencia_recordatorio = 'una_vez';
    }

    if (!in_array($formato_fecha, $formatosFechaPermitidos, true)) {
        $formato_fecha = 'd/m/Y';
    }

    if (!in_array($orden_garantias, $ordenesPermitidos, true)) {
        $orden_garantias = 'fecha_compra_desc';
    }

    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color_acento)) {
        $color_acento = '#202bbf';
    }

    if (!preg_match('/^(2[0-3]|[01][0-9]):([0-5][0-9])$/', $hora_recordatorio)) {
        $hora_recordatorio = '09:00';
    }

    if ($nombre === '' || $email === '') {
        $mensaje = 'El nombre y el correo son obligatorios.';
        $tipo_alerta = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El correo electrónico no tiene un formato válido.';
        $tipo_alerta = 'danger';
    } elseif (($password_nueva !== '' || $password_confirmar !== '' || $password_actual !== '') && $password_actual === '') {
        $mensaje = 'Para cambiar la contraseña debes introducir la contraseña actual.';
        $tipo_alerta = 'danger';
    } elseif ($password_nueva !== $password_confirmar) {
        $mensaje = 'La nueva contraseña y su confirmación no coinciden.';
        $tipo_alerta = 'danger';
    } elseif ($password_nueva !== '' && strlen($password_nueva) < 6) {
        $mensaje = 'La nueva contraseña debe tener al menos 6 caracteres.';
        $tipo_alerta = 'danger';
    } elseif ($password_nueva !== '' && !password_verify($password_actual, $usuario['contrasena'])) {
        $mensaje = 'La contraseña actual no es correcta.';
        $tipo_alerta = 'danger';
    } else {
        try {
            $stmtEmail = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario <> :id");
            $stmtEmail->execute([
                ':email' => $email,
                ':id' => $id_usuario
            ]);

            if ($stmtEmail->fetch()) {
                $mensaje = 'Ese correo electrónico ya está siendo usado por otro usuario.';
                $tipo_alerta = 'danger';
            } else {
                $pdo->beginTransaction();

                if ($password_nueva !== '') {
                    $passwordHash = password_hash($password_nueva, PASSWORD_BCRYPT);

                    $sqlUser = "UPDATE usuarios 
                                SET nombre = :nombre,
                                    email = :email,
                                    contrasena = :contrasena
                                WHERE id_usuario = :id";

                    $stmtUser = $pdo->prepare($sqlUser);
                    $stmtUser->execute([
                        ':nombre' => $nombre,
                        ':email' => $email,
                        ':contrasena' => $passwordHash,
                        ':id' => $id_usuario
                    ]);
                } else {
                    $sqlUser = "UPDATE usuarios 
                                SET nombre = :nombre,
                                    email = :email
                                WHERE id_usuario = :id";

                    $stmtUser = $pdo->prepare($sqlUser);
                    $stmtUser->execute([
                        ':nombre' => $nombre,
                        ':email' => $email,
                        ':id' => $id_usuario
                    ]);
                }

                $nuevaFoto = $fotoPerfil;

                if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                        $directorio = 'assets/img/';

                        if (!is_dir($directorio)) {
                            mkdir($directorio, 0777, true);
                        }

                        $extension = strtolower(pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION));
                        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
                        $tamano = $_FILES['foto_perfil']['size'];

                        if (!in_array($extension, $permitidas, true)) {
                            throw new Exception('Formato de imagen no permitido.');
                        }

                        if ($tamano > 3 * 1024 * 1024) {
                            throw new Exception('La foto de perfil no puede superar los 3 MB.');
                        }

                        $nuevoNombre = 'perfil_' . $id_usuario . '_' . time() . '.' . $extension;
                        $rutaFinal = $directorio . $nuevoNombre;

                        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaFinal)) {
                            $nuevaFoto = $nuevoNombre;
                        }
                    }
                }

                if ($existeConfiguracion) {
                    $sqlConfig = "UPDATE opciones_configuracion
                                  SET foto_perfil = :foto,
                                      notificaciones_email = :notificaciones_email,
                                      notificaciones_app = :notificaciones_app,
                                      aviso_vencimiento = :aviso,
                                      dias_aviso = :dias_aviso,
                                      frecuencia_recordatorio = :frecuencia,
                                      hora_recordatorio = :hora_recordatorio,
                                      notificar_caducadas = :notificar_caducadas,
                                      resumen_mensual = :resumen_mensual,
                                      idioma = :idioma,
                                      tema = :tema,
                                      color_acento = :color_acento,
                                      formato_fecha = :formato_fecha,
                                      animaciones_ui = :animaciones_ui,
                                      orden_garantias = :orden_garantias,
                                      mostrar_dias_restantes = :mostrar_dias_restantes,
                                      confirmar_eliminacion = :confirmar_eliminacion,
                                      modo_compacto = :modo_compacto
                                  WHERE id_usuario = :id";

                    $stmtConfig = $pdo->prepare($sqlConfig);
                    $stmtConfig->execute([
                        ':foto' => $nuevaFoto,
                        ':notificaciones_email' => $notificaciones_email,
                        ':notificaciones_app' => $notificaciones_app,
                        ':aviso' => $aviso_vencimiento,
                        ':dias_aviso' => $dias_aviso,
                        ':frecuencia' => $frecuencia_recordatorio,
                        ':hora_recordatorio' => $hora_recordatorio,
                        ':notificar_caducadas' => $notificar_caducadas,
                        ':resumen_mensual' => $resumen_mensual,
                        ':idioma' => $idioma,
                        ':tema' => $tema,
                        ':color_acento' => $color_acento,
                        ':formato_fecha' => $formato_fecha,
                        ':animaciones_ui' => $animaciones_ui,
                        ':orden_garantias' => $orden_garantias,
                        ':mostrar_dias_restantes' => $mostrar_dias_restantes,
                        ':confirmar_eliminacion' => $confirmar_eliminacion,
                        ':modo_compacto' => $modo_compacto,
                        ':id' => $id_usuario
                    ]);
                } else {
                    $sqlConfig = "INSERT INTO opciones_configuracion
                                  (id_usuario, foto_perfil, notificaciones_email, notificaciones_app, aviso_vencimiento, dias_aviso,
                                   frecuencia_recordatorio, hora_recordatorio, notificar_caducadas, resumen_mensual, idioma,
                                   tema, color_acento, formato_fecha, animaciones_ui, orden_garantias, mostrar_dias_restantes,
                                   confirmar_eliminacion, modo_compacto)
                                  VALUES
                                  (:id, :foto, :notificaciones_email, :notificaciones_app, :aviso, :dias_aviso,
                                   :frecuencia, :hora_recordatorio, :notificar_caducadas, :resumen_mensual, :idioma,
                                   :tema, :color_acento, :formato_fecha, :animaciones_ui, :orden_garantias, :mostrar_dias_restantes,
                                   :confirmar_eliminacion, :modo_compacto)";

                    $stmtConfig = $pdo->prepare($sqlConfig);
                    $stmtConfig->execute([
                        ':id' => $id_usuario,
                        ':foto' => $nuevaFoto,
                        ':notificaciones_email' => $notificaciones_email,
                        ':notificaciones_app' => $notificaciones_app,
                        ':aviso' => $aviso_vencimiento,
                        ':dias_aviso' => $dias_aviso,
                        ':frecuencia' => $frecuencia_recordatorio,
                        ':hora_recordatorio' => $hora_recordatorio,
                        ':notificar_caducadas' => $notificar_caducadas,
                        ':resumen_mensual' => $resumen_mensual,
                        ':idioma' => $idioma,
                        ':tema' => $tema,
                        ':color_acento' => $color_acento,
                        ':formato_fecha' => $formato_fecha,
                        ':animaciones_ui' => $animaciones_ui,
                        ':orden_garantias' => $orden_garantias,
                        ':mostrar_dias_restantes' => $mostrar_dias_restantes,
                        ':confirmar_eliminacion' => $confirmar_eliminacion,
                        ':modo_compacto' => $modo_compacto
                    ]);
                }

                $pdo->commit();

                $_SESSION['nombre'] = $nombre;
                header("Location: configuracion.php?guardado=1");
                exit();
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $mensaje = 'Error al guardar la configuración: ' . $e->getMessage();
            $tipo_alerta = 'danger';
        }
    }
}

if (isset($_GET['guardado'])) {
    $mensaje = 'Configuración guardada correctamente.';
    $tipo_alerta = 'success';

    try {
        $sql = "SELECT 
                    u.nombre,
                    u.email,
                    u.contrasena,
                    c.foto_perfil,
                    c.notificaciones_email,
                    c.notificaciones_app,
                    c.aviso_vencimiento,
                    c.dias_aviso,
                    c.frecuencia_recordatorio,
                    c.hora_recordatorio,
                    c.notificar_caducadas,
                    c.resumen_mensual,
                    c.idioma,
                    c.tema,
                    c.color_acento,
                    c.formato_fecha,
                    c.animaciones_ui,
                    c.orden_garantias,
                    c.mostrar_dias_restantes,
                    c.confirmar_eliminacion,
                    c.modo_compacto
                FROM usuarios u
                LEFT JOIN opciones_configuracion c ON u.id_usuario = c.id_usuario
                WHERE u.id_usuario = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id_usuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        $fotoPerfil = !empty($usuario['foto_perfil']) ? $usuario['foto_perfil'] : 'default-avatar.png';
        $temaActual = valor($usuario, 'tema', 'claro');
        $colorActual = valor($usuario, 'color_acento', '#202bbf');
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

$notificacionesEmail = (int)valor($usuario, 'notificaciones_email', 1);
$notificacionesApp = (int)valor($usuario, 'notificaciones_app', 1);
$avisoVencimiento = (int)valor($usuario, 'aviso_vencimiento', 1);
$diasAviso = (int)valor($usuario, 'dias_aviso', 30);
$frecuenciaRecordatorio = valor($usuario, 'frecuencia_recordatorio', 'una_vez');
$horaRecordatorio = valor($usuario, 'hora_recordatorio', '09:00');
$notificarCaducadas = (int)valor($usuario, 'notificar_caducadas', 0);
$resumenMensual = (int)valor($usuario, 'resumen_mensual', 0);
$idiomaActual = valor($usuario, 'idioma', 'Español');
$formatoFechaActual = valor($usuario, 'formato_fecha', 'd/m/Y');
$animacionesUI = (int)valor($usuario, 'animaciones_ui', 1);
$ordenGarantias = valor($usuario, 'orden_garantias', 'fecha_compra_desc');
$mostrarDiasRestantes = (int)valor($usuario, 'mostrar_dias_restantes', 1);
$confirmarEliminacion = (int)valor($usuario, 'confirmar_eliminacion', 1);
$modoCompacto = (int)valor($usuario, 'modo_compacto', 0);
?>
<!DOCTYPE html>
<html lang="<?= $preferencias['idioma'] === 'Inglés' ? 'en' : 'es' ?>"
    data-theme="<?= htmlspecialchars($preferencias['tema']) ?>"
    data-animations="<?= (int)$preferencias['animaciones_ui'] ?>">

<head>
    <meta charset="UTF-8">
    <title>Configuración - TicKeep</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/preferencias.css">
    <style>
        :root {
            --accent: <?= htmlspecialchars($colorActual) ?>;
            --bg: #f3f4f6;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --danger: #dc2626;
        }

        html[data-theme="oscuro"] {
            --bg: #101827;
            --card: #172033;
            --text: #f9fafb;
            --muted: #a1a1aa;
            --border: #334155;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .settings-shell {
            max-width: 1100px;
            margin: 50px auto 0;
        }

        .settings-layout {
            display: grid;
            grid-template-columns: 270px 1fr;
            gap: 24px;
            align-items: start;
        }

        .settings-sidebar,
        .config-card {
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .08);
            border: 1px solid var(--border);
        }

        .settings-sidebar {
            padding: 22px;
            position: sticky;
            top: 95px;
        }

        .sidebar-avatar {
            width: 86px;
            height: 86px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--accent);
        }

        .sidebar-name {
            font-weight: 700;
            margin-top: 12px;
        }

        .sidebar-email {
            color: var(--muted);
            font-size: .85rem;
            word-break: break-all;
        }

        .settings-nav {
            margin-top: 24px;
            display: grid;
            gap: 8px;
        }

        .settings-nav a {
            color: var(--text);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: .92rem;
        }

        .settings-nav a:hover {
            background: rgba(32, 43, 191, .08);
            color: var(--accent);
        }

        .config-card {
            padding: 34px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--accent);
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 18px;
        }

        .profile-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
            position: relative;
        }

        .profile-img {
            width: 142px;
            height: 142px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--accent);
        }

        .edit-photo {
            position: absolute;
            bottom: 6px;
            margin-left: 104px;
            background: var(--card);
            border: 1px solid var(--border);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        }

        .page-title {
            color: var(--accent);
            font-weight: 800;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: .95rem;
            margin-bottom: 28px;
        }

        .section-title {
            color: var(--accent);
            font-weight: 800;
            margin-top: 34px;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .form-label {
            font-weight: 700;
            font-size: .9rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border-color: var(--border);
            padding: 10px 12px;
        }

        html[data-theme="oscuro"] .form-control,
        html[data-theme="oscuro"] .form-select {
            background: #111827;
            color: #f9fafb;
            border-color: #374151;
        }

        .setting-box {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
            background: rgba(148, 163, 184, .06);
            margin-bottom: 14px;
        }

        .setting-help {
            color: var(--muted);
            font-size: .84rem;
            margin-top: 3px;
        }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .accent-radio { display: none; }

        .accent-option {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 48px;
            height: 38px;
            border-radius: 8px;
            border: 2.5px solid transparent;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s, border-color .15s;
            box-shadow: 0 1px 4px rgba(0,0,0,.18);
            position: relative;
            font-size: .78rem;
            font-weight: 600;
            color: #fff;
            letter-spacing: .01em;
            white-space: nowrap;
        }
        .accent-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,.28);
        }
        .accent-radio:checked + .accent-option {
            border-color: #fff;
            box-shadow: 0 0 0 3px rgba(0,0,0,.30), 0 2px 8px rgba(0,0,0,.2);
            transform: translateY(-2px);
        }
        .accent-option .check-icon {
            display: none;
            width: 14px; height: 14px;
            flex-shrink: 0;
        }
        .accent-radio:checked + .accent-option .check-icon { display: block; }

        .btn-save-config {
            background: var(--accent);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 800;
        }

        .btn-save-config:hover {
            filter: brightness(.92);
            color: white;
        }

        .btn-logout-red {
            background: var(--danger);
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-logout-red:hover {
            background: #b91c1c;
            color: white;
        }

        .security-note {
            background: rgba(32, 43, 191, .08);
            border-left: 4px solid var(--accent);
            padding: 12px;
            border-radius: 10px;
            color: var(--muted);
            font-size: .88rem;
        }

        footer {
            background: #202bbf;
            color: white;
            text-align: center;
            padding: 18px;
            font-size: .8rem;
            margin-top: 80px;
        }

        @media (max-width: 900px) {
            .settings-layout {
                grid-template-columns: 1fr;
            }

            .settings-sidebar {
                position: static;
            }
        }
    </style>
</head>

<body>

    <header class="tk-header">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="tk-logo">TicKeep</a>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white d-none d-sm-block"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></span>
                <img src="assets/img/<?= htmlspecialchars($fotoPerfil); ?>" class="avatar-img" alt="Perfil">
            </div>
        </div>
    </header>

    <main class="container">
        <div class="settings-shell">
            <a href="index.php" class="back-link">← Volver a mis garantías</a>

            <div class="settings-layout">
                <aside class="settings-sidebar">
                    <div class="text-center">
                        <img src="assets/img/<?= htmlspecialchars($fotoPerfil); ?>" class="sidebar-avatar" alt="Foto perfil">
                        <div class="sidebar-name"><?= htmlspecialchars($usuario['nombre']) ?></div>
                        <div class="sidebar-email"><?= htmlspecialchars($usuario['email']) ?></div>
                    </div>

                    <nav class="settings-nav">
                        <a href="#perfil">Perfil</a>
                        <a href="#seguridad">Seguridad</a>
                        <a href="#notificaciones">Notificaciones</a>
                        <a href="#apariencia">Apariencia</a>
                        <a href="#gestion">Gestión de garantías</a>
                    </nav>
                </aside>

                <section class="config-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="profile-wrapper">
                            <img src="assets/img/<?= htmlspecialchars($fotoPerfil); ?>" class="profile-img" alt="Foto perfil">
                            <label for="foto_perfil" class="edit-photo" title="Cambiar foto">✎</label>
                            <input type="file" id="foto_perfil" name="foto_perfil" accept=".jpg,.jpeg,.png,.webp" hidden>
                        </div>

                        <h2 class="page-title">Configuración</h2>
                        <p class="page-subtitle">
                            Personaliza tu cuenta y el comportamiento de TicKeep con opciones útiles y prácticas.
                        </p>

                        <?php if ($mensaje !== ''): ?>
                            <div class="alert alert-<?= htmlspecialchars($tipo_alerta) ?>">
                                <?= htmlspecialchars($mensaje) ?>
                            </div>
                        <?php endif; ?>

                        <h5 class="section-title" id="perfil">Perfil de usuario</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre de usuario</label>
                                <input type="text" name="nombre" class="form-control"
                                    value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                <div class="setting-help">Nombre visible dentro de la aplicación.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo electrónico</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                <div class="setting-help">Correo usado para avisos y acceso a la cuenta.</div>
                            </div>
                        </div>

                        <h5 class="section-title" id="seguridad">Seguridad</h5>

                        <div class="security-note mb-3">
                            Si quieres cambiar la contraseña, escribe primero la actual. Si no, deja estos campos vacíos.
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Contraseña actual</label>
                                <input type="password" name="password_actual" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" name="password_nueva" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" name="password_confirmar" class="form-control">
                            </div>
                        </div>

                        <h5 class="section-title" id="notificaciones">Notificaciones</h5>

                        <div class="setting-box">
                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                <div>
                                    <label class="form-check-label fw-bold" for="notificaciones_email">
                                        Notificaciones por correo
                                    </label>
                                    <div class="setting-help">
                                        Activa o desactiva el envío de emails desde la aplicación.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="notificaciones_email"
                                    name="notificaciones_email" <?= $notificacionesEmail ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="setting-box">
                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                <div>
                                    <label class="form-check-label fw-bold" for="notificaciones_app">
                                        Notificaciones dentro de la app
                                    </label>
                                    <div class="setting-help">
                                        Preparado para mostrar avisos internos en el panel principal.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="notificaciones_app"
                                    name="notificaciones_app" <?= $notificacionesApp ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="setting-box" id="bloqueAvisos">
                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                                <div>
                                    <label class="form-check-label fw-bold" for="aviso_vencimiento">
                                        Avisar cuando una garantía vaya a expirar pronto
                                    </label>
                                    <div class="setting-help">
                                        Te permite definir con cuánta antelación quieres recibir el aviso.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="aviso_vencimiento"
                                    name="aviso_vencimiento" <?= $avisoVencimiento ? 'checked' : '' ?>>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Antelación del aviso</label>
                                    <select name="dias_aviso" id="dias_aviso" class="form-select">
                                        <option value="7" <?= $diasAviso === 7 ? 'selected' : '' ?>>7 días</option>
                                        <option value="15" <?= $diasAviso === 15 ? 'selected' : '' ?>>15 días</option>
                                        <option value="30" <?= $diasAviso === 30 ? 'selected' : '' ?>>30 días</option>
                                        <option value="60" <?= $diasAviso === 60 ? 'selected' : '' ?>>60 días</option>
                                        <option value="90" <?= $diasAviso === 90 ? 'selected' : '' ?>>90 días</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Frecuencia del recordatorio</label>
                                    <select name="frecuencia_recordatorio" id="frecuencia_recordatorio" class="form-select">
                                        <option value="una_vez" <?= $frecuenciaRecordatorio === 'una_vez' ? 'selected' : '' ?>>Solo una vez</option>
                                        <option value="semanal" <?= $frecuenciaRecordatorio === 'semanal' ? 'selected' : '' ?>>Semanal</option>
                                        <option value="diario" <?= $frecuenciaRecordatorio === 'diario' ? 'selected' : '' ?>>Diario</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Hora preferida del aviso</label>
                                    <input type="time" name="hora_recordatorio" id="hora_recordatorio" class="form-control"
                                        value="<?= htmlspecialchars($horaRecordatorio) ?>">
                                </div>
                            </div>

                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0 mb-3">
                                <div>
                                    <label class="form-check-label fw-bold" for="notificar_caducadas">
                                        Avisar también cuando una garantía ya haya caducado
                                    </label>
                                    <div class="setting-help">
                                        Útil para tener control de las garantías que ya vencieron.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="notificar_caducadas"
                                    name="notificar_caducadas" <?= $notificarCaducadas ? 'checked' : '' ?>>
                            </div>

                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                <div>
                                    <label class="form-check-label fw-bold" for="resumen_mensual">
                                        Recibir resumen mensual
                                    </label>
                                    <div class="setting-help">
                                        Resumen con garantías vigentes, próximas a vencer y caducadas.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="resumen_mensual"
                                    name="resumen_mensual" <?= $resumenMensual ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <h5 class="section-title" id="apariencia">Apariencia</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Idioma</label>
                                <select name="idioma" class="form-select">
                                    <option value="Español" <?= $idiomaActual === 'Español' ? 'selected' : '' ?>>Español</option>
                                    <option value="Inglés" <?= $idiomaActual === 'Inglés' ? 'selected' : '' ?>>Inglés</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tema</label>
                                <select name="tema" id="tema" class="form-select">
                                    <option value="claro" <?= $temaActual === 'claro' ? 'selected' : '' ?>>Claro</option>
                                    <option value="oscuro" <?= $temaActual === 'oscuro' ? 'selected' : '' ?>>Oscuro</option>
                                    <option value="sistema" <?= $temaActual === 'sistema' ? 'selected' : '' ?>>Según el sistema</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Formato de fecha</label>
                                <select name="formato_fecha" class="form-select">
                                    <option value="d/m/Y" <?= $formatoFechaActual === 'd/m/Y' ? 'selected' : '' ?>>31/12/2026</option>
                                    <option value="Y-m-d" <?= $formatoFechaActual === 'Y-m-d' ? 'selected' : '' ?>>2026-12-31</option>
                                    <option value="m/d/Y" <?= $formatoFechaActual === 'm/d/Y' ? 'selected' : '' ?>>12/31/2026</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color principal</label>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php
                                $colores = ['#202bbf', '#0d7fc0', '#16a34a', '#d97706', '#dc2626', '#7c3aed', '#111827'];
                                foreach ($colores as $color):
                                ?>
                                    <label>
                                        <input class="accent-radio" type="radio" name="color_acento" value="<?= htmlspecialchars($color) ?>"
                                            <?= strtolower($colorActual) === strtolower($color) ? 'checked' : '' ?>>
                                        <span class="accent-option" style="background:<?= htmlspecialchars($color) ?>">
                                            <svg class="check-icon" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <h5 class="section-title" id="gestion">Gestión de garantías</h5>

                        <div class="mb-3">
                            <label class="form-label">Ordenar garantías por</label>
                            <select name="orden_garantias" class="form-select">
                                <option value="fecha_compra_desc" <?= $ordenGarantias === 'fecha_compra_desc' ? 'selected' : '' ?>>Fecha de compra (más reciente primero)</option>
                                <option value="fecha_compra_asc" <?= $ordenGarantias === 'fecha_compra_asc' ? 'selected' : '' ?>>Fecha de compra (más antigua primero)</option>
                                <option value="fecha_vencimiento_asc" <?= $ordenGarantias === 'fecha_vencimiento_asc' ? 'selected' : '' ?>>Fecha de vencimiento (más próxima primero)</option>
                                <option value="fecha_vencimiento_desc" <?= $ordenGarantias === 'fecha_vencimiento_desc' ? 'selected' : '' ?>>Fecha de vencimiento (más lejana primero)</option>
                                <option value="nombre_asc" <?= $ordenGarantias === 'nombre_asc' ? 'selected' : '' ?>>Nombre (A-Z)</option>
                                <option value="nombre_desc" <?= $ordenGarantias === 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z-A)</option>
                            </select>
                        </div>

                        <div class="setting-box">
                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                <div>
                                    <label class="form-check-label fw-bold" for="mostrar_dias_restantes">
                                        Mostrar días restantes hasta el vencimiento
                                    </label>
                                    <div class="setting-help">
                                        Muy útil para ver rápidamente cuánto tiempo le queda a cada garantía.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="mostrar_dias_restantes"
                                    name="mostrar_dias_restantes" <?= $mostrarDiasRestantes ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="setting-box">
                            <div class="form-check form-switch d-flex justify-content-between align-items-center ps-0">
                                <div>
                                    <label class="form-check-label fw-bold" for="confirmar_eliminacion">
                                        Pedir confirmación al borrar una garantía
                                    </label>
                                    <div class="setting-help">
                                        Recomendado para evitar borrar garantías por error.
                                    </div>
                                </div>
                                <input class="form-check-input ms-3" type="checkbox" id="confirmar_eliminacion"
                                    name="confirmar_eliminacion" <?= $confirmarEliminacion ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
                            <a href="logout.php" class="btn-logout-red">Cerrar sesión</a>

                            <button type="submit" class="btn btn-save-config">
                                Guardar configuración
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>

    <footer>
        © 2025 TicKeep. Todos los derechos reservados.<br>
        Tu tranquilidad, garantizada.
    </footer>

    <script>
        const notificacionesEmail = document.getElementById('notificaciones_email');
        const avisoVencimiento = document.getElementById('aviso_vencimiento');
        const diasAviso = document.getElementById('dias_aviso');
        const frecuencia = document.getElementById('frecuencia_recordatorio');
        const horaRecordatorio = document.getElementById('hora_recordatorio');
        const notificarCaducadas = document.getElementById('notificar_caducadas');
        const resumenMensual = document.getElementById('resumen_mensual');

        function actualizarOpcionesCorreo() {
            const emailActivo = notificacionesEmail.checked;

            avisoVencimiento.disabled = !emailActivo;
            diasAviso.disabled = !emailActivo || !avisoVencimiento.checked;
            frecuencia.disabled = !emailActivo || !avisoVencimiento.checked;
            horaRecordatorio.disabled = !emailActivo || !avisoVencimiento.checked;
            notificarCaducadas.disabled = !emailActivo;
            resumenMensual.disabled = !emailActivo;
        }

        notificacionesEmail.addEventListener('change', actualizarOpcionesCorreo);
        avisoVencimiento.addEventListener('change', actualizarOpcionesCorreo);
        actualizarOpcionesCorreo();

        document.querySelectorAll('input[name="color_acento"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.documentElement.style.setProperty('--accent', radio.value);
            });
        });

        function aplicarTema(theme) {
            if (theme === 'sistema') {
                const oscuroSistema = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', oscuroSistema ? 'oscuro' : 'claro');
            } else {
                document.documentElement.setAttribute('data-theme', theme);
            }
        }

        const temaSelect = document.getElementById('tema');
        temaSelect.addEventListener('change', function() {
            aplicarTema(this.value);
        });

        aplicarTema(temaSelect.value);
    </script>

</body>

</html>