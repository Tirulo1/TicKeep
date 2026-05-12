<?php
// partials/auth_tabs.php
// $activeTab debe ser: 'login' o 'register'
?>
<nav class="tk-tabs mb-4" aria-label="Acceso">
  <a
    class="tk-tab <?= ($activeTab ?? '') === 'login' ? 'is-active' : '' ?>"
    href="login.php"
    aria-current="<?= ($activeTab ?? '') === 'login' ? 'page' : 'false' ?>"
  >
    Iniciar Sesión
  </a>

  <a
    class="tk-tab <?= ($activeTab ?? '') === 'register' ? 'is-active' : '' ?>"
    href="registro.php"
    aria-current="<?= ($activeTab ?? '') === 'register' ? 'page' : 'false' ?>"
  >
    Registro
  </a>
</nav>