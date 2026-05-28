<?php
/* mis_pedidos.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
include 'conectbd.php';

$idU = (int)$_SESSION['idU'];

$sqlOrdenes = "SELECT o.idOrden, o.nombre, o.apellido, o.total, o.estado, o.fecha,
                      COUNT(od.idDetalle) AS num_items
               FROM ordenes o
               LEFT JOIN orden_detalle od ON o.idOrden = od.idOrden
               WHERE o.idUsuario = $idU
               GROUP BY o.idOrden
               ORDER BY o.fecha DESC";
$resOrdenes = mysqli_query($conexion, $sqlOrdenes);
$ordenes = array();
if ($resOrdenes) {
    while ($row = mysqli_fetch_assoc($resOrdenes)) {
        $ordenes[] = $row;
    }
}
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_pedidos.css">
</head>
<body>

<nav>
    <a class="brand" href="index.php"><img src="img/Logo_motostore.png" alt="MotoStore" class="nav-logo"></a>
    <div class="nav-r">
        <div class="user-dd" id="userDd">
            <button class="user-badge" id="userBadgeBtn" type="button">
                <div class="user-av"><?php echo strtoupper(mb_substr($_SESSION['usuario'], 0, 1)); ?></div>
                <span class="user-nm"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <span class="dd-caret">&#9660;</span>
            </button>
            <div class="dd-panel" id="ddPanel">
                <div class="dd-head">
                    <div class="user-av"><?php echo strtoupper(mb_substr($_SESSION['usuario'], 0, 1)); ?></div>
                    <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
                <div class="dd-sep"></div>
                <a href="mis_pedidos.php" class="dd-item">&#128203; Mis Pedidos</a>
                <a href="logout.php" onclick="return confirm('&#191;Seguro que deseas cerrar sesion?')" class="dd-item dd-out">Cerrar Sesion</a>
            </div>
        </div>
        <a href="galeria.php" class="btn-n">&#128661; Catalogo</a>
        <a href="carrito.php" class="btn-n">&#128722; Carrito</a>
    </div>
</nav>

<div class="page">
    <div class="page-lbl">Tu cuenta</div>
    <h1>Mis Pedidos</h1>

    <?php if (count($ordenes) === 0) { ?>
        <div class="empty">
            <div class="empty-ico">&#128203;</div>
            <h2>Sin pedidos aun</h2>
            <p>Cuando realices una compra apareceran aqui todos tus pedidos.</p>
            <a href="galeria.php" class="btn-pri">&#128661; Ir al Catalogo</a>
        </div>
    <?php } else { ?>
        <div class="orders-list">
            <?php foreach ($ordenes as $o):
                $num      = str_pad($o['idOrden'], 6, '0', STR_PAD_LEFT);
                $fecha    = date('d/m/Y H:i', strtotime($o['fecha']));
                $total    = number_format((float)$o['total'], 2, '.', ',');
                $items    = (int)$o['num_items'];
                $estado   = htmlspecialchars($o['estado']);
                $statusCls = 'status-' . strtolower($estado);
            ?>
            <a href="confirmacion.php?orden=<?php echo $o['idOrden']; ?>" class="order-card">
                <div class="order-num-badge">
                    <small>Orden</small>
                    #<?php echo $num; ?>
                </div>
                <div class="order-meta">
                    <span class="order-date">&#128197; <?php echo $fecha; ?></span>
                    <span class="order-items">&#128230; <?php echo $items; ?> producto<?php echo $items !== 1 ? 's' : ''; ?></span>
                </div>
                <div class="order-right">
                    <div class="order-total">$<?php echo $total; ?> <small>MXN</small></div>
                    <span class="status-badge <?php echo $statusCls; ?>"><?php echo $estado; ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php } ?>
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
<script>
(function(){
    var btn = document.getElementById('userBadgeBtn');
    var panel = document.getElementById('ddPanel');
    if (!btn) return;
    btn.addEventListener('click', function(e){ e.stopPropagation(); panel.classList.toggle('open'); });
    document.addEventListener('click', function(){ panel.classList.remove('open'); });
})();
</script>
</body>
</html>
