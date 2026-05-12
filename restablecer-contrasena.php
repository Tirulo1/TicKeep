<?php
require 'config/bd.php';

$token   = trim($_GET['token'] ?? '');
$mensaje = "";
$tipo_alerta = "";
$tokenValido = false;
$emailUsuario = "";

// Validar token
if ($token !== '') {
    $stmt = $pdo->prepare("SELECT email, expira_en FROM password_resets WHERE token = :token");
    $stmt->execute([':token' => $token]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila && strtotime($fila['expira_en']) > time()) {
        $tokenValido  = true;
        $emailUsuario = $fila['email'];
    } else {
        $mensaje     = "El enlace es inválido o ha expirado. Solicita uno nuevo.";
        $tipo_alerta = "danger";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $tokenValido) {
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $mensaje     = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_alerta = "danger";
    } elseif ($password !== $confirm_password) {
        $mensaje     = "Las contraseñas no coinciden.";
        $tipo_alerta = "danger";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE usuarios SET contrasena = :pass WHERE email = :email")
            ->execute([':pass' => $hash, ':email' => $emailUsuario]);
        $pdo->prepare("DELETE FROM password_resets WHERE email = :email")
            ->execute([':email' => $emailUsuario]);

        header("Location: login.php?reset=1");
        exit;
    }
}

$pageTitle = "TicKeep | Nueva contraseña";
$authTitle = "Nueva contraseña";
require 'partials/header_auth.php';
?>

<?php if (!$tokenValido): ?>
  <div class="tk-alert tk-alert-danger">
    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($mensaje) ?>
  </div>
  <p class="tk-switch"><a href="recuperar-contrasena.php">Solicitar nuevo enlace</a></p>

<?php else: ?>

  <?php if ($mensaje !== ""): ?>
    <div class="tk-alert tk-alert-<?= htmlspecialchars($tipo_alerta) ?>">
      <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($mensaje) ?>
    </div>
  <?php endif; ?>

  <p style="font-size:.88rem;color:#6B7280;text-align:center;margin-bottom:1.5rem;">
    Elige una contraseña nueva segura para tu cuenta.
  </p>

  <form method="POST" novalidate>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />

    <div class="mb-f">
      <label for="password" class="form-label">Nueva contraseña</label>
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
               placeholder="Repite la contraseña" required autocomplete="new-password" />
        <button type="button" class="toggle-pw" onclick="togglePw('confirm_password',this)" tabindex="-1">
          <svg width="19" height="19" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </button>
      </div>
    </div>

    <button type="submit" class="btn-auth">Guardar nueva contraseña</button>
  </form>

<?php endif; ?>

<p class="tk-switch"><a href="login.php">← Volver al inicio de sesión</a></p>

<script>
function togglePw(id, btn) {
  const f = document.getElementById(id);
  f.type = f.type === 'password' ? 'text' : 'password';
  btn.style.opacity = f.type === 'text' ? '0.5' : '1';
}
</script>

<?php require 'partials/footer_auth.php'; ?>
