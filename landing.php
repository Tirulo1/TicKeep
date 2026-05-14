<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicKeep — Nunca Más Pierdas una Garantía</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue:       #1D4ED8;
            --blue-deep:  #1338A0;
            --blue-light: #EFF6FF;
            --blue-mid:   #3B6EF0;
            --ink:        #0D1117;
            --ink-soft:   #374151;
            --muted:      #6B7280;
            --border:     #E5E7EB;
            --surface:    #F8FAFF;
            --white:      #FFFFFF;
            --green:      #059669;
            --amber:      #D97706;
            --red:        #DC2626;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            color: var(--ink);
            background: var(--white);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ─── NAV ─── */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            padding: 0 2rem;
            height: 68px;
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(18px) saturate(180%);
            border-bottom: 1px solid rgba(29,78,216,0.08);
            transition: box-shadow 0.3s;
        }
        nav.scrolled { box-shadow: 0 4px 32px rgba(29,78,216,0.10); }

        .nav-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.45rem;
            color: var(--blue);
            letter-spacing: -0.5px;
            text-decoration: none;
        }
        .nav-logo span { color: var(--ink); }

        .nav-links { display: flex; align-items: center; gap: 0.5rem; }

        .btn-ghost {
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            border: 1.5px solid var(--border);
            background: transparent;
            color: var(--ink-soft);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .btn-ghost:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-light); }

        .btn-primary {
            padding: 0.55rem 1.4rem;
            border-radius: 50px;
            border: none;
            background: var(--blue);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(29,78,216,0.3);
        }
        .btn-primary:hover { background: var(--blue-deep); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(29,78,216,0.38); }

        /* ─── HERO ─── */
        .hero {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 120px 2rem 80px;
            position: relative;
            overflow: hidden;
        }

        /* gradient mesh background */
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 20% 30%, rgba(29,78,216,0.10) 0%, transparent 70%),
                radial-gradient(ellipse 50% 50% at 80% 70%, rgba(59,110,240,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 80% 40% at 50% 100%, rgba(29,78,216,0.06) 0%, transparent 70%);
        }

        /* subtle grid lines */
        .hero::after {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(29,78,216,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(29,78,216,0.04) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 30%, transparent 100%);
        }

        .hero-inner {
            position: relative; z-index: 1;
            max-width: 780px;
            text-align: center;
        }

        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.38rem 1rem;
            background: var(--blue-light);
            border: 1px solid rgba(29,78,216,0.2);
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--blue);
            margin-bottom: 2rem;
            animation: fadeUp 0.6s ease both;
        }
        .hero-badge-dot {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: var(--blue);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%,100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.75); }
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.6rem, 6vw, 4.2rem);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -1.5px;
            color: var(--ink);
            margin-bottom: 1.5rem;
            animation: fadeUp 0.7s 0.1s ease both;
        }
        h1 em {
            font-style: normal;
            color: var(--blue);
            position: relative;
        }
        h1 em::after {
            content: '';
            position: absolute; left: 0; bottom: -4px; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--blue), var(--blue-mid));
            border-radius: 2px;
        }

        .hero-sub {
            font-size: 1.15rem;
            color: var(--muted);
            font-weight: 300;
            line-height: 1.7;
            max-width: 560px;
            margin: 0 auto 2.5rem;
            animation: fadeUp 0.7s 0.2s ease both;
        }

        .hero-cta {
            display: flex; align-items: center; justify-content: center; gap: 1rem;
            flex-wrap: wrap;
            animation: fadeUp 0.7s 0.3s ease both;
        }

        .btn-hero {
            padding: 0.85rem 2.2rem;
            border-radius: 50px;
            border: none;
            background: var(--blue);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 6px 24px rgba(29,78,216,0.35);
            display: flex; align-items: center; gap: 0.5rem;
        }
        .btn-hero:hover { background: var(--blue-deep); transform: translateY(-2px); box-shadow: 0 10px 32px rgba(29,78,216,0.45); }
        .btn-hero svg { transition: transform 0.2s; }
        .btn-hero:hover svg { transform: translateX(3px); }

        .btn-hero-ghost {
            padding: 0.85rem 2rem;
            border-radius: 50px;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--ink-soft);
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-hero-ghost:hover { border-color: var(--blue); color: var(--blue); }

        .hero-trust {
            margin-top: 3rem;
            display: flex; align-items: center; justify-content: center; gap: 2rem;
            flex-wrap: wrap;
            animation: fadeUp 0.7s 0.4s ease both;
        }
        .trust-item {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.82rem;
            color: var(--muted);
        }
        .trust-item svg { color: var(--blue); flex-shrink: 0; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── SECTION COMMON ─── */
        section { padding: 100px 2rem; }

        .section-label {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 1rem;
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            font-weight: 700;
            letter-spacing: -0.8px;
            line-height: 1.15;
            color: var(--ink);
            margin-bottom: 1rem;
        }

        .section-sub {
            color: var(--muted);
            font-weight: 300;
            font-size: 1.05rem;
            line-height: 1.7;
            max-width: 520px;
        }

        .container { max-width: 1120px; margin: 0 auto; }
        .text-center { text-align: center; }
        .text-center .section-sub { margin: 0 auto; }

        /* ─── FEATURES ─── */
        .features { background: var(--surface); }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 4rem;
        }

        .feature-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            transition: transform 0.25s, box-shadow 0.25s, border-color 0.25s;
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--blue), var(--blue-mid));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s;
            border-radius: 3px 3px 0 0;
        }
        .feature-card:hover { transform: translateY(-6px); box-shadow: 0 20px 48px rgba(29,78,216,0.12); border-color: rgba(29,78,216,0.2); }
        .feature-card:hover::before { transform: scaleX(1); }

        .feature-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: var(--blue-light);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 1.5rem;
        }

        .feature-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.6rem;
        }

        .feature-desc {
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.65;
            font-weight: 300;
        }

        /* ─── HOW IT WORKS ─── */
        .how-it-works { background: var(--white); }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
            position: relative;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 28px; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), var(--border), transparent);
        }

        .step {
            text-align: center;
            position: relative;
        }

        .step-num {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--blue);
            color: #fff;
            font-family: 'Syne', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 8px 24px rgba(29,78,216,0.3);
            position: relative; z-index: 1;
        }

        .step-title {
            font-family: 'Syne', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            color: var(--ink);
            margin-bottom: 0.5rem;
        }
        .step-desc { color: var(--muted); font-size: 0.88rem; line-height: 1.6; font-weight: 300; }

        /* ─── PRIVACY SECTION ─── */
        .privacy { background: var(--ink); color: #fff; }
        .privacy .section-label { color: #93C5FD; }
        .privacy .section-title { color: #fff; }
        .privacy .section-sub { color: rgba(255,255,255,0.55); }

        .privacy-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-top: 1rem;
        }

        @media (max-width: 720px) {
            .privacy-grid { grid-template-columns: 1fr; gap: 2.5rem; }
        }

        .privacy-list { margin-top: 2.5rem; display: flex; flex-direction: column; gap: 1.25rem; }

        .privacy-item {
            display: flex; gap: 1rem;
            align-items: flex-start;
        }
        .privacy-icon {
            width: 40px; height: 40px; flex-shrink: 0;
            border-radius: 10px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            display: flex; align-items: center; justify-content: center;
        }
        .privacy-item-title {
            font-family: 'Syne', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            color: #fff;
            margin-bottom: 0.2rem;
        }
        .privacy-item-desc { font-size: 0.85rem; color: rgba(255,255,255,0.5); font-weight: 300; line-height: 1.5; }

        /* big decorative card on the right */
        .privacy-visual {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }
        .privacy-visual::before {
            content: '';
            position: absolute;
            top: -40%; right: -30%;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(29,78,216,0.25), transparent 70%);
        }
        .mock-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .mock-logo { font-family: 'Syne', sans-serif; font-weight: 800; font-size: 1.1rem; color: #60A5FA; }
        .mock-dots { display: flex; gap: 5px; }
        .mock-dot { width: 8px; height: 8px; border-radius: 50%; }

        .mock-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 1rem 1.2rem;
            margin-bottom: 0.75rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .mock-thumb {
            width: 42px; height: 42px; border-radius: 10px;
            background: rgba(255,255,255,0.08);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .mock-name { font-family: 'Syne', sans-serif; font-size: 0.85rem; font-weight: 600; color: #fff; }
        .mock-meta { font-size: 0.72rem; color: rgba(255,255,255,0.45); margin-top: 0.2rem; }
        .mock-badge {
            margin-left: auto;
            padding: 0.22rem 0.7rem;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        .badge-green { background: rgba(5,150,105,0.2); color: #34D399; }
        .badge-amber { background: rgba(217,119,6,0.2); color: #FCD34D; }
        .badge-red   { background: rgba(220,38,38,0.2); color: #F87171; }

        /* ─── CTA SECTION ─── */
        .cta-section {
            background: linear-gradient(135deg, var(--blue) 0%, var(--blue-deep) 100%);
            padding: 100px 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .cta-section::before {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }
        .cta-section .section-label { color: #93C5FD; }
        .cta-section .section-title { color: #fff; max-width: 520px; margin: 0 auto 1rem; }
        .cta-section .section-sub { color: rgba(255,255,255,0.6); margin: 0 auto 2.5rem; }

        .btn-cta {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.9rem 2.4rem;
            border-radius: 50px;
            border: none;
            background: #fff;
            color: var(--blue);
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        }
        .btn-cta:hover { transform: translateY(-2px); box-shadow: 0 14px 40px rgba(0,0,0,0.3); }
        .btn-cta svg { transition: transform 0.2s; }
        .btn-cta:hover svg { transform: translateX(3px); }

        /* ─── FOOTER ─── */
        footer {
            background: var(--ink);
            padding: 2.5rem 2rem;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .footer-logo {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--blue);
            margin-bottom: 0.75rem;
        }
        .footer-copy { color: rgba(255,255,255,0.35); font-size: 0.82rem; }
        .footer-tagline { color: rgba(255,255,255,0.2); font-size: 0.75rem; margin-top: 0.25rem; font-weight: 300; font-style: italic; }

        /* ─── OBSERVE ANIMATIONS ─── */
        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }
        .reveal.visible { opacity: 1; transform: none; }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 640px) {
            nav { padding: 0 1.25rem; }
            section { padding: 72px 1.25rem; }
            .steps::before { display: none; }
        }
    </style>
</head>
<body>

    <!-- NAV -->
    <nav id="nav">
        <a href="landing.php" class="nav-logo">Tic<span>Keep</span></a>
        <div class="nav-links">
            <a href="login.php" class="btn-ghost">Iniciar sesión</a>
            <a href="registro.php" class="btn-primary">Registrarse</a>
        </div>
    </nav>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-inner">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                100% privado · Sin nubes · Sin cuentas bancarias
            </div>

            <h1>Nunca Más Pierdas<br>una <em>Garantía</em></h1>

            <p class="hero-sub">
                Guarda tus tickets en segundos. TicKeep organiza tus garantías
                de forma segura y completamente privada, directamente en tu dispositivo.
            </p>

            <div class="hero-cta">
                <a href="registro.php" class="btn-hero">
                    Empieza gratis
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="login.php" class="btn-hero-ghost">Ya tengo cuenta</a>
            </div>

            <div class="hero-trust">
                <div class="trust-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Sin registro de datos
                </div>
                <div class="trust-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Almacenamiento local
                </div>
                <div class="trust-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Alertas automáticas
                </div>
            </div>
        </div>
    </div>

    <!-- FEATURES -->
    <section class="features">
        <div class="container">
            <div class="text-center">
                <span class="section-label reveal">Funcionalidades</span>
                <h2 class="section-title reveal reveal-delay-1">Tu tranquilidad,<br>garantizada</h2>
                <p class="section-sub reveal reveal-delay-2">Diseñado para ser poderoso y simple. Te damos el control total sobre tus compras de la forma más segura posible.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="feature-icon">🛡️</div>
                    <div class="feature-title">Privacidad Total</div>
                    <p class="feature-desc">Todo se guarda exclusivamente en tu dispositivo. No tenemos acceso a tu información, ni la necesitamos.</p>
                </div>
                <div class="feature-card reveal reveal-delay-1">
                    <div class="feature-icon">📸</div>
                    <div class="feature-title">Registro en Segundos</div>
                    <p class="feature-desc">Añade un producto, haz una foto al ticket y dinos cuándo termina la garantía. Así de fácil.</p>
                </div>
                <div class="feature-card reveal reveal-delay-2">
                    <div class="feature-icon">🔔</div>
                    <div class="feature-title">Alertas Inteligentes</div>
                    <p class="feature-desc">Te avisaremos antes de que una garantía importante expire, para que nunca te pille desprevenido.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon">📅</div>
                    <div class="feature-title">Vista Calendario</div>
                    <p class="feature-desc">Visualiza todas tus garantías en un calendario interactivo y ten siempre el control del tiempo.</p>
                </div>
                <div class="feature-card reveal reveal-delay-1">
                    <div class="feature-icon">📤</div>
                    <div class="feature-title">Exporta cuando quieras</div>
                    <p class="feature-desc">Descarga tus garantías en PDF o Excel con un solo clic. Tus datos, siempre a tu alcance.</p>
                </div>
                <div class="feature-card reveal reveal-delay-2">
                    <div class="feature-icon">🌐</div>
                    <div class="feature-title">Multiidioma</div>
                    <p class="feature-desc">Disponible en español e inglés. TicKeep se adapta a ti, no al revés.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="how-it-works">
        <div class="container">
            <div class="text-center">
                <span class="section-label reveal">Cómo funciona</span>
                <h2 class="section-title reveal reveal-delay-1">En tres pasos</h2>
                <p class="section-sub reveal reveal-delay-2">Sin complicaciones. Sin curvas de aprendizaje. Solo abre la app y empieza.</p>
            </div>
            <div class="steps">
                <div class="step reveal">
                    <div class="step-num">1</div>
                    <div class="step-title">Añade el producto</div>
                    <p class="step-desc">Nombre, tienda, foto del ticket o del producto. En menos de 30 segundos.</p>
                </div>
                <div class="step reveal reveal-delay-1">
                    <div class="step-num">2</div>
                    <div class="step-title">Indica la garantía</div>
                    <p class="step-desc">Dinos la fecha de vencimiento y TicKeep hará el resto del trabajo.</p>
                </div>
                <div class="step reveal reveal-delay-2">
                    <div class="step-num">3</div>
                    <div class="step-title">Olvídate del resto</div>
                    <p class="step-desc">Recibirás alertas a tiempo. Tus garantías siempre bajo control.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PRIVACY DARK -->
    <section class="privacy">
        <div class="container">
            <div class="privacy-grid">
                <div>
                    <span class="section-label reveal">Privacidad por diseño</span>
                    <h2 class="section-title reveal reveal-delay-1">Tus datos son<br>solo tuyos</h2>
                    <p class="section-sub reveal reveal-delay-2">No vendemos datos. No usamos la nube. No necesitas ninguna cuenta bancaria. Tu información vive en tu dispositivo y punto.</p>

                    <div class="privacy-list">
                        <div class="privacy-item reveal">
                            <div class="privacy-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#60A5FA" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="privacy-item-title">Sin acceso externo</div>
                                <p class="privacy-item-desc">Ningún servidor nuestro toca tus datos personales.</p>
                            </div>
                        </div>
                        <div class="privacy-item reveal reveal-delay-1">
                            <div class="privacy-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#60A5FA" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="privacy-item-title">Almacenamiento local</div>
                                <p class="privacy-item-desc">Toda la información se guarda directamente en tu dispositivo.</p>
                            </div>
                        </div>
                        <div class="privacy-item reveal reveal-delay-2">
                            <div class="privacy-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#60A5FA" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                            </div>
                            <div>
                                <div class="privacy-item-title">Sin anuncios, nunca</div>
                                <p class="privacy-item-desc">No mostramos publicidad ni compartimos tu perfil con terceros.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mock UI -->
                <div class="privacy-visual reveal reveal-delay-1">
                    <div class="mock-header">
                        <div class="mock-logo">TicKeep</div>
                        <div class="mock-dots">
                            <div class="mock-dot" style="background:#ef4444"></div>
                            <div class="mock-dot" style="background:#f59e0b"></div>
                            <div class="mock-dot" style="background:#10b981"></div>
                        </div>
                    </div>

                    <div style="font-size:0.7rem;color:rgba(255,255,255,0.3);margin-bottom:1rem;letter-spacing:0.05em;text-transform:uppercase;">Mis Garantías · 3 registradas</div>

                    <div class="mock-card">
                        <div class="mock-thumb">📱</div>
                        <div>
                            <div class="mock-name">iPhone 15 Pro</div>
                            <div class="mock-meta">Apple · Vence 14 mar 2026</div>
                        </div>
                        <span class="mock-badge badge-green">Vigente</span>
                    </div>
                    <div class="mock-card">
                        <div class="mock-thumb">🖥️</div>
                        <div>
                            <div class="mock-name">Monitor LG 27"</div>
                            <div class="mock-meta">MediaMarkt · Vence 3 jun 2025</div>
                        </div>
                        <span class="mock-badge badge-amber">Expira pronto</span>
                    </div>
                    <div class="mock-card">
                        <div class="mock-thumb">🎧</div>
                        <div>
                            <div class="mock-name">Sony WH-1000XM5</div>
                            <div class="mock-meta">Amazon · Venció 10 ene 2025</div>
                        </div>
                        <span class="mock-badge badge-red">Caducada</span>
                    </div>

                    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid rgba(255,255,255,0.06);display:flex;gap:1rem;">
                        <div style="flex:1;background:rgba(255,255,255,0.04);border-radius:10px;padding:0.75rem;text-align:center;">
                            <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:700;color:#34D399;">2</div>
                            <div style="font-size:0.68rem;color:rgba(255,255,255,0.35);margin-top:2px;">Vigentes</div>
                        </div>
                        <div style="flex:1;background:rgba(255,255,255,0.04);border-radius:10px;padding:0.75rem;text-align:center;">
                            <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:700;color:#FCD34D;">1</div>
                            <div style="font-size:0.68rem;color:rgba(255,255,255,0.35);margin-top:2px;">Por expirar</div>
                        </div>
                        <div style="flex:1;background:rgba(255,255,255,0.04);border-radius:10px;padding:0.75rem;text-align:center;">
                            <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:700;color:#F87171;">1</div>
                            <div style="font-size:0.68rem;color:rgba(255,255,255,0.35);margin-top:2px;">Caducadas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div style="position:relative;z-index:1">
            <span class="section-label reveal">Empieza hoy</span>
            <h2 class="section-title reveal reveal-delay-1">¿A qué esperas?</h2>
            <p class="section-sub reveal reveal-delay-2">Crea tu cuenta gratis en segundos y empieza a gestionar tus garantías con tranquilidad.</p>
            <a href="registro.php" class="btn-cta reveal reveal-delay-3">
                Crear cuenta gratis
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="footer-logo">TicKeep</div>
        <p class="footer-copy">© 2025 TicKeep. Todos los derechos reservados.</p>
        <p class="footer-tagline">Tu tranquilidad, garantizada.</p>
    </footer>

    <script>
        // Nav shadow on scroll
        const nav = document.getElementById('nav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 20);
        });

        // Reveal on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) { e.target.classList.add('visible'); }
            });
        }, { threshold: 0.12 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    </script>

</body>
</html>