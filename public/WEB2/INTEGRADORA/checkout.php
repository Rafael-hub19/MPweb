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
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f0f0f; color: #e8e8e8; font-family: 'Outfit', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        nav { display:flex; align-items:center; justify-content:space-between; padding:16px 32px; background:#151515; border-bottom:1px solid #222; position:sticky; top:0; z-index:100; }
        .brand { font-family:'Bebas Neue',sans-serif; font-size:28px; color:#ff6b35; text-decoration:none; letter-spacing:2px; }
        .nav-r { display:flex; gap:12px; align-items:center; }
        .btn-n { padding:8px 18px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; color:#e8e8e8; background:#222; transition:background .2s; }
        .btn-n:hover { background:#333; }
        .page { flex:1; max-width:1100px; width:100%; margin:0 auto; padding:48px 24px; }
        .page-lbl { font-size:13px; letter-spacing:3px; color:#ff6b35; text-transform:uppercase; margin-bottom:8px; }
        h1 { font-family:'Bebas Neue',sans-serif; font-size:48px; line-height:1; margin-bottom:32px; }
        /* Bootstrap handles the two-column responsive layout */
        .panel { background:#151515; border:1px solid #222; border-radius:16px; padding:28px; }
        .panel h2 { font-family:'Bebas Neue',sans-serif; font-size:28px; color:#ff6b35; margin-bottom:20px; }
        .form-group { margin-bottom:16px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:#999; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px; }
        .form-group input, .form-group textarea { width:100%; background:#1e1e1e; border:1px solid #333; border-radius:8px; padding:10px 14px; color:#e8e8e8; font-family:'Outfit',sans-serif; font-size:15px; }
        .form-group input:focus, .form-group textarea:focus { outline:none; border-color:#ff6b35; }
        .form-group textarea { height:80px; resize:vertical; }
        .order-item { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #222; font-size:15px; }
        .order-item:last-child { border-bottom:none; }
        .order-total { display:flex; justify-content:space-between; margin-top:20px; padding-top:16px; border-top:2px solid #ff6b35; font-family:'Bebas Neue',sans-serif; font-size:24px; }
        .order-total span:last-child { color:#ff6b35; }
        #paypal-button-container { margin-top:24px; }
        .alert { background:#2a1a1a; border:1px solid #c0392b; color:#e74c3c; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; }
        .pickup-note { background:#1a1f2a; border:1px solid #2a3f5a; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:13px; color:#8ab4d4; line-height:1.6; }
        .pickup-note strong { color:#ff6b35; }
        footer { background:#0d1117; border-top:1px solid rgba(255,255,255,.07); padding:40px 32px; }
        .footer-inner { max-width:1100px; margin:0 auto; display:flex; flex-direction:column; align-items:center; gap:20px; text-align:center; }
        .footer-brand { font-family:'Bebas Neue',sans-serif; font-size:30px; letter-spacing:3px; color:#f97316; }
        .footer-links { display:flex; gap:20px; flex-wrap:wrap; justify-content:center; }
        .footer-links a { color:#64748b; text-decoration:none; font-size:14px; transition:color .2s; }
        .footer-links a:hover { color:#f97316; }
        .footer-copy { color:#334155; font-size:12px; line-height:1.7; }
    </style>
</head>
<body>
<nav>
    <a class="brand" href="index.php"><img src="img/Logo_motostore.png" alt="MotoStore" style="height:42px;vertical-align:middle;filter:drop-shadow(0 0 6px rgba(255,107,53,0.35));"></a>
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
