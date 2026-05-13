<?php
// --- ENVIAR_NOTIFICACIONES.PHP listo para Railway ---

// --- PROTECCIÓN CRON ---
$tokenEsperado = getenv('CRON_TOKEN') ?: '';
$tokenRecibido = $_GET['token'] ?? '';

if ($tokenEsperado === '' || !hash_equals($tokenEsperado, $tokenRecibido)) {
    http_response_code(403);
    echo "Acceso denegado.\n";
    exit();
}

// --- CARGAR CONFIGURACIONES ---
require __DIR__ . '/../config/bd.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailConfig = require __DIR__ . '/../config/mail.php';

// Ruta del log
$logFile = __DIR__ . '/../logs/cron.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logCron($mensaje) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s')." - $mensaje\n", FILE_APPEND);
}

// --- FUNCIONES ---
function enviarCorreo($mailConfig, $destino, $nombreDestino, $asunto, $html)
{
    $mail = new PHPMailer(true);
    try {
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
        return true;
    } catch (Exception $e) {
        global $logFile;
        logCron("Error enviando correo a $destino: ".$mail->ErrorInfo);
        return false;
    }
}

// --- FUNCIONES AUXILIARES ---
function yaSeEnvio($pdo, $id_usuario, $id_garantia, $tipo, $periodoClave = null) {
    $sql = "SELECT COUNT(*) FROM notificaciones_enviadas WHERE id_usuario = :id_usuario AND tipo = :tipo";
    $params = [':id_usuario'=>$id_usuario, ':tipo'=>$tipo];
    if ($id_garantia!==null) { $sql.=" AND id_garantia = :id_garantia"; $params[':id_garantia']=$id_garantia; }
    if ($periodoClave!==null) { $sql.=" AND periodo_clave = :periodo"; $params[':periodo']=$periodoClave; }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn() > 0;
}

function registrarEnvio($pdo, $id_usuario, $id_garantia, $tipo, $periodoClave=null) {
    $stmt = $pdo->prepare("INSERT INTO notificaciones_enviadas (id_usuario,id_garantia,tipo,fecha_envio,periodo_clave) VALUES (:id_usuario,:id_garantia,:tipo,NOW(),:periodo)");
    $stmt->execute([
        ':id_usuario'=>$id_usuario,
        ':id_garantia'=>$id_garantia,
        ':tipo'=>$tipo,
        ':periodo'=>$periodoClave
    ]);
}

function calcularDiasRestantes($fechaVencimiento){
    $hoy = new DateTime('today');
    $vencimiento = new DateTime($fechaVencimiento);
    return (int)$hoy->diff($vencimiento)->format('%r%a');
}

function limpiar($texto){ return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }

// --- PLANTILLAS DE CORREO ---
// (Aquí puedes copiar tus funciones plantillaGarantiaVencePronto, plantillaGarantiaCaducada y plantillaResumenMensual)
// Asegúrate de que la variable $enlaceApp use getenv('APP_URL')

// URL de la app
$enlaceApp = getenv('APP_URL') ?: 'http://localhost/ULTIMO TICKEEP/index.php';
$horaActual = date('H:i');

// --- EJECUCIÓN PRINCIPAL ---
try {
    $sqlUsuarios = "SELECT u.id_usuario,u.nombre,u.email,c.notificaciones_email,c.aviso_vencimiento,
                    c.dias_aviso,c.frecuencia_recordatorio,c.hora_recordatorio,c.notificar_caducadas,c.resumen_mensual
                    FROM usuarios u INNER JOIN opciones_configuracion c ON u.id_usuario=c.id_usuario
                    WHERE c.notificaciones_email=1";
    $stmtUsuarios = $pdo->query($sqlUsuarios);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        $idUsuario = (int)$usuario['id_usuario'];
        $horaPreferida = $usuario['hora_recordatorio'] ?: '09:00';
        if ($horaActual < $horaPreferida) continue;

        // Aquí llamas a tus funciones para avisos, caducadas y resumen mensual
        // y usas enviarCorreo(...) y registrarEnvio(...)
        // Ejemplo:
        logCron("Procesando usuario {$usuario['email']}");
    }

    logCron("Cron ejecutado correctamente.");

} catch(Exception $e){
    logCron("Error en cron: ".$e->getMessage());
}