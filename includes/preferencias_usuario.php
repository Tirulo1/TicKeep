<?php

$preferencias = [
    'tema' => 'claro',
    'color_acento' => '#202bbf',
    'idioma' => 'Español',
    'formato_fecha' => 'd/m/Y',
    'animaciones_ui' => 1,
    'orden_garantias' => 'fecha_compra_desc',
    'mostrar_dias_restantes' => 1,
    'confirmar_eliminacion' => 1,
    'modo_compacto' => 0,
    'notificaciones_email' => 1,
    'notificaciones_app' => 1,
    'aviso_vencimiento' => 1,
    'dias_aviso' => 30,
    'frecuencia_recordatorio' => 'una_vez',
    'hora_recordatorio' => '09:00',
    'notificar_caducadas' => 0,
    'resumen_mensual' => 0
];

if (isset($_SESSION['id_usuario'])) {
    $stmtPref = $pdo->prepare("SELECT * FROM opciones_configuracion WHERE id_usuario = :id");
    $stmtPref->execute([':id' => $_SESSION['id_usuario']]);
    $prefsBD = $stmtPref->fetch(PDO::FETCH_ASSOC);

    if ($prefsBD) {
        foreach ($preferencias as $clave => $valorDefecto) {
            if (array_key_exists($clave, $prefsBD) && $prefsBD[$clave] !== null) {
                $preferencias[$clave] = $prefsBD[$clave];
            }
        }
    }
}

function fechaTickeep($fecha, $preferencias)
{
    if (empty($fecha)) {
        return '';
    }

    return date($preferencias['formato_fecha'] ?? 'd/m/Y', strtotime($fecha));
}

function diasRestantesGarantia($fechaVencimiento)
{
    $hoy = new DateTime();
    $vencimiento = new DateTime($fechaVencimiento);
    return (int)$hoy->diff($vencimiento)->format('%r%a');
}

function ordenGarantiasSQL($preferencias)
{
    $orden = $preferencias['orden_garantias'] ?? 'fecha_compra_desc';

    return match ($orden) {
        'fecha_compra_asc' => 'fecha_compra ASC',
        'fecha_vencimiento_asc' => 'fecha_vencimiento ASC',
        'fecha_vencimiento_desc' => 'fecha_vencimiento DESC',
        'nombre_asc' => 'nombre_producto ASC',
        'nombre_desc' => 'nombre_producto DESC',
        default => 'fecha_compra DESC'
    };
}