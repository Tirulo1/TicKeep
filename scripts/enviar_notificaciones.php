<?php
require __DIR__ . '/../config/bd.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailConfig = require __DIR__ . '/../config/mail.php';

function enviarCorreo($mailConfig, $destino, $nombreDestino, $asunto, $html)
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = $mailConfig['secure'];
    $mail->Port = $mailConfig['port'];

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($destino, $nombreDestino);

    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body = $html;
    $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));

    $mail->send();
}

function yaSeEnvio($pdo, $id_usuario, $id_garantia, $tipo, $periodoClave = null)
{
    $sql = "SELECT COUNT(*) FROM notificaciones_enviadas
            WHERE id_usuario = :id_usuario
            AND tipo = :tipo";

    $params = [
        ':id_usuario' => $id_usuario,
        ':tipo' => $tipo
    ];

    if ($id_garantia !== null) {
        $sql .= " AND id_garantia = :id_garantia";
        $params[':id_garantia'] = $id_garantia;
    }

    if ($periodoClave !== null) {
        $sql .= " AND periodo_clave = :periodo";
        $params[':periodo'] = $periodoClave;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn() > 0;
}

function registrarEnvio($pdo, $id_usuario, $id_garantia, $tipo, $periodoClave = null)
{
    $stmt = $pdo->prepare("INSERT INTO notificaciones_enviadas
                           (id_usuario, id_garantia, tipo, fecha_envio, periodo_clave)
                           VALUES
                           (:id_usuario, :id_garantia, :tipo, NOW(), :periodo)");

    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_garantia' => $id_garantia,
        ':tipo' => $tipo,
        ':periodo' => $periodoClave
    ]);
}

function puedeEnviarPorFrecuencia($pdo, $id_usuario, $id_garantia, $tipo, $frecuencia)
{
    if ($frecuencia === 'una_vez') {
        return !yaSeEnvio($pdo, $id_usuario, $id_garantia, $tipo);
    }

    $stmt = $pdo->prepare("SELECT fecha_envio FROM notificaciones_enviadas
                           WHERE id_usuario = :id_usuario
                           AND id_garantia = :id_garantia
                           AND tipo = :tipo
                           ORDER BY fecha_envio DESC
                           LIMIT 1");

    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_garantia' => $id_garantia,
        ':tipo' => $tipo
    ]);

    $ultima = $stmt->fetchColumn();

    if (!$ultima) {
        return true;
    }

    $ultimaFecha = new DateTime($ultima);
    $ahora = new DateTime();
    $dias = (int)$ultimaFecha->diff($ahora)->format('%a');

    if ($frecuencia === 'diario') {
        return $dias >= 1;
    }

    if ($frecuencia === 'semanal') {
        return $dias >= 7;
    }

    return false;
}

function calcularDiasRestantes($fechaVencimiento)
{
    $hoy = new DateTime('today');
    $vencimiento = new DateTime($fechaVencimiento);

    return (int)$hoy->diff($vencimiento)->format('%r%a');
}

function limpiar($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function plantillaGarantiaVencePronto($nombreUsuario, $nombreProducto, $tienda, $fechaVencimiento, $diasRestantes, $enlaceApp)
{
    $asuntoProducto = $nombreProducto !== '' ? $nombreProducto : 'tu garantía';

    if ($diasRestantes <= 0) {
        $asunto = "TicKeep | La garantía de {$asuntoProducto} vence hoy";
        $estadoTexto = "Vence hoy";
        $colorEstado = "#dc2626";
        $textoDiasRestantes = "Vence hoy";
    } elseif ($diasRestantes === 1) {
        $asunto = "TicKeep | La garantía de {$asuntoProducto} vence mañana";
        $estadoTexto = "Vence mañana";
        $colorEstado = "#d97706";
        $textoDiasRestantes = "1 día";
    } else {
        $asunto = "TicKeep | La garantía de {$asuntoProducto} vence en {$diasRestantes} días";
        $estadoTexto = "Expira pronto";
        $colorEstado = "#d97706";
        $textoDiasRestantes = $diasRestantes . " días";
    }

    $nombreUsuarioSeguro = limpiar($nombreUsuario ?: 'usuario');
    $nombreProductoSeguro = limpiar($nombreProducto ?: 'Producto sin nombre');
    $tiendaSegura = limpiar($tienda ?: 'No especificada');
    $fechaVencimientoSegura = limpiar($fechaVencimiento);
    $estadoTextoSeguro = limpiar($estadoTexto);
    $textoDiasRestantesSeguro = limpiar($textoDiasRestantes);
    $enlaceAppSeguro = limpiar($enlaceApp);

    $html = '
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Notificación TicKeep</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
    Recordatorio de TicKeep: una de tus garantías está próxima a vencer.
  </div>

  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f6fb; margin:0; padding:0;">
    <tr>
      <td align="center" style="padding:30px 15px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:650px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);">

          <tr>
            <td style="background:linear-gradient(135deg, #202bbf, #0d7fc0); padding:28px 30px; text-align:center;">
              <h1 style="margin:0; font-size:28px; color:#ffffff; font-weight:700;">TicKeep</h1>
              <p style="margin:8px 0 0; font-size:14px; color:#dbeafe;">Tu tranquilidad, garantizada</p>
            </td>
          </tr>

          <tr>
            <td style="padding:35px 30px 20px;">
              <p style="margin:0 0 18px; font-size:18px; color:#111827;">
                Hola, <strong>' . $nombreUsuarioSeguro . '</strong>
              </p>

              <p style="margin:0 0 22px; font-size:16px; line-height:1.6; color:#374151;">
                Te avisamos de que una de tus garantías está próxima a vencer. Aquí tienes el resumen:
              </p>

              <div style="display:inline-block; background:' . $colorEstado . '; color:#ffffff; padding:8px 14px; border-radius:999px; font-size:13px; font-weight:bold; margin-bottom:20px;">
                ' . $estadoTextoSeguro . '
              </div>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                <tr>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; width:38%; font-weight:bold; color:#111827;">Producto</td>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; color:#374151;">' . $nombreProductoSeguro . '</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; font-weight:bold; color:#111827;">Tienda</td>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; color:#374151;">' . $tiendaSegura . '</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; font-weight:bold; color:#111827;">Fecha de vencimiento</td>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; color:#374151;">' . $fechaVencimientoSegura . '</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px; font-weight:bold; color:#111827;">Días restantes</td>
                  <td style="padding:14px 18px; color:#374151;">' . $textoDiasRestantesSeguro . '</td>
                </tr>
              </table>

              <p style="margin:25px 0 0; font-size:15px; line-height:1.6; color:#4b5563;">
                Te recomendamos revisar esta garantía en tu cuenta para comprobar su estado y guardar cualquier dato importante antes de que expire.
              </p>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding:10px 30px 35px;">
              <a href="' . $enlaceAppSeguro . '"
                 style="display:inline-block; background:#202bbf; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:10px; font-size:15px; font-weight:bold;">
                 Ver mi garantía
              </a>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 30px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
              <p style="margin:0 0 8px; font-size:13px; color:#6b7280;">
                Has recibido este correo porque tienes activadas las notificaciones en TicKeep.
              </p>
              <p style="margin:0; font-size:12px; color:#9ca3af;">
                © 2026 TicKeep. Todos los derechos reservados.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    return [
        'asunto' => $asunto,
        'html' => $html
    ];
}

function plantillaGarantiaCaducada($nombreUsuario, $nombreProducto, $tienda, $fechaVencimiento, $enlaceApp)
{
    $nombreUsuarioSeguro = limpiar($nombreUsuario ?: 'usuario');
    $nombreProductoSeguro = limpiar($nombreProducto ?: 'Producto sin nombre');
    $tiendaSegura = limpiar($tienda ?: 'No especificada');
    $fechaVencimientoSegura = limpiar($fechaVencimiento);
    $enlaceAppSeguro = limpiar($enlaceApp);

    $asunto = "TicKeep | La garantía de {$nombreProductoSeguro} ha caducado";

    $html = '
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Garantía caducada - TicKeep</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f6fb;">
    <tr>
      <td align="center" style="padding:30px 15px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:650px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);">
          <tr>
            <td style="background:linear-gradient(135deg, #991b1b, #dc2626); padding:28px 30px; text-align:center;">
              <h1 style="margin:0; font-size:28px; color:#ffffff;">TicKeep</h1>
              <p style="margin:8px 0 0; color:#fee2e2;">Aviso de garantía caducada</p>
            </td>
          </tr>

          <tr>
            <td style="padding:35px 30px;">
              <p style="font-size:18px;">Hola, <strong>' . $nombreUsuarioSeguro . '</strong></p>
              <p style="font-size:16px; line-height:1.6; color:#374151;">
                La garantía de <strong>' . $nombreProductoSeguro . '</strong> ya ha caducado.
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                <tr>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; font-weight:bold;">Producto</td>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb;">' . $nombreProductoSeguro . '</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb; font-weight:bold;">Tienda</td>
                  <td style="padding:14px 18px; border-bottom:1px solid #e5e7eb;">' . $tiendaSegura . '</td>
                </tr>
                <tr>
                  <td style="padding:14px 18px; font-weight:bold;">Fecha de vencimiento</td>
                  <td style="padding:14px 18px;">' . $fechaVencimientoSegura . '</td>
                </tr>
              </table>

              <div style="text-align:center; margin-top:28px;">
                <a href="' . $enlaceAppSeguro . '" style="display:inline-block; background:#dc2626; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:10px; font-weight:bold;">
                  Revisar garantía
                </a>
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 30px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
              <p style="margin:0; font-size:12px; color:#9ca3af;">© 2026 TicKeep. Todos los derechos reservados.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    return [
        'asunto' => $asunto,
        'html' => $html
    ];
}

function plantillaResumenMensual($nombreUsuario, $resumen, $enlaceApp)
{
    $nombreUsuarioSeguro = limpiar($nombreUsuario ?: 'usuario');
    $enlaceAppSeguro = limpiar($enlaceApp);

    $lineas = "";

    foreach ($resumen as $r) {
        $estado = limpiar($r['estado']);
        $total = (int)$r['total'];
        $lineas .= "
            <tr>
                <td style='padding:12px 16px; border-bottom:1px solid #e5e7eb; font-weight:bold;'>{$estado}</td>
                <td style='padding:12px 16px; border-bottom:1px solid #e5e7eb;'>{$total}</td>
            </tr>
        ";
    }

    if ($lineas === "") {
        $lineas = "
            <tr>
                <td colspan='2' style='padding:12px 16px; border-bottom:1px solid #e5e7eb;'>No tienes garantías registradas actualmente.</td>
            </tr>
        ";
    }

    $asunto = "TicKeep | Resumen mensual de tus garantías";

    $html = '
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Resumen mensual - TicKeep</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6fb; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f6fb;">
    <tr>
      <td align="center" style="padding:30px 15px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:650px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);">
          <tr>
            <td style="background:linear-gradient(135deg, #202bbf, #0d7fc0); padding:28px 30px; text-align:center;">
              <h1 style="margin:0; font-size:28px; color:#ffffff;">TicKeep</h1>
              <p style="margin:8px 0 0; color:#dbeafe;">Resumen mensual de garantías</p>
            </td>
          </tr>

          <tr>
            <td style="padding:35px 30px;">
              <p style="font-size:18px;">Hola, <strong>' . $nombreUsuarioSeguro . '</strong></p>
              <p style="font-size:16px; line-height:1.6; color:#374151;">
                Este es el resumen mensual de tus garantías:
              </p>

              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
                <tr>
                    <td style="padding:12px 16px; background:#202bbf; color:white; font-weight:bold;">Estado</td>
                    <td style="padding:12px 16px; background:#202bbf; color:white; font-weight:bold;">Total</td>
                </tr>
                ' . $lineas . '
              </table>

              <div style="text-align:center; margin-top:28px;">
                <a href="' . $enlaceAppSeguro . '" style="display:inline-block; background:#202bbf; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:10px; font-weight:bold;">
                  Ver mis garantías
                </a>
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 30px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
              <p style="margin:0; font-size:12px; color:#9ca3af;">© 2026 TicKeep. Todos los derechos reservados.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>';

    return [
        'asunto' => $asunto,
        'html' => $html
    ];
}

$horaActual = date('H:i');
$enlaceApp = 'http://localhost/dashboard/index.php';

$sqlUsuarios = "SELECT 
                    u.id_usuario,
                    u.nombre,
                    u.email,
                    c.notificaciones_email,
                    c.aviso_vencimiento,
                    c.dias_aviso,
                    c.frecuencia_recordatorio,
                    c.hora_recordatorio,
                    c.notificar_caducadas,
                    c.resumen_mensual
                FROM usuarios u
                INNER JOIN opciones_configuracion c ON u.id_usuario = c.id_usuario
                WHERE c.notificaciones_email = 1";

$stmtUsuarios = $pdo->query($sqlUsuarios);
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

foreach ($usuarios as $usuario) {
    $idUsuario = (int)$usuario['id_usuario'];
    $horaPreferida = $usuario['hora_recordatorio'] ?: '09:00';

    if ($horaActual < $horaPreferida) {
        continue;
    }

    if ((int)$usuario['aviso_vencimiento'] === 1) {
        $diasAviso = (int)$usuario['dias_aviso'];

        $sqlGarantias = "SELECT *
                         FROM garantias
                         WHERE id_usuario = :id
                         AND fecha_vencimiento >= CURDATE()
                         AND DATEDIFF(fecha_vencimiento, CURDATE()) <= :dias";

        $stmtGarantias = $pdo->prepare($sqlGarantias);
        $stmtGarantias->execute([
            ':id' => $idUsuario,
            ':dias' => $diasAviso
        ]);

        $garantias = $stmtGarantias->fetchAll(PDO::FETCH_ASSOC);

        foreach ($garantias as $g) {
            $frecuencia = $usuario['frecuencia_recordatorio'] ?: 'una_vez';

            if (!puedeEnviarPorFrecuencia($pdo, $idUsuario, $g['id_garantia'], 'vencimiento', $frecuencia)) {
                continue;
            }

            $diasRestantes = calcularDiasRestantes($g['fecha_vencimiento']);

            $nombreUsuario = $usuario['nombre'] ?? 'usuario';
            $nombreProducto = $g['nombre_producto'] ?? 'tu garantía';
            $tienda = $g['tienda'] ?? 'No especificada';
            $fechaVencimiento = date('d/m/Y', strtotime($g['fecha_vencimiento']));

            $correo = plantillaGarantiaVencePronto(
                $nombreUsuario,
                $nombreProducto,
                $tienda,
                $fechaVencimiento,
                $diasRestantes,
                $enlaceApp
            );

            enviarCorreo($mailConfig, $usuario['email'], $usuario['nombre'], $correo['asunto'], $correo['html']);
            registrarEnvio($pdo, $idUsuario, $g['id_garantia'], 'vencimiento');

            echo "Enviado aviso de vencimiento a {$usuario['email']} para {$g['nombre_producto']}\n";
        }
    }

    if ((int)$usuario['notificar_caducadas'] === 1) {
        $sqlCaducadas = "SELECT *
                         FROM garantias
                         WHERE id_usuario = :id
                         AND fecha_vencimiento < CURDATE()";

        $stmtCaducadas = $pdo->prepare($sqlCaducadas);
        $stmtCaducadas->execute([':id' => $idUsuario]);
        $caducadas = $stmtCaducadas->fetchAll(PDO::FETCH_ASSOC);

        foreach ($caducadas as $g) {
            if (yaSeEnvio($pdo, $idUsuario, $g['id_garantia'], 'caducada')) {
                continue;
            }

            $nombreUsuario = $usuario['nombre'] ?? 'usuario';
            $nombreProducto = $g['nombre_producto'] ?? 'tu garantía';
            $tienda = $g['tienda'] ?? 'No especificada';
            $fechaVencimiento = date('d/m/Y', strtotime($g['fecha_vencimiento']));

            $correo = plantillaGarantiaCaducada(
                $nombreUsuario,
                $nombreProducto,
                $tienda,
                $fechaVencimiento,
                $enlaceApp
            );

            enviarCorreo($mailConfig, $usuario['email'], $usuario['nombre'], $correo['asunto'], $correo['html']);
            registrarEnvio($pdo, $idUsuario, $g['id_garantia'], 'caducada');

            echo "Enviado aviso de garantía caducada a {$usuario['email']} para {$g['nombre_producto']}\n";
        }
    }

    if ((int)$usuario['resumen_mensual'] === 1) {
        $periodo = date('Y-m');

        if (!yaSeEnvio($pdo, $idUsuario, null, 'resumen_mensual', $periodo)) {
            $stmtResumen = $pdo->prepare("SELECT estado, COUNT(*) total
                                          FROM garantias
                                          WHERE id_usuario = :id
                                          GROUP BY estado");

            $stmtResumen->execute([':id' => $idUsuario]);
            $resumen = $stmtResumen->fetchAll(PDO::FETCH_ASSOC);

            $correo = plantillaResumenMensual(
                $usuario['nombre'],
                $resumen,
                $enlaceApp
            );

            enviarCorreo($mailConfig, $usuario['email'], $usuario['nombre'], $correo['asunto'], $correo['html']);
            registrarEnvio($pdo, $idUsuario, null, 'resumen_mensual', $periodo);

            echo "Enviado resumen mensual a {$usuario['email']}\n";
        }
    }
}