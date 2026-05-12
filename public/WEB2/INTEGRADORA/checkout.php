<?php
/* checkout.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include 'conectbd.php';
include 'paypal_config.php';

$jsonFile = __DIR__ . '/carrito.json';

if (!file_exists($jsonFile)) {
    header('Location: carrito.php');
    exit();
}

$carrito = json_decode(file_get_contents($jsonFile), true);
if (!is_array($carrito) || count($carrito) == 0) {
    header('Location: carrito.php');
    exit();
}

$items = array();
$total = 0.0;
foreach ($carrito as $it) {
    $items[] = array(
        'idP'      => (int)$it['idP'],
        'nombre'   => (string)$it['nombre'],
        'precio'   => (float)$it['precio'],
        'cantidad' => (int)$it['cantidad'],
    );
    $total += (float)$it['precio'] * (int)$it['cantidad'];
}

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_checkout.css">
</head>
<body>
<nav>
    <a class="brand" href="index.php"><img src="img/Logo_motostore.png" alt="MotoStore" class="nav-logo"></a>
    <div class="nav-r">
        <a href="galeria.php" class="btn-n">&#128661; Catalogo</a>
        <a href="carrito.php" class="btn-n">&#8592; Carrito</a>
        <a href="logout.php" onclick="return confirm('&#191;Seguro?')" class="btn-n">Salir</a>
    </div>
</nav>
<div class="page">
    <div class="page-lbl">Paso final</div>
    <h1>Datos del<br>Pedido</h1>
    <div id="alert-box"></div>
    <div class="row g-4">
        <!-- LEFT: Form -->
        <div class="col-12 col-lg-6">
        <div class="panel">
            <h2>Informacion de Contacto</h2>
            <div class="pickup-note">
                <strong>&#128661; Recogida en Agencia</strong><br>
                Tu moto estara lista para recoger en nuestra agencia. Te contactaremos al telefono proporcionado para confirmar la fecha y hora de entrega.
            </div>
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" id="nombre" placeholder="Tu nombre">
            </div>
            <div class="form-group">
                <label>Apellido *</label>
                <input type="text" id="apellido" placeholder="Tu apellido">
            </div>
            <div class="form-group">
                <label>Telefono *</label>
                <input type="tel" id="telefono" placeholder="Ej: 3312345678">
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" id="email" placeholder="tu@email.com">
            </div>
            <div class="form-group">
                <label>Notas (opcional)</label>
                <textarea id="notas" placeholder="Alguna nota para la agencia..."></textarea>
            </div>
        </div>
        </div>
        <!-- RIGHT: Order summary + PayPal -->
        <div class="col-12 col-lg-6">
        <div class="panel">
            <h2>Resumen del Pedido</h2>
            <?php foreach ($items as $item) { ?>
            <div class="order-item">
                <span><?php echo htmlspecialchars($item['nombre']); ?> x<?php echo $item['cantidad']; ?></span>
                <span>$<?php echo number_format($item['precio'] * $item['cantidad'], 0, '.', ','); ?></span>
            </div>
            <?php } ?>
            <div class="order-total">
                <span>Total</span>
                <span>$<?php echo number_format($total, 0, '.', ','); ?> MXN</span>
            </div>
            <div id="paypal-button-container"></div>
        </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://www.paypal.com/sdk/js?client-id=<?php echo PAYPAL_CLIENT_ID; ?>&currency=MXN"></script>
<script>
    function getFormData() {
        return {
            nombre:   document.getElementById('nombre').value.trim(),
            apellido: document.getElementById('apellido').value.trim(),
            telefono: document.getElementById('telefono').value.trim(),
            email:    document.getElementById('email').value.trim(),
            notas:    document.getElementById('notas').value.trim()
        };
    }
    function showAlert(msg) {
        document.getElementById('alert-box').innerHTML = '<div class="alert">' + msg + '</div>';
        window.scrollTo(0, 0);
    }
    paypal.Buttons({
        createOrder: function() {
            var d = getFormData();
            if (!d.nombre || !d.apellido || !d.telefono || !d.email) {
                showAlert('Por favor completa todos los campos obligatorios.');
                return Promise.reject(new Error('Campos incompletos'));
            }
            return fetch('paypal_create_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(d)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.error) { showAlert('Error: ' + data.error); throw new Error(data.error); }
                return data.id;
            });
        },
        onApprove: function(data) {
            return fetch('paypal_capture_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderID: data.orderID })
            })
            .then(function(res) { return res.json(); })
            .then(function(result) {
                if (result.success) {
                    window.location.href = 'confirmacion.php?orden=' + result.idOrden;
                } else {
                    showAlert('Error al procesar el pago: ' + (result.error || 'Error desconocido'));
                }
            });
        },
        onError: function(err) {
            showAlert('Error en PayPal. Intenta de nuevo.');
            console.error(err);
        }
    }).render('#paypal-button-container');
</script>

<!-- ========== FOOTER ========== -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">MotoStore</div>
        <div class="footer-links">
            <a href="index.php">Inicio</a>
            <a href="galeria.php">Cat&aacute;logo</a>
            <a href="carrito.php">Carrito</a>
            <a href="terminos.html">T&eacute;rminos y Condiciones</a>
            <a href="logout.php" onclick="return confirm('&iquest;Seguro que deseas cerrar sesion?')">Salir</a>
        </div>
        <div class="footer-copy">
            &copy; 2026 MotoStore &mdash; Rafael Avila Sanchez &middot; CETI 8F &middot; 22300193<br>
            Programacion Web 2 &mdash; Mtra. Patricia Torres
        </div>
    </div>
</footer>
</body>
</html>
