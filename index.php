<?php
session_start();
require 'config/bd.php';
require 'includes/preferencias_usuario.php';
require 'config/lang.php'; // Cargamos las traducciones

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

function rutaFotoPerfil($fotoPerfil)
{
    $default = 'assets/img/default-avatar.png';

    if (empty($fotoPerfil)) {
        return $default;
    }

    $fotoPerfil = trim($fotoPerfil);
    $posiblesRutas = [];

    if (str_contains($fotoPerfil, '/')) {
        $posiblesRutas[] = $fotoPerfil;
    } else {
        $posiblesRutas[] = 'assets/img/' . $fotoPerfil;
        $posiblesRutas[] = 'uploads/perfiles/' . $fotoPerfil;
    }

    foreach ($posiblesRutas as $ruta) {
        if (file_exists(__DIR__ . '/' . $ruta)) {
            return $ruta;
        }
    }

    return $default;
}

try {
    $queryUser = "SELECT u.nombre, c.foto_perfil FROM usuarios u 
                  LEFT JOIN opciones_configuracion c ON u.id_usuario = c.id_usuario 
                  WHERE u.id_usuario = :id";
    $stmtUser = $pdo->prepare($queryUser);
    $stmtUser->execute([':id' => $id_usuario]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $ordenSQL = ordenGarantiasSQL($preferencias);

    $queryGarantias = "SELECT * FROM garantias 
                   WHERE id_usuario = :id 
                   ORDER BY $ordenSQL";
    $stmtGarantias = $pdo->prepare($queryGarantias);
    $stmtGarantias->execute([':id' => $id_usuario]);
    $garantias = $stmtGarantias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$fotoPerfil = rutaFotoPerfil($userData['foto_perfil'] ?? null);

$garantiasCalendario = [];
foreach ($garantias as $g) {
    $estado = $g['estado'] ?? 'Vigente';
    $color = '#16a34a';
    $estadoTraducido = $estado;

    if ($estado === 'Vigente') {
        $color = '#16a34a';
        $estadoTraducido = $t['vigente'];
    }
    if ($estado === 'Expira pronto') {
        $color = '#d97706';
        $estadoTraducido = $t['expira_pronto'];
    }
    if ($estado === 'Caducada') {
        $color = '#dc2626';
        $estadoTraducido = $t['caducada'];
    }

    $garantiasCalendario[] = [
        'title'         => $g['nombre_producto'],
        'start'         => $g['fecha_vencimiento'],
        'color'         => $color,
        'url'           => 'detalle.php?id=' . $g['id_garantia'],
        'extendedProps' => ['tienda' => $g['tienda'], 'estado' => $estadoTraducido],
    ];
}
$garantiasJson = json_encode($garantiasCalendario, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="<?= $preferencias['idioma'] === 'Inglés' ? 'en' : 'es' ?>"
    data-theme="<?= htmlspecialchars($preferencias['tema']) ?>"
    data-animations="<?= (int)$preferencias['animaciones_ui'] ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['app_nombre'] ?> | <?= $t['mis_garantias'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/preferencias.css">
</head>

<body class="<?= !empty($preferencias['modo_compacto']) ? 'modo-compacto' : '' ?>">

    <header class="tk-header">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="tk-logo"><?= $t['app_nombre'] ?></a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white d-none d-sm-block fw-500"><?= htmlspecialchars($userData['nombre']); ?></span>
                <a href="configuracion.php">
                    <img src="<?= htmlspecialchars($fotoPerfil); ?>?v=<?= time(); ?>" class="avatar-img" alt="Perfil">
                </a>
                <a href="logout.php" class="tk-btn-logout" title="<?= $t['cerrar_sesion'] ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="d-none d-md-inline"><?= $t['salir'] ?></span>
                </a>
            </div>
        </div>
    </header>

    <main class="container my-4">

        <section class="title-section mb-4">
            <div>
                <h2 class="mb-0"><?= $t['mis_garantias'] ?></h2>
                <p class="text-muted small mb-0"><?= count($garantias) ?> <?= count($garantias) !== 1 ? $t['garantias_registradas'] : $t['garantia_registrada'] ?></p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <div class="dropdown">
                    <div class="dropdown">
                        <button class="tk-btn-export d-flex align-items-center gap-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <?= $t['exportar'] ?>
                        </button>

                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="exportar_garantias_pdf.php">
                                    <?= $t['exportar_pdf'] ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="exportar_garantias_excel.php">
                                    <?= $t['exportar_excel'] ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <a href="nueva-garantia.php" class="tk-btn-primary text-decoration-none d-flex align-items-center gap-1">
                    <?= $t['nueva_garantia'] ?>
                </a>
            </div>
        </section>

        <ul class="nav tk-view-tabs mb-4" id="viewTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="tk-view-tab active" id="list-tab" data-bs-toggle="tab" data-bs-target="#tab-lista" type="button" role="tab">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <?= $t['lista'] ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="tk-view-tab" id="cal-tab" data-bs-toggle="tab" data-bs-target="#tab-calendario" type="button" role="tab">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <?= $t['calendario'] ?>
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <div class="tab-pane fade show active" id="tab-lista" role="tabpanel">
                <section class="search-input-wrapper mb-3">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="M21 21l-4.35-4.35" />
                    </svg>
                    <input type="text" id="searchInput" class="search-input" placeholder="<?= $t['buscar'] ?>">
                </section>

                <section class="filter-pills mb-4">
                    <button class="filter-pill active" data-filter="Todo"><?= $t['filtro_todo'] ?></button>
                    <button class="filter-pill" data-filter="Vigente"><?= $t['filtro_vigente'] ?></button>
                    <button class="filter-pill" data-filter="Expira pronto"><?= $t['filtro_expira'] ?></button>
                    <button class="filter-pill" data-filter="Caducada"><?= $t['filtro_caducada'] ?></button>
                </section>

                <section id="garantias-list">
                    <?php if (count($garantias) > 0): ?>
                        <?php foreach ($garantias as $g): ?>
                            <?php
                            $status = $g['estado'] ?? 'Vigente';
                            $badge  = 'badge-vigente';
                            $statusTraducido = $status;

                            if ($status === 'Vigente') {
                                $statusTraducido = $t['vigente'];
                            }
                            if ($status === 'Expira pronto') {
                                $badge = 'badge-expira-pronto';
                                $statusTraducido = $t['expira_pronto'];
                            }
                            if ($status === 'Caducada') {
                                $badge = 'badge-caducada';
                                $statusTraducido = $t['caducada'];
                            }

                            $imagenMostrar = 'assets/img/producto-default.png';

                            if (!empty($g['foto_producto']) && file_exists($g['foto_producto'])) {
                                $imagenMostrar = $g['foto_producto'];
                            } elseif (!empty($g['archivo_ticket']) && file_exists($g['archivo_ticket'])) {
                                $imagenMostrar = $g['archivo_ticket'];
                            }
                            ?>
                            <div class="tk-ticket-card"
                                data-estado="<?= htmlspecialchars($status) ?>"
                                data-nombre="<?= strtolower(htmlspecialchars($g['nombre_producto'])) ?>"
                                data-tienda="<?= strtolower(htmlspecialchars($g['tienda'] ?? '')) ?>">
                                <img src="<?= htmlspecialchars($imagenMostrar) ?>" class="ticket-thumb" alt="Producto">
                                <div class="ticket-info">
                                    <div class="ticket-header">
                                        <h3 class="ticket-title"><?= htmlspecialchars($g['nombre_producto']); ?></h3>
                                        <span class="status-badge <?= $badge ?>"><?= $statusTraducido ?></span>
                                    </div>
                                    <p class="mb-1 small"><?= $t['comprado_en'] ?> <span class="store-name fw-bold"><?= htmlspecialchars($g['tienda']); ?></span></p>
                                    <?php if (!empty($g['comentarios'])): ?>
                                        <p class="ticket-coments mb-2"><?= htmlspecialchars($g['comentarios']); ?></p>
                                    <?php endif; ?>
                                    <p class="ticket-expiry mb-0"><?= $t['vence_el'] ?> <b><?= fechaTickeep($g['fecha_vencimiento'], $preferencias); ?></b>
                                    </p>
                                    <?php if (!empty($preferencias['mostrar_dias_restantes'])): ?>
                                        <?php
                                        $diasRestantes = diasRestantesGarantia($g['fecha_vencimiento']);
                                        ?>

                                        <?php if ($diasRestantes > 0): ?>
                                            <p class="ticket-expiry mb-0 small">
                                                <?= $t['quedan'] ?> <b><?= $diasRestantes ?></b> <?= $t['dias'] ?>
                                            </p>
                                        <?php elseif ($diasRestantes === 0): ?>
                                            <p class="ticket-expiry mb-0 small text-warning">
                                                <?= $t['vence_hoy'] ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="ticket-expiry mb-0 small text-danger">
                                                <?= $t['caduco_hace'] ?> <b><?= abs($diasRestantes) ?></b> <?= $t['dias'] ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <a href="detalle.php?id=<?= $g['id_garantia']; ?>" class="tk-btn-details text-decoration-none"><?= $t['ver_detalles'] ?></a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state text-center py-5">
                            <div style="font-size:3rem">📋</div>
                            <p class="text-muted mt-3"><?= $t['sin_garantias'] ?></p>
                            <a href="nueva-garantia.php" class="tk-btn-primary text-decoration-none mt-2 d-inline-block"><?= $t['anadir_primera'] ?></a>
                        </div>
                    <?php endif; ?>
                    <div id="no-results" class="text-center py-5 d-none">
                        <p class="text-muted"><?= $t['sin_resultados'] ?></p>
                    </div>
                </section>
            </div>

            <div class="tab-pane fade" id="tab-calendario" role="tabpanel">
                <div class="calendar-legend mb-3 d-flex gap-3 flex-wrap">
                    <span class="legend-item"><span class="legend-dot" style="background:#16a34a"></span><?= $t['vigente'] ?></span>
                    <span class="legend-item"><span class="legend-dot" style="background:#d97706"></span><?= $t['expira_pronto'] ?></span>
                    <span class="legend-item"><span class="legend-dot" style="background:#dc2626"></span><?= $t['caducada'] ?></span>
                </div>
                <div id="tk-calendar"></div>
            </div>
        </div>
    </main>

    <footer class="main-footer">
        <p class="mb-1"><?= $t['footer'] ?></p>
        <p class="mb-0 x-small fw-light"><?= $t['footer_sub'] ?></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales-all.global.min.js"></script> 
    <script>
        const searchInput = document.getElementById('searchInput');
        const filterBtns = document.querySelectorAll('.filter-pill');
        const cards = document.querySelectorAll('.tk-ticket-card');
        const noResults = document.getElementById('no-results');
        let activeFilter = 'Todo';

        function applyFilters() {
            const q = searchInput.value.toLowerCase().trim();
            let visible = 0;
            cards.forEach(card => {
                const matchQ = !q || card.dataset.nombre.includes(q) || card.dataset.tienda.includes(q);
                const matchF = activeFilter === 'Todo' || card.dataset.estado === activeFilter;
                card.style.display = (matchQ && matchF) ? '' : 'none';
                if (matchQ && matchF) visible++;
            });
            noResults.classList.toggle('d-none', visible > 0);
        }

        searchInput.addEventListener('input', applyFilters);
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                activeFilter = btn.dataset.filter;
                applyFilters();
            });
        });

        document.getElementById('cal-tab').addEventListener('shown.bs.tab', () => {
            if (window._calInit) return;
            window._calInit = true;
            const cal = new FullCalendar.Calendar(document.getElementById('tk-calendar'), {
                initialView: 'dayGridMonth',
                locale: '<?= $idiomaActivo ?>',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listMonth'
                },
                events: <?= $garantiasJson ?>,
                eventClick(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) window.location.href = info.event.url;
                },
                eventDidMount(info) {
                    info.el.title = info.event.title + ' — ' + (info.event.extendedProps.tienda || '') + ' (' + (info.event.extendedProps.estado || '') + ')';
                }
            });
            cal.render();
        });
    </script>
</body>

</html>