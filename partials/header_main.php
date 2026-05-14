<?php
// partials/header_main.php
// Requiere que $fotoPerfil esté definida en la página que lo incluye.
// Si no está, usa el avatar por defecto.
if (!isset($fotoPerfil)) {
    $fotoPerfil = 'assets/img/default-avatar.png';
}
if (!isset($userData['nombre'])) {
    $userData['nombre'] = $_SESSION['nombre'] ?? 'Usuario';
}
?>
<header class="tk-header" style="position:sticky;top:0;z-index:999;">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="tk-logo"><?= $t['app_nombre'] ?></a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white d-none d-sm-block fw-500">
                <?= htmlspecialchars($userData['nombre']) ?>
            </span>
            <a href="configuracion.php">
                <img src="<?= htmlspecialchars($fotoPerfil) ?>?v=<?= time() ?>"
                     class="avatar-img" alt="Perfil">
            </a>
            <a href="logout.php" class="tk-btn-logout" title="<?= $t['cerrar_sesion'] ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="d-none d-md-inline"><?= $t['salir'] ?></span>
            </a>
        </div>
    </div>
</header>