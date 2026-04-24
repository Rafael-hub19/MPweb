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
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f0f0f; color: #e8e8e8; font-family: 'Outfit', sans-serif; min-height: 100vh; }
        nav { display:flex; align-items:center; justify-content:space-between; padding:16px 32px; background:#151515; border-bottom:1px solid #222; position:sticky; top:0; z-index:100; }
        .brand { font-family:'Bebas Neue',sans-serif; font-size:28px; color:#ff6b35; text-decoration:none; letter-spacing:2px; }
        .nav-r { display:flex; gap:12px; align-items:center; }
        .btn-n { padding:8px 18px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; color:#e8e8e8; background:#222; transition:background .2s; }
        .btn-n:hover { background:#333; }
        .page { max-width:960px; margin:0 auto; padding:48px 24px; }
        .page-lbl { font-size:13px; letter-spacing:3px; color:#27ae60; text-transform:uppercase; margin-bottom:8px; }
        h1 { font-family:'Bebas Neue',sans-serif; font-size:52px; line-height:1; margin-bottom:8px; color:#27ae60; }
        .order-num { font-size:15px; color:#999; margin-bottom:36px; }
        .order-num span { color:#ff6b35; font-weight:700; font-size:18px; }
        .panel { background:#151515; border:1px solid #222; border-radius:16px; padding:28px; margin-bottom:24px; }
        .panel h2 { font-family:'Bebas Neue',sans-serif; font-size:24px; color:#ff6b35; margin-bottom:18px; border-bottom:1px solid #2a2a2a; padding-bottom:10px; }
        .info-item label { display:block; font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:#555; margin-bottom:3px; }
        .info-item p { font-size:15px; color:#e8e8e8; }
        /* Tabla de productos */
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#555; padding:10px 12px; border-bottom:2px solid #2a2a2a; white-space:nowrap; }
        td { padding:10px 12px; font-size:14px; border-bottom:1px solid #1e1e1e; color:#e8e8e8; vertical-align:middle; }
        th:not(:first-child), td:not(:first-child) { text-align:right; }
        .marca-cell { font-size:12px; color:#888; }
        /* Filas de totales */
        .row-subtotal td { border-bottom:none; border-top:2px solid #2a2a2a; color:#aaa; font-size:13px; padding-top:12px; }
        .row-iva td { border-bottom:none; color:#cccc88; font-size:13px; }
        .row-iva td:first-child { display:flex; align-items:center; gap:6px; }
        .iva-badge { background:#333300; color:#cccc00; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px; letter-spacing:1px; }
        .row-total td { border-top:2px solid #ff6b35; border-bottom:none; font-family:'Bebas Neue',sans-serif; font-size:22px; padding-top:14px; }
        .row-total td:last-child { color:#ff6b35; }
        /* Caja de pickup */
        .pickup-box { background:#1a2a1a; border:1px solid #2a5a2a; border-radius:12px; padding:18px 22px; margin-bottom:24px; }
        .pickup-box h3 { font-family:'Bebas Neue',sans-serif; font-size:20px; color:#27ae60; margin-bottom:8px; }
        .pickup-box p { font-size:14px; color:#8ad48a; line-height:1.6; }
        .pickup-box p strong { color:#e8e8e8; }
        /* Caja de referencia */
        .ref-panel { background:#151515; border:1px solid #222; border-radius:16px; padding:24px 28px; margin-bottom:24px; }
        .ref-panel h2 { font-family:'Bebas Neue',sans-serif; font-size:24px; color:#ff6b35; margin-bottom:18px; border-bottom:1px solid #2a2a2a; padding-bottom:10px; }
        .ref-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px 24px; }
        @media(max-width:600px) { .ref-grid { grid-template-columns:1fr; } }
        .ref-item label { display:block; font-size:11px; text-transform:uppercase; letter-spacing:1.5px; color:#555; margin-bottom:3px; }
        .ref-item span { font-size:14px; color:#ccccee; font-family:'Courier New', monospace; word-break:break-all; }
        .ref-item.full { grid-column:1/-1; }
        .badge-pagado { background:#1a3a1a; color:#4fc04f; font-size:12px; font-weight:700; padding:3px 10px; border-radius:6px; border:1px solid #2a5a2a; font-family:'Outfit',sans-serif; text-transform:uppercase; letter-spacing:1px; }
        /* Botones */
        .btn-pri { display:inline-block; background:#ff6b35; color:#fff; padding:14px 28px; border-radius:10px; text-decoration:none; font-family:'Bebas Neue',sans-serif; font-size:18px; letter-spacing:1px; transition:background .2s; margin-bottom:10px; }
        .btn-pri:hover { background:#e85d2a; color:#fff; }
        .btn-sec { display:inline-block; padding:14px 28px; border-radius:10px; text-decoration:none; font-family:'Bebas Neue',sans-serif; font-size:18px; letter-spacing:1px; margin-right:10px; margin-bottom:10px; transition:background .2s; }
        .btn-factura { background:#1a2a1a; border:1px solid #2a5a2a; color:#8ad48a; }
        .btn-factura:hover { background:#223a22; color:#8ad48a; }
        .btn-ticket { background:#1e1e1e; border:1px solid #444; color:#e8e8e8; }
        .btn-ticket:hover { background:#2a2a2a; color:#e8e8e8; }
        .success-ico { font-size:64px; margin-bottom:12px; }
        .actions { display:flex; flex-wrap:wrap; align-items:center; gap:0; }
        footer { background:#0d1117; border-top:1px solid rgba(255,255,255,.07); padding:40px 32px; margin-top:32px; }
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
