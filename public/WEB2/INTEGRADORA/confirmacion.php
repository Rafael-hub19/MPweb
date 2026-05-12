<?php
/* confirmacion.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

include 'conectbd.php';

$idOrden = isset($_GET['orden']) ? intval($_GET['orden']) : 0;

if ($idOrden <= 0) {
    header('Location: index.php');
    exit();
}

$sqlOrden = "SELECT o.*, u.usuarioU FROM ordenes o JOIN usuarios u ON o.idUsuario = u.idU WHERE o.idOrden = $idOrden";
$resOrden = mysqli_query($conexion, $sqlOrden);
$orden    = $resOrden ? mysqli_fetch_assoc($resOrden) : null;

if (!$orden) {
    header('Location: index.php');
    exit();
}

$sqlDetalle = "SELECT od.*, p.marcaP FROM orden_detalle od LEFT JOIN productos p ON od.idP = p.idP WHERE od.idOrden = $idOrden";
$resDetalle = mysqli_query($conexion, $sqlDetalle);
$detalles   = array();
if ($resDetalle) {
    while ($row = mysqli_fetch_assoc($resDetalle)) {
        $detalles[] = $row;
    }
}

mysqli_close($conexion);

/* ── Calculos de IVA ── */
$totalFinal     = (float)$orden['total'];
$subtotalSinIva = round($totalFinal / 1.16, 2);
$ivaAmount      = round($totalFinal - $subtotalSinIva, 2);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Confirmada - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_confirmacion.css">
</head>
<body>
<nav>
    <a class="brand" href="index.php"><img src="img/Logo_motostore.png" alt="MotoStore" class="nav-logo"></a>
    <div class="nav-r">
        <a href="galeria.php" class="btn-n">&#128661; Catalogo</a>
        <a href="logout.php" onclick="return confirm('&#191;Seguro?')" class="btn-n">Salir</a>
    </div>
</nav>
<div class="page">
    <div class="success-ico">&#9989;</div>
    <div class="page-lbl">Pago Exitoso</div>
    <h1>Compra Confirmada!</h1>
    <div class="order-num">Numero de orden: <span>#<?php echo str_pad($orden['idOrden'], 6, '0', STR_PAD_LEFT); ?></span></div>

    <div class="pickup-box">
        <h3>&#128661; Recogida en Agencia</h3>
        <p>Te contactaremos al <strong><?php echo htmlspecialchars($orden['telefono']); ?></strong> para coordinar la recogida. Presenta tu factura o ticket al llegar a la agencia.</p>
    </div>

    <!-- Informacion del cliente -->
    <div class="panel">
        <h2>Informacion del Cliente</h2>
        <div class="row g-3">
            <div class="col-12 col-sm-6 info-item">
                <label>Nombre completo</label>
                <p><?php echo htmlspecialchars($orden['nombre'] . ' ' . $orden['apellido']); ?></p>
            </div>
            <div class="col-12 col-sm-6 info-item">
                <label>Usuario</label>
                <p><?php echo htmlspecialchars($orden['usuarioU']); ?></p>
            </div>
            <div class="col-12 col-sm-6 info-item">
                <label>Telefono</label>
                <p><?php echo htmlspecialchars($orden['telefono']); ?></p>
            </div>
            <div class="col-12 col-sm-6 info-item">
                <label>Email</label>
                <p><?php echo htmlspecialchars($orden['email']); ?></p>
            </div>
            <?php if (!empty($orden['notas'])) { ?>
            <div class="col-12 info-item">
                <label>Notas</label>
                <p><?php echo htmlspecialchars($orden['notas']); ?></p>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Productos con desglose de IVA -->
    <div class="panel">
        <h2>Detalle de la Compra</h2>
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Marca</th>
                    <th>Cant.</th>
                    <th>P. Unit. s/IVA</th>
                    <th>Subtotal s/IVA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $det):
                    $precioSinIva  = round((float)$det['precioP'] / 1.16, 2);
                    $subtotalDetSin = round($precioSinIva * (int)$det['cantidad'], 2);
                    $marca = !empty($det['marcaP']) ? $det['marcaP'] : '—';
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($det['nombreP']); ?></td>
                    <td class="marca-cell"><?php echo htmlspecialchars($marca); ?></td>
                    <td><?php echo (int)$det['cantidad']; ?></td>
                    <td>$<?php echo number_format($precioSinIva, 2, '.', ','); ?></td>
                    <td>$<?php echo number_format($subtotalDetSin, 2, '.', ','); ?></td>
                </tr>
                <?php endforeach; ?>

                <!-- Subtotal sin IVA -->
                <tr class="row-subtotal">
                    <td colspan="4">Subtotal (sin IVA)</td>
                    <td>$<?php echo number_format($subtotalSinIva, 2, '.', ','); ?></td>
                </tr>
                <!-- IVA -->
                <tr class="row-iva">
                    <td colspan="4">
                        IVA <span class="iva-badge">16%</span>
                    </td>
                    <td>$<?php echo number_format($ivaAmount, 2, '.', ','); ?></td>
                </tr>
                <!-- Total final -->
                <tr class="row-total">
                    <td colspan="4">Total Pagado (MXN)</td>
                    <td>$<?php echo number_format($totalFinal, 2, '.', ','); ?></td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Referencia del pago -->
    <div class="ref-panel">
        <h2>Datos del Pago</h2>
        <div class="ref-grid">
            <div class="ref-item">
                <label>No. de Orden</label>
                <span>#<?php echo str_pad($orden['idOrden'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="ref-item">
                <label>Estado</label>
                <span class="badge-pagado"><?php echo htmlspecialchars($orden['estado']); ?></span>
            </div>
            <div class="ref-item">
                <label>Fecha y Hora</label>
                <span><?php echo date('d/m/Y H:i:s', strtotime($orden['fecha'])); ?></span>
            </div>
            <div class="ref-item">
                <label>Forma de Pago</label>
                <span>PayPal (Sandbox)</span>
            </div>
            <div class="ref-item full">
                <label>Referencia PayPal</label>
                <span><?php echo htmlspecialchars($orden['paypal_order_id'] ? $orden['paypal_order_id'] : 'N/A'); ?></span>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="actions">
        <a href="generar_factura.php?orden=<?php echo $orden['idOrden']; ?>" target="_blank" class="btn-sec btn-factura">
            &#128196; Descargar Factura PDF
        </a>
        <a href="generar_ticket.php?orden=<?php echo $orden['idOrden']; ?>" target="_blank" class="btn-sec btn-ticket">
            &#129534; Descargar Ticket
        </a>
        <a href="galeria.php" class="btn-pri">&#128661; Seguir Comprando</a>
    </div>
</div>

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
