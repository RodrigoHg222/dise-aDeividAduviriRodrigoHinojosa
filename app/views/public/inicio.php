<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>DiseñaTuPrenda | Diseña prendas a tu estilo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="public/assets/css/portal.css?v=4">
</head>

<body class="landing-body">

    <header class="landing-header">

        <a class="landing-brand" href="<?= h(url('inicio')) ?>">
            <img
              class="landing-brand-icon"
              src="public/assets/img/logo-principal.png"
              alt="Logo DiseñaTuPrenda"
            >

            <span>
                <b>Diseña<span>TuPrenda</span></b>
                <small>Crea tu estilo, hazlo realidad</small>
            </span>
        </a>
        <nav class="landing-nav" aria-label="Navegación principal">
            <a href="#inicio">Inicio</a>
            <a href="#como-funciona">Cómo funciona</a>
            <a href="#espacios">Plataforma</a>
        </nav>
        <div class="landing-actions">
            <a class="landing-login" href="<?= h(url('login')) ?>">
                Iniciar sesión
            </a>
            <a class="landing-register" href="<?= h(url('registro')) ?>">
                Crear cuenta
            </a>
        </div>
    </header>

    <main>
        <section class="landing-hero" id="inicio">
            <div class="landing-hero-copy">
                <span class="eyebrow">
                    <i></i>
                    DISEÑO Y PERSONALIZACIÓN DE PRENDAS
                </span>
                <h1>
                    Tu estilo,<br>
                    <em>tus propias ideas.</em>
                </h1>
                <p>
                    Diseña vestidos, faldas, blusas y otras prendas femeninas.
                    Elige un modelo, combina colores y detalles, y comparte tu
                    propuesta con emprendimientos de confección de Sacaba.
                </p>
                <div class="landing-hero-buttons">
                    <a class="landing-register large" href="<?= h(url('registro')) ?>">
                        Comenzar a diseñar
                        <span>→</span>
                    </a>
                    <a class="landing-outline" href="<?= h(url('login')) ?>">
                        Ya tengo una cuenta
                    </a>
                </div>
                <div class="landing-proof">
                    <span class="proof-icon">✦</span>
                    <span>
                        <b>Diseña a tu manera</b>
                        <small>Personalización sencilla y creativa</small>
                    </span>
                    <span class="proof-divider"></span>
                    <span>
                        <b>Hecho para Sacaba</b>
                        <small>Conecta con la confección local</small>
                    </span>
                </div>
            </div>
            <div class="landing-visual">
                <div class="landing-image-main">
                    <img
                        src="public/assets/img/vestido-hermoso.png"
                        alt="Modelo de vestido para personalizar"
                    >
                    <span class="image-label">
                        TU DISEÑO COMIENZA AQUÍ
                    </span>
                </div>
                <div class="landing-image-small">
                    <img
                        src="public/assets/img/vestido-top.png"
                        alt="Modelo de falda para personalizar"
                    >
                </div>
                <div class="landing-floating-note">
                    <span>♡</span>
                    <div>
                        <b>Hecho a tu estilo</b>
                        <small>Colores · telas · detalles</small>
                    </div>
                </div>
                <div class="landing-orbit"></div>
            </div>
        </section>
        <section class="landing-features" id="como-funciona">
            <div class="landing-section-title">
                <span class="eyebrow">
                    UN PROCESO SIMPLE
                </span>
                <h2>De una idea a un diseño propio</h2>
                <p>
                    Todo lo necesario para preparar tu propuesta de prenda
                    en un solo lugar.
                </p>
            </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <span class="feature-number">01</span>
                    <div class="feature-icon">♧</div>
                    <h3>Elige tu modelo</h3>
                    <p>
                        Explora el catálogo de vestidos, faldas, tops y otras
                        prendas disponibles.
                    </p>
                </article>
                <article class="feature-card">
                    <span class="feature-number">02</span>
                    <div class="feature-icon">✧</div>
                    <h3>Personaliza detalles</h3>
                    <p>
                        Selecciona colores, telas, escote, mangas, largo
                        y otros acabados.
                    </p>
                </article>
                <article class="feature-card">
                    <span class="feature-number">03</span>
                    <div class="feature-icon">♡</div>
                    <h3>Guarda y comparte</h3>
                    <p>
                        Conserva tus diseños y envía una solicitud para
                        que sea revisada.
                    </p>
                </article>
            </div>
        </section>
        <section class="landing-spaces" id="espacios">
            <div>
                <span class="eyebrow">
                    UNA PLATAFORMA, DOS ESPACIOS
                </span>
                <h2>Diseño y gestión en un mismo sistema</h2>
                <p>
                    El espacio de cliente permite crear propuestas y consultar
                    su seguimiento. El administrador gestiona el catálogo,
                    los usuarios y las solicitudes desde un panel independiente.
                </p>
                <a class="landing-register" href="<?= h(url('login')) ?>">
                    Ingresar a la plataforma
                    <span>→</span>
                </a>
            </div>
            <div class="space-cards">
                <a class="space-card customer" href="<?= h(url('registro')) ?>">
                    <span>♡</span>
                    <div>
                        <small>ESPACIO DE CLIENTE</small>
                        <h3>Diseña tus prendas</h3>
                        <p>
                            Crear cuenta para personalizar modelos,
                            guardar ideas y revisar solicitudes.
                        </p>
                    </div>
                </a>
                <a class="space-card admin" href="<?= h(url('login')) ?>">
                    <span>⚙</span>
                    <div>
                        <small>ESPACIO ADMINISTRADOR</small>
                        <h3>Administrar el sistema</h3>
                        <p>
                            Acceso para cuentas autorizadas que gestionan
                            el catálogo, usuarios y reportes.
                        </p>
                    </div>
                </a>
            </div>
        </section>
    </main>
    <footer class="landing-footer">
        <a class="landing-brand" href="<?= h(url('inicio')) ?>">
            <span class="landing-brand-icon">♧</span>
            <span>
                <b>Diseña<span>TuPrenda</span></b>
                <small>Crea tu estilo, hazlo realidad</small>
            </span>
        </a>
        <span>
            Proyecto de desarrollo web · Sacaba, Cochabamba
        </span>
        <span>
            © <?= date('Y') ?> DiseñaTuPrenda
        </span>
    </footer>
</body>
</html>