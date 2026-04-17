<?php
$fechaHora = date("F_j_Y");
$ruta      = $fechaHora . "_pedido.json";
$folio     = strtoupper(substr(md5(uniqid()), 0, 8));
$fecha     = date("d/m/Y H:i:s");
$errores   = array();
$comprados = array();
$total     = 0;

if (!file_exists($ruta)) {
    header("Location: carrito.php");
    exit;
}

$archivo = file_get_contents($ruta);
$pedido  = json_decode($archivo, true);

if (empty($pedido)) {
    header("Location: carrito.php");
    exit;
}

// Conexión a la BD
$conexion = mysqli_connect("localhost", "mueblesweb", "429afaa4d", "mueblesweb")
    or die("Error de conexión");
mysqli_set_charset($conexion, "utf8");

// Descontar existencia de cada producto en la BD
foreach ($pedido as $item) {
    $nombre   = mysqli_real_escape_string($conexion, $item['nombre']);
    $cantidad = intval($item['cantidad']);

    // Verificar existencia actual
    $res = mysqli_query($conexion, "SELECT id_producto, existencia FROM `dulceria` WHERE `nombre`='$nombre' LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $prod = mysqli_fetch_assoc($res);
        if ($prod['existencia'] >= $cantidad) {
            // Descontar existencia
            mysqli_query($conexion,
                "UPDATE `dulceria` SET `existencia` = `existencia` - $cantidad WHERE `id_producto` = " . $prod['id_producto']
            );
            $comprados[] = $item;
            $total += $item['subTotal'];
        } else {
            $errores[] = "Stock insuficiente para <strong>" . htmlspecialchars($item['nombre']) . "</strong> (disponible: " . $prod['existencia'] . ")";
        }
    } else {
        $errores[] = "Producto no encontrado: <strong>" . htmlspecialchars($item['nombre']) . "</strong>";
    }
}

mysqli_close($conexion);

// Borrar el JSON del pedido al finalizar (solo si no hubo errores)
if (empty($errores)) {
    unlink($ruta);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago completado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        /* Ticket estilo papel */
        .ticket {
            max-width: 480px;
            background: #fff;
            border-radius: 4px 4px 0 0;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            position: relative;
        }
        /* Efecto borde recortado inferior del ticket */
        .ticket-edge {
            display: flex;
            overflow: hidden;
            height: 20px;
        }
        .ticket-edge span {
            flex: 1;
            background: #fff8f0;
            border-radius: 50%;
            height: 40px;
            margin-top: -20px;
            box-shadow: inset 0 -3px 6px rgba(0,0,0,0.08);
        }
        .ticket-footer {
            max-width: 480px;
            background: #fff;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .dashed-line {
            border-top: 2px dashed #ddd;
            margin: 12px 0;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#fff8f0;">

    <!-- Navbar -->
    <nav class="navbar navbar-dark" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <div class="container-fluid">
            <a href="index.html" class="btn btn-outline-light btn-sm">
                <i class="fas fa-home me-1"></i> Inicio
            </a>
            <span class="navbar-brand mx-auto fw-bold">
                <i class="fas fa-candy-cane me-2"></i>Dulcería "La Patita"
            </span>
            <span style="width:90px;"></span>
        </div>
    </nav>

    <main class="flex-grow-1 d-flex flex-column align-items-center py-4 px-3">

        <?php if (!empty($errores)): ?>
        <!-- Errores de stock -->
        <div class="w-100 mb-3" style="max-width:480px;">
            <div class="alert alert-danger">
                <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Algunos productos no se pudieron procesar:</h6>
                <ul class="mb-0 mt-2">
                    <?php foreach ($errores as $e): ?>
                        <li><?php echo $e; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($comprados)): ?>

        <!-- Encabezado de éxito -->
        <div class="text-center mb-3">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2"
                 style="width:72px;height:72px;background:linear-gradient(135deg,#27ae60,#1e8449);">
                <i class="fas fa-check fa-2x text-white"></i>
            </div>
            <h4 class="fw-bold mb-0">¡Pago completado!</h4>
            <p class="text-muted small">Gracias por tu compra en Dulcería La Patita</p>
        </div>

        <!-- TICKET -->
        <div class="ticket w-100 px-4 pt-4 pb-2">

            <!-- Logo y nombre -->
            <div class="text-center mb-3">
                <div style="font-size:2rem;">🍬</div>
                <h5 class="fw-bold mb-0" style="color:#c0392b;">Dulcería "La Patita"</h5>
                <p class="text-muted small mb-0">Ticket de compra</p>
            </div>

            <div class="dashed-line"></div>

            <!-- Datos del ticket -->
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span><i class="fas fa-hashtag me-1"></i>Folio</span>
                <span class="fw-bold text-dark"><?php echo $folio; ?></span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span><i class="fas fa-calendar me-1"></i>Fecha</span>
                <span><?php echo $fecha; ?></span>
            </div>

            <div class="dashed-line"></div>

            <!-- Productos comprados -->
            <p class="fw-semibold text-muted small mb-2 text-uppercase" style="letter-spacing:.05em;">Productos</p>
            <?php foreach ($comprados as $item): ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold small"><?php echo htmlspecialchars($item['nombre']); ?></span>
                    <span class="small fw-bold" style="color:#c0392b;">$<?php echo number_format($item['subTotal'], 2); ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted" style="font-size:.8rem;"><?php echo htmlspecialchars($item['marca']); ?> &times; <?php echo $item['cantidad']; ?></span>
                    <span class="text-muted" style="font-size:.8rem;">$<?php echo number_format($item['precio'], 2); ?> c/u</span>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="dashed-line"></div>

            <!-- Total -->
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold fs-6">TOTAL PAGADO</span>
                <span class="fw-bold fs-5" style="color:#c0392b;">$<?php echo number_format($total, 2); ?> MXN</span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-3">
                <span><?php echo count($comprados); ?> producto(s)</span>
                <span><?php echo array_sum(array_column($comprados, 'cantidad')); ?> pieza(s)</span>
            </div>

            <!-- Mensaje de gracias -->
            <p class="text-center text-muted small mb-2">¡Gracias por tu preferencia! 🍭</p>
        </div>

        <!-- Borde inferior del ticket (efecto cortado) -->
        <div class="ticket-edge w-100" style="max-width:480px;">
            <?php for ($i = 0; $i < 18; $i++) echo '<span></span>'; ?>
        </div>
        <div class="ticket-footer w-100 px-4 py-3">
            <p class="text-center text-muted small mb-0">
                <i class="fas fa-store me-1"></i>Dulcería La Patita &mdash; &copy; 2025 Patricia Torres
            </p>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center mt-4 w-100" style="max-width:480px;">
            <a href="galeria_dinamica.php" class="btn btn-lg fw-semibold text-white flex-fill"
               style="background:linear-gradient(135deg,#ff6f61,#c0392b); border-radius:12px;">
                <i class="fas fa-store me-2"></i>Seguir comprando
            </a>
            <a href="index.html" class="btn btn-lg btn-outline-secondary fw-semibold flex-fill"
               style="border-radius:12px;">
                <i class="fas fa-home me-2"></i>Ir al inicio
            </a>
        </div>

        <?php else: ?>
        <!-- Si todos fallaron -->
        <div class="text-center mt-4">
            <i class="fas fa-times-circle fa-4x mb-3" style="color:#e74c3c;"></i>
            <h5 class="fw-bold">No se pudo completar el pago</h5>
            <a href="carrito.php" class="btn btn-outline-danger mt-2">
                <i class="fas fa-arrow-left me-1"></i>Regresar al carrito
            </a>
        </div>
        <?php endif; ?>

    </main>

    <footer class="text-center py-3 text-white mt-auto" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <small><i class="fas fa-copyright me-1"></i>2025 Patricia Torres | Dulcería La Patita</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
