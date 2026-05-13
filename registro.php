<?php
require 'config/bd.php';

$mensaje     = "";
$tipo_alerta = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre           = trim($_POST['nombre'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($nombre === '' || $email === '' || $password === '' || $confirm_password === '') {
        $mensaje = "Por favor, rellena todos los campos.";
        $tipo_alerta = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El email no tiene un formato válido.";
        $tipo_alerta = "danger";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_alerta = "danger";
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_alerta = "danger";
    } else {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $mensaje = "Ese correo ya está registrado.";
            $tipo_alerta = "warning";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, contrasena, email_verificado) VALUES (:nombre, :email, :pass, 1)");
                $stmt->execute([':nombre' => $nombre, ':email' => $email, ':pass' => $password_hash]);
                $id_nuevo = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO opciones_configuracion (id_usuario) VALUES (:id)")->execute([':id' => $id_nuevo]);
                $pdo->commit();
                $mensaje = "¡Cuenta creada con éxito! Redirigiendo al inicio de sesión...";
                $tipo_alerta = "success";
                header("refresh:2;url=login.php");
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje = "Error en el sistema. Inténtalo de nuevo.";
                $tipo_alerta = "danger";
            }
        }
    }
}

$pageTitle = "TicKeep | Crear cuenta";
$authTitle = "Crear cuenta";
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
<?php endif; ?>

<form method="POST" novalidate>
  <div class="mb-f">
    <label for="nombre" class="form-label">Nombre</label>
    <input type="text" class="form-control form-control-lg" id="nombre" name="nombre"
           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
           placeholder="Tu nombre completo" required autocomplete="name" />
  </div>

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
             placeholder="Mínimo 6 caracteres" required autocomplete="new-password" />
      <button type="button" class="toggle-pw" onclick="togglePw('password',this)" tabindex="-1">
        <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
      </button>
    </div>
  </div>

  <div class="mb-f">
    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
    <div class="input-pw">
      <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password"
             placeholder="Repite tu contraseña" required autocomplete="new-password" />
      <button type="button" class="toggle-pw" onclick="togglePw('confirm_password',this)" tabindex="-1">
        <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
      </button>
    </div>
  </div>

  <button type="submit" class="btn-auth">Registrarse</button>
</form>

<p class="tk-switch">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>

<script>
function togglePw(id, btn) {
  const f = document.getElementById(id);
  f.type = f.type === 'password' ? 'text' : 'password';
  btn.style.opacity = f.type === 'text' ? '0.5' : '1';
}
</script>

<?php require 'partials/footer_auth.php'; ?>
