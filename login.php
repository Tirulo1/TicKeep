<?php
session_start();
require 'config/bd.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $mensaje = "Introduce tu email y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario, nombre, contrasena, email_verificado FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['contrasena'])) {
            if (!$usuario['email_verificado']) {
                $mensaje = "Debes verificar tu email antes de iniciar sesión. Revisa tu bandeja de entrada o <a href='reenviar-verificacion.php?email=" . urlencode($email) . "' style='color:#991B1B;font-weight:600;'>reenvía el email de verificación</a>.";
            } else {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'];
                header("Location: index.php");
                exit;
            }
        } else {
            $mensaje = "Email o contraseña incorrectos.";
        }
    }
}

$pageTitle = "TicKeep | Iniciar sesión";
$authTitle = "Iniciar sesión";
require 'partials/header_auth.php';
?>

<?php if (isset($_GET['logout'])): ?>
  <div class="tk-alert tk-alert-success">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Sesión cerrada correctamente. ¡Hasta pronto!
  </div>
<?php endif; ?>

<?php if (isset($_GET['reset'])): ?>
  <div class="tk-alert tk-alert-success">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    ¡Contraseña actualizada! Ya puedes iniciar sesión.
  </div>
<?php endif; ?>

<?php if ($mensaje !== ""): ?>
  <div class="tk-alert tk-alert-danger">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= $mensaje ?>
  </div>
<?php endif; ?>

<form method="POST" novalidate>
  <div class="mb-f">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control form-control-lg" id="email" name="email"
           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
           placeholder="ejemplo@correo.com" required autocomplete="email" />
  </div>

  <div class="mb-f">
    <label for="password" class="form-label">Contraseña</label>
    <div class="input-pw">
      <input type="password" class="form-control form-control-lg" id="password" name="password"
             placeholder="Tu contraseña" required autocomplete="current-password" />
      <button type="button" class="toggle-pw" onclick="togglePw('password',this)" tabindex="-1">
        <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
      </button>
    </div>
  </div>

  <div class="tk-forgot">
    <a href="recuperar-contrasena.php">¿Olvidaste tu contraseña?</a>
  </div>

  <button type="submit" class="btn-auth">Iniciar sesión</button>
</form>

<p class="tk-switch">¿No tienes cuenta? <a href="registro.php">Crear cuenta</a></p>

<script>
function togglePw(id, btn) {
  const f = document.getElementById(id);
  f.type = f.type === 'password' ? 'text' : 'password';
  btn.style.opacity = f.type === 'text' ? '0.5' : '1';
}
</script>

<?php require 'partials/footer_auth.php'; ?>