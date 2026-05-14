<?php
session_start();

// Si el usuario ya tiene sesión iniciada, lo mandamos directo a su panel
if (isset($_SESSION['id_usuario'])) {
    header('Location: mis_garantias.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicKeep - Nunca Más Pierdas una Garantía</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --tk-blue: #202bbf;
            --tk-blue-dark: #161e87;
            --tk-blue-soft: #eff2ff;
            --tk-dark: #0f172a;
            --tk-muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-light);
            color: var(--tk-dark);
            overflow-x: hidden;
        }

        /* --- Animaciones de entrada --- */
        @keyframes fadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- ANIMACIÓN DEL FONDO AZUL --- */
        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .anim-fade-up {
            animation: fadeSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .anim-delay-1 {
            animation-delay: 0.1s;
            opacity: 0;
        }

        .anim-delay-2 {
            animation-delay: 0.2s;
            opacity: 0;
        }

        /* --- Navbar Blanca Fija --- */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 18px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--tk-blue) !important;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            transition: transform 0.2s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        /* Botones Navbar */
        .btn-outline-custom {
            color: var(--tk-dark);
            border: 1.5px solid #e2e8f0;
            border-radius: 50px;
            padding: 8px 24px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-outline-custom:hover {
            border-color: var(--tk-blue);
            color: var(--tk-blue);
            background-color: var(--tk-blue-soft);
        }

        .btn-solid-custom {
            background-color: var(--tk-blue);
            color: white;
            border-radius: 50px;
            padding: 9px 28px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            transition: all 0.3s;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(32, 43, 191, 0.2);
        }

        .btn-solid-custom:hover {
            background-color: var(--tk-blue-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(32, 43, 191, 0.3);
        }

        /* --- Hero Section (Caja Grande Animada) --- */
        .hero {
            /* Degradado más grande que el contenedor para poder moverlo */
            background: linear-gradient(-45deg, #161e87, #202bbf, #2a38e8, #161e87);
            background-size: 300% 300%;
            /* Activamos el movimiento en bucle infinito (dura 10 segundos el ciclo) */
            animation: gradientMove 10s ease infinite;

            color: white;
            padding: 100px 20px;
            text-align: center;
        }

        .hero h1 {
            font-weight: 800;
            font-size: 3.5rem;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            font-weight: 400;
            opacity: 0.9;
            max-width: 650px;
            margin: 0 auto 40px auto;
            line-height: 1.6;
        }

        .btn-hero {
            background-color: #0a0f44;
            color: white;
            border-radius: 50px;
            padding: 14px 35px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-hero:hover {
            background-color: #050826;
            color: white;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* --- Features Section --- */
        .features-section {
            padding: 100px 20px 60px;
            text-align: center;
        }

        .features-section h2 {
            font-weight: 800;
            color: var(--tk-blue);
            margin-bottom: 15px;
            font-size: 2.2rem;
            letter-spacing: -0.5px;
        }

        .features-section .subtitle {
            color: var(--tk-muted);
            max-width: 550px;
            margin: 0 auto 60px auto;
            font-size: 1.1rem;
        }

        /* Tarjetas estilo SaaS */
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 45px 30px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(0, 0, 0, 0.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(32, 43, 191, 0.08);
            border-color: rgba(32, 43, 191, 0.1);
        }

        /* Contenedor del Icono */
        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: var(--tk-blue-soft);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
            color: var(--tk-blue);
            transition: transform 0.3s ease;
        }

        .feature-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(3deg);
        }

        .feature-card h4 {
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 15px;
            color: var(--tk-dark);
        }

        .feature-card p {
            color: var(--tk-muted);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 0;
        }

        /* --- About Us Section --- */
        .about-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            max-width: 900px;
            margin: 0 auto 100px auto;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .about-card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
        }

        .about-card h4 {
            font-weight: 800;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 1.4rem;
        }

        .about-card p {
            color: var(--tk-dark);
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 0;
        }

        /* --- Footer --- */
        footer {
            background-color: var(--tk-blue);
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            padding: 40px 20px;
        }

        footer p {
            margin: 0;
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.05rem;
            }

            .about-card {
                padding: 35px 20px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="index.php">TicKeep</a>
            <div class="d-flex gap-3 align-items-center">
                <a href="login.php" class="btn-outline-custom">Login</a>
                <a href="registro.php" class="btn-solid-custom">Registro</a>
            </div>
        </div>
    </nav>

    <section class="hero anim-fade-up">
        <div class="container">
            <h1>Nunca Más Pierdas una Garantía</h1>
            <p>Guarda tus tickets de compra en segundos. TicKeep organiza tus garantías de forma segura y 100% privada,
                directamente en tu dispositivo. Sin cuentas. Sin nubes. Sin preocupaciones</p>
            <a href="registro.php" class="btn-hero">
                Empieza gratis
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </section>

    <section class="features-section container anim-fade-up anim-delay-1">
        <h2>Tu tranquilidad, garantizada</h2>
        <p class="subtitle">Diseñado para ser poderoso y simple. Te damos el control total sobre tus compras de la forma
            más segura posible.</p>

        <div class="row g-4 mt-2 mb-5">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h4>Privacidad Total</h4>
                    <p>Todo se guarda exclusivamente en tu cuenta. No tenemos acceso ni compartimos tu información con
                        terceros.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <circle cx="12" cy="13" r="3" stroke-width="2" />
                        </svg>
                    </div>
                    <h4>Registro en Segundos</h4>
                    <p>Añade un producto, haz una foto al ticket y dinos cuándo termina la garantía para tenerlo siempre
                        a mano.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h4>Alertas Inteligentes</h4>
                    <p>Te avisaremos proactivamente antes de que una garantía importante expire para que estés
                        prevenido.</p>
                </div>
            </div>
        </div>

        <div class="about-card anim-fade-up anim-delay-2">
            <h4>
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="none" viewBox="0 0 24 24"
                    stroke="var(--tk-blue)" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                ¿Quiénes Somos?
            </h4>
            <p>
                Nos dedicamos a transformar frustraciones cotidianas en soluciones digitales elegantes. Nuestro proceso
                es simple: partimos de un problema real, diseñamos una experiencia de usuario intuitiva y construimos
                software seguro que prioriza la privacidad por diseño.
            </p>
        </div>
    </section>

    <footer>
        <p>© <?= date('Y') ?> TicKeep. Todos los derechos reservados.</p>
        <p style="margin-top: 5px; font-weight: 300; opacity: 0.8;">Tu tranquilidad, garantizada.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>