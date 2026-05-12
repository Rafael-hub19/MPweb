<?php
/* index.php - Landing Page de MotoStore
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
$loggedIn = isset($_SESSION['usuario']);

include 'conectbd.php';

/* Traer los primeros 4 productos para la seccion de destacados */
$sqlFeat = "SELECT `idP`, `nombreP`, `marcaP`, `existenciaP`, `precioP`
            FROM `productos`
            WHERE `existenciaP` > 0
            ORDER BY `idP` ASC
            LIMIT 4";
$resFeat = mysqli_query($conexion, $sqlFeat);

/* Contar totales para las estadisticas */
$sqlTotProd = "SELECT COUNT(*) AS total FROM `productos`";
$resTotProd = mysqli_query($conexion, $sqlTotProd);
$totalProductos = ($resTotProd) ? mysqli_fetch_assoc($resTotProd)['total'] : 0;

$sqlTotUsers = "SELECT COUNT(*) AS total FROM `usuarios`";
$resTotUsers = mysqli_query($conexion, $sqlTotUsers);
$totalUsuarios = ($resTotUsers) ? mysqli_fetch_assoc($resTotUsers)['total'] : 0;

$sqlTotBrand = "SELECT COUNT(DISTINCT `marcaP`) AS total FROM `productos`";
$resTotBrand = mysqli_query($conexion, $sqlTotBrand);
$totalMarcas = ($resTotBrand) ? mysqli_fetch_assoc($resTotBrand)['total'] : 0;

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoStore - Motocicletas Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_index.css">
</head>
<body>

<!-- ========== NAV ========== -->
<nav>
    <a class="brand" href="index.php"><img src="img/Logo_motostore.png" alt="MotoStore" class="nav-logo"></a>
    <div class="nav-r">
        <?php if ($loggedIn) { ?>
            <span class="nav-u">Hola, <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span></span>
            <a href="galeria.php" class="btn-n btn-cart">&#128661; Catalogo</a>
            <a href="carrito.php" class="btn-n btn-ghost">&#128722; Carrito</a>
            <a href="logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesion?')" class="btn-n btn-out">Salir</a>
        <?php } else { ?>
            <a href="galeria.php" class="btn-n btn-cart">&#128661; Catalogo</a>
            <a href="login.php" class="btn-n btn-ghost">Iniciar Sesion</a>
            <a href="registro.php" class="btn-n btn-out">Registrarse</a>
        <?php } ?>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Menu">&#9776;</button>
</nav>

<!-- ========== MOBILE MENU ========== -->
<div class="mobile-menu" id="mobileMenu">
    <a href="galeria.php">&#128661; Catalogo</a>
    <?php if ($loggedIn) { ?>
        <a href="carrito.php">&#128722; Carrito</a>
        <a href="logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesion?')">Salir</a>
    <?php } else { ?>
        <a href="login.php">Iniciar Sesion</a>
        <a href="registro.php">Registrarse</a>
    <?php } ?>
</div>

<a href="../web2.html" class="btn-back-index">&larr; Regresar a WEB 2</a>

<!-- ========== HERO ========== -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-lbl">Catalogo 2026</div>
        <h1>Encuentra tu<br><span>Moto Ideal</span></h1>
        <p>Explora nuestra seleccion de motocicletas premium. Precios inmejorables, stock actualizado en tiempo real.</p>
        <div class="hero-btns">
            <a href="galeria.php" class="btn-hero-pri">&#128661; Ver Catalogo</a>
            <?php if ($loggedIn) { ?>
                <a href="carrito.php" class="btn-hero-sec">&#128722; Mi Carrito</a>
            <?php } else { ?>
                <a href="login.php" class="btn-hero-sec">Iniciar Sesion</a>
            <?php } ?>
        </div>
    </div>
    <div class="hero-deco">&#127949;</div>
</section>

<!-- ========== STATS ========== -->
<section class="stats">
    <div class="stats-inner">
        <div class="stat">
            <div class="stat-num"><?php echo $totalProductos; ?>+</div>
            <div class="stat-lbl">Modelos</div>
        </div>
        <div class="stat">
            <div class="stat-num"><?php echo $totalMarcas; ?></div>
            <div class="stat-lbl">Marcas</div>
        </div>
        <div class="stat">
            <div class="stat-num"><?php echo $totalUsuarios; ?>+</div>
            <div class="stat-lbl">Clientes</div>
        </div>
        <div class="stat">
            <div class="stat-num">MXN</div>
            <div class="stat-lbl">Precios en Pesos</div>
        </div>
    </div>
</section>

<!-- ========== FEATURED PRODUCTS ========== -->
<section class="featured">
    <div class="section-head">
        <div class="section-lbl">Lo mas destacado</div>
        <h2>Motos en<br>Tendencia</h2>
        <p>Una seleccion de nuestros modelos mas populares del catalogo.</p>
    </div>
    <div class="feat-grid">
    <?php
    if ($resFeat && mysqli_num_rows($resFeat) > 0) {
        mysqli_data_seek($resFeat, 0);
        while ($moto = mysqli_fetch_assoc($resFeat)) {
            echo '<div class="feat-card">';
            echo '<div class="feat-img-w">';
            echo '<img src="imagen.php?id=' . $moto['idP'] . '" alt="' . htmlspecialchars($moto['nombreP']) . '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">';
            echo '<div class="feat-ph">&#128661;</div>';
            echo '</div>';
            echo '<div class="feat-body">';
            echo '<div class="feat-brand">' . htmlspecialchars($moto['marcaP']) . '</div>';
            echo '<div class="feat-name">' . htmlspecialchars($moto['nombreP']) . '</div>';
            echo '<div class="feat-footer">';
            echo '<div class="feat-price">$' . number_format($moto['precioP'], 0, '.', ',') . ' <span>MXN</span></div>';
            if ($loggedIn) {
                echo '<a href="galeria.php" class="feat-btn">&#128722; Comprar</a>';
            } else {
                echo '<a href="galeria.php" class="feat-btn">&#128661; Ver en Catalogo</a>';
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="no-feat">No hay productos disponibles en este momento.</p>';
    }
    ?>
    </div>
    <div class="feat-cta">
        <a href="galeria.php" class="btn-hero-pri">Ver Catalogo Completo &rarr;</a>
    </div>
</section>

<!-- ========== FEATURES / BENEFICIOS ========== -->
<section class="features">
    <div class="section-head">
        <div class="section-lbl">Por que elegirnos</div>
        <h2>Tu tienda de<br>confianza</h2>
    </div>
    <div class="feat-boxes">
        <div class="feat-box">
            <div class="feat-icon">&#9889;</div>
            <h3>Compra Rapida</h3>
            <p>Agrega al carrito y confirma tu pedido en segundos desde nuestro catalogo en linea.</p>
        </div>
        <div class="feat-box">
            <div class="feat-icon">&#128230;</div>
            <h3>Stock en Tiempo Real</h3>
            <p>Nuestro inventario se actualiza automaticamente al confirmar cada compra.</p>
        </div>
        <div class="feat-box">
            <div class="feat-icon">&#128196;</div>
            <h3>Carrito JSON</h3>
            <p>Tu carrito se almacena en formato JSON estandar, garantizando compatibilidad y persistencia.</p>
        </div>
        <div class="feat-box">
            <div class="feat-icon">&#128272;</div>
            <h3>Cuenta Segura</h3>
            <p>Registro rapido y acceso protegido para que solo tu gestiones tu pedido.</p>
        </div>
    </div>
</section>

<!-- ========== CTA BANNER ========== -->
<section class="cta-banner">
    <div class="cta-content">
        <h2>Listo para encontrar<br>tu proxima moto?</h2>
        <p>Explora el catalogo completo con precios y stock actualizados. Inicia sesion para agregar al carrito y realizar tu compra.</p>
        <a href="galeria.php" class="btn-hero-pri">&#128661; Ir al Catalogo</a>
        <?php if (!$loggedIn) { ?>
            <a href="registro.php" class="btn-hero-sec">Crear Cuenta Gratis</a>
        <?php } ?>
    </div>
</section>

<!-- ========== FOOTER ========== -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">MotoStore</div>
        <div class="footer-links">
            <a href="index.php">Inicio</a>
            <a href="galeria.php">Catalogo</a>
            <?php if ($loggedIn) { ?>
                <a href="carrito.php">Carrito</a>
                <a href="logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesion?')">Salir</a>
            <?php } else { ?>
                <a href="login.php">Iniciar Sesion</a>
                <a href="registro.php">Registro</a>
            <?php } ?>
            <a href="terminos.html">T&eacute;rminos y Condiciones</a>
            <a href="../web2.html">WEB 2</a>
        </div>
        <div class="footer-copy">
            &copy; 2026 MotoStore &mdash; Rafael Avila Sanchez &middot; CETI 8F &middot; 22300193<br>
            Programacion Web 2 &mdash; Mtra. Patricia Torres
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const toggle = document.getElementById('navToggle');
const menu   = document.getElementById('mobileMenu');
toggle.addEventListener('click', () => menu.classList.toggle('open'));
</script>
</body>
</html>
