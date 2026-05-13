<?php
require 'config/bd.php';

$token = trim($_GET['token'] ?? '');
$estado = 'invalido'; // 'ok', 'ya_verificado', 'invalido'

if ($token !== '') {
    $stmt = $pdo->prepare("SELECT id_usuario, email_verificado FROM usuarios WHERE token_verificacion = :token");
    $stmt->execute([':token' => $token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if ($usuario['email_verificado']) {
            $estado = 'ya_verificado';
        } else {
            $pdo->prepare("UPDATE usuarios SET email_verificado = 1, token_verificacion = NULL WHERE id_usuario = :id")
                ->execute([':id' => $usuario['id_usuario']]);
            $estado = 'ok';
        }
    }
}

$pageTitle = "TicKeep | Verificar email";
$authTitle = "Verificación de email";
require 'partials/header_auth.php';
?>

<?php if ($estado === 'ok'): ?>
  <div class="tk-alert tk-alert-success">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    ¡Email verificado correctamente! Ya puedes iniciar sesión.
  </div>
  <a href="login.php" class="btn-auth" style="margin-top:1rem;">Ir al inicio de sesión</a>

<?php elseif ($estado === 'ya_verificado'): ?>
  <div class="tk-alert tk-alert-info">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Este email ya estaba verificado. Puedes iniciar sesión.
  </div>
  <a href="login.php" class="btn-auth" style="margin-top:1rem;">Ir al inicio de sesión</a>

<?php else: ?>
  <div class="tk-alert tk-alert-danger">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    El enlace es inválido o ha expirado.
  </div>
  <p class="tk-switch"><a href="registro.php">Crear una cuenta nueva</a></p>
<?php endif; ?>

<?php require 'partials/footer_auth.php'; ?>