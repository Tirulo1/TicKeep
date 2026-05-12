<?php
require 'config/bd.php';
require 'config/mail.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailConfig = require 'config/mail.php';

$mensaje     = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje     = "Introduce un email válido.";
        $tipo_alerta = "danger";
    } else {
        // Buscar usuario (siempre mostramos el mismo mensaje por seguridad)
        $stmt = $pdo->prepare("SELECT id_usuario, nombre FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Generar token seguro
            $token   = bin2hex(random_bytes(32));
            $expira  = date('Y-m-d H:i:s', time() + 3600); // 1 hora

            // Guardar token en BD
            $pdo->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);
            $pdo->prepare("INSERT INTO password_resets (email, token, expira_en) VALUES (:email, :token, :expira)")
                ->execute([':email' => $email, ':token' => $token, ':expira' => $expira]);

            // Construir URL de reset
            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host      = $_SERVER['HTTP_HOST'];
            $resetUrl  = "$protocolo://$host/restablecer-contrasena.php?token=$token";

            // Enviar email con PHPMailer
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $mailConfig['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailConfig['username'];
                $mail->Password   = $mailConfig['password'];
                $mail->SMTPSecure = $mailConfig['secure'];
                $mail->Port       = $mailConfig['port'];
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
                $mail->addAddress($email, $usuario['nombre']);

                $mail->isHTML(true);
                $mail->Subject = 'Recupera tu contraseña de TicKeep';
                $mail->Body    = "
                <!DOCTYPE html>
                <html lang='es'>
                <head><meta charset='UTF-8'></head>
                <body style='font-family:Poppins,Arial,sans-serif;background:#F0F2F5;margin:0;padding:2rem 1rem;'>
                  <div style='max-width:480px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.1)'>
                    <div style='background:linear-gradient(135deg,#1D4ED8,#1e3a8a);padding:1.5rem 2rem;text-align:center'>
                      <span style='color:#fff;font-size:1.8rem;font-weight:700;letter-spacing:-0.5px'>TicKeep</span>
                    </div>
                    <div style='padding:2rem 2.25rem'>
                      <h2 style='color:#111827;font-size:1.2rem;font-weight:700;margin:0 0 .75rem'>Hola, " . htmlspecialchars($usuario['nombre']) . " 👋</h2>
                      <p style='color:#374151;font-size:.93rem;line-height:1.6;margin:0 0 1.5rem'>
                        Hemos recibido una solicitud para restablecer la contraseña de tu cuenta de TicKeep.
                        Si no has sido tú, puedes ignorar este email.
                      </p>
                      <div style='text-align:center;margin:1.5rem 0'>
                        <a href='$resetUrl'
                           style='display:inline-block;background:#1D4ED8;color:#fff;text-decoration:none;
                                  padding:.85rem 2rem;border-radius:10px;font-weight:600;font-size:.95rem;
                                  box-shadow:0 2px 10px rgba(29,78,216,.3)'>
                          Restablecer contraseña
                        </a>
                      </div>
                      <p style='color:#6B7280;font-size:.82rem;text-align:center;margin:.75rem 0 0'>
                        Este enlace expira en <strong>1 hora</strong>.<br>
                        Si el botón no funciona, copia este enlace en tu navegador:<br>
                        <a href='$resetUrl' style='color:#1D4ED8;word-break:break-all'>$resetUrl</a>
                      </p>
                    </div>
                    <div style='background:#F3F4F6;padding:1rem 2rem;text-align:center'>
                      <p style='color:#9CA3AF;font-size:.78rem;margin:0'>© 2026 TicKeep · Tu tranquilidad, garantizada.</p>
                    </div>
                  </div>
                </body>
                </html>";

                $mail->AltBody = "Hola {$usuario['nombre']},\n\nPara restablecer tu contraseña visita:\n$resetUrl\n\nEste enlace expira en 1 hora.";
                $mail->send();
            } catch (Exception $e) {
                // Silenciar error SMTP al usuario, pero loguear internamente
                error_log("PHPMailer error: " . $mail->ErrorInfo);
            }
        }

        // Siempre el mismo mensaje (no revelar si el email existe)
        $mensaje     = "Si ese email está registrado, recibirás un mensaje con instrucciones en breve. Revisa también la carpeta de spam.";
        $tipo_alerta = "success";
    }
}

$pageTitle = "TicKeep | Recuperar contraseña";
$authTitle = "¿Olvidaste tu contraseña?";
require 'partials/header_auth.php';
?>

<?php if ($mensaje !== ""): ?>
  <div class="tk-alert tk-alert-<?= htmlspecialchars($tipo_alerta) ?>">
    <?php if ($tipo_alerta === 'success'): ?>
      <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <?php else: ?>
      <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?php endif; ?>
    <?= htmlspecialchars($mensaje) ?>
  </div>
<?php else: ?>

<p style="font-size:.88rem;color:#6B7280;text-align:center;margin-bottom:1.5rem;">
  Escribe tu email y te enviaremos un enlace para restablecer tu contraseña.
</p>

<form method="POST" novalidate>
  <div class="mb-f">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control form-control-lg" id="email" name="email"
           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
           placeholder="ejemplo@correo.com" required autocomplete="email" />
  </div>
  <button type="submit" class="btn-auth">Enviar enlace de recuperación</button>
</form>

<?php endif; ?>

<p class="tk-switch"><a href="login.php">← Volver al inicio de sesión</a></p>

<?php require 'partials/footer_auth.php'; ?>
