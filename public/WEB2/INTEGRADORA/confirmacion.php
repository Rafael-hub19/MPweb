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

$sqlDetalle = "SELECT * FROM orden_detalle WHERE idOrden = $idOrden";
$resDetalle = mysqli_query($conexion, $sqlDetalle);
$detalles   = array();
if ($resDetalle) {
    while ($row = mysqli_fetch_assoc($resDetalle)) {
        $detalles[] = $row;
    }
}

mysqli_close($conexion);
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
        .page { max-width:900px; margin:0 auto; padding:48px 24px; }
        .page-lbl { font-size:13px; letter-spacing:3px; color:#27ae60; text-transform:uppercase; margin-bottom:8px; }
        h1 { font-family:'Bebas Neue',sans-serif; font-size:52px; line-height:1; margin-bottom:8px; color:#27ae60; }
        .order-num { font-size:15px; color:#999; margin-bottom:36px; }
        .order-num span { color:#ff6b35; font-weight:700; font-size:18px; }
        .panel { background:#151515; border:1px solid #222; border-radius:16px; padding:28px; margin-bottom:24px; }
        .panel h2 { font-family:'Bebas Neue',sans-serif; font-size:24px; color:#ff6b35; margin-bottom:18px; }
        /* Bootstrap handles the two-column responsive layout for info-grid */
        .info-item label { display:block; font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#666; margin-bottom:3px; }
        .info-item p { font-size:15px; color:#e8e8e8; }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#666; padding:8px 12px; border-bottom:1px solid #2a2a2a; }
        td { padding:10px 12px; font-size:15px; border-bottom:1px solid #1e1e1e; }
        td:last-child, th:last-child { text-align:right; }
        .total-row td { border-top:2px solid #ff6b35; border-bottom:none; font-family:'Bebas Neue',sans-serif; font-size:20px; padding-top:14px; }
        .total-row td:last-child { color:#ff6b35; }
        .pickup-box { background:#1a2a1a; border:1px solid #2a5a2a; border-radius:12px; padding:18px 22px; margin-bottom:24px; }
        .pickup-box h3 { font-family:'Bebas Neue',sans-serif; font-size:20px; color:#27ae60; margin-bottom:8px; }
        .pickup-box p { font-size:14px; color:#8ad48a; line-height:1.6; }
        .pickup-box p strong { color:#e8e8e8; }
        .ref-box { background:#1a1a2a; border:1px solid #2a2a5a; border-radius:10px; padding:14px 18px; margin-bottom:24px; font-size:13px; color:#8888cc; }
        .ref-box strong { color:#e8e8e8; }
        .btn-pri { display:inline-block; background:#ff6b35; color:#fff; padding:14px 32px; border-radius:10px; text-decoration:none; font-family:'Bebas Neue',sans-serif; font-size:20px; letter-spacing:1px; transition:background .2s; }
        .btn-pri:hover { background:#e85d2a; }
        .success-ico { font-size:64px; margin-bottom:12px; }
    </style>
</head>
<body>
<nav>
    <a class="brand" href="index.php">MotoStore</a>
    <div class="nav-r">
        <a href="galeria.php" class="btn-n">&#128661; Catalogo</a>
        <a href="logout.php" onclick="return confirm('&#191;Seguro?')" class="btn-n">Salir</a>
    </div>
</nav>
<div class="page">
    <div class="success-ico">&#9989;</div>
    <div class="page-lbl">Pago Exitoso</div>
    <h1>Compra Confirmada!</h1>
    <div class="order-num">Numero de orden: <span>#<?php echo $orden['idOrden']; ?></span></div>

    <div class="pickup-box">
        <h3>&#128661; Recogida en Agencia</h3>
        <p>Te contactaremos al <strong><?php echo htmlspecialchars($orden['telefono']); ?></strong> para coordinar la recogida en agencia. Tu moto estara lista para cuando llegues.</p>
    </div>

    <div class="panel">
        <h2>Informacion del Cliente</h2>
        <div class="row g-3">
            <div class="col-12 col-sm-6 info-item">
                <label>Nombre</label>
                <p><?php echo htmlspecialchars($orden['nombre']); ?></p>
            </div>
            <div class="col-12 col-sm-6 info-item">
                <label>Apellido</label>
                <p><?php echo htmlspecialchars($orden['apellido']); ?></p>
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

    <div class="panel">
        <h2>Productos Adquiridos</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align:center;">Cantidad</th>
                    <th>Precio Unit.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $det) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($det['nombreP']); ?></td>
                    <td style="text-align:center;"><?php echo (int)$det['cantidad']; ?></td>
                    <td>$<?php echo number_format((float)$det['precioP'], 0, '.', ','); ?></td>
                    <td>$<?php echo number_format((float)$det['precioP'] * (int)$det['cantidad'], 0, '.', ','); ?></td>
                </tr>
                <?php } ?>
                <tr class="total-row">
                    <td colspan="3">Total Pagado</td>
                    <td>$<?php echo number_format((float)$orden['total'], 0, '.', ','); ?> MXN</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="ref-box">
        <strong>Referencia PayPal:</strong> <?php echo htmlspecialchars($orden['paypal_order_id']); ?><br>
        <strong>Estado:</strong> <?php echo htmlspecialchars($orden['estado']); ?><br>
        <strong>Fecha:</strong> <?php echo htmlspecialchars($orden['fecha']); ?>
    </div>

    <a href="generar_reporte.php?orden=<?php echo $orden['idOrden']; ?>" target="_blank"
       style="display:inline-block; background:#1e1e1e; border:1px solid #444; color:#e8e8e8; padding:14px 32px; border-radius:10px; text-decoration:none; font-family:'Bebas Neue',sans-serif; font-size:20px; letter-spacing:1px; margin-right:12px; transition:background .2s;"
       onmouseover="this.style.background='#2a2a2a'" onmouseout="this.style.background='#1e1e1e'">
        &#128196; Descargar Ticket PDF
    </a>
    <a href="galeria.php" class="btn-pri">&#128661; Seguir Comprando</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
