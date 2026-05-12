<?php
/* carrito.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
include 'conectbd.php';

$jsonFile = 'carrito.json';
$mensaje  = '';
$tipo     = '';
$compraOk = false;

/* ── Funcion auxiliar: cargar carrito ── */
function cargar_carrito($jsonFile) {
    if (!file_exists($jsonFile)) return array();
    $dec = json_decode(file_get_contents($jsonFile), true);
    return is_array($dec) ? $dec : array();
}

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'actualizar') {
    $idx      = isset($_POST['idx'])      ? intval($_POST['idx'])      : -1;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    if ($cantidad < 1) {
        $mensaje = 'La cantidad debe ser al menos 1.';
        $tipo    = 'error';
    } elseif ($cantidad > 99) {
        $mensaje = 'La cantidad maxima es 99.';
        $tipo    = 'error';
    } else {
        $carrito = cargar_carrito($jsonFile);
        if (isset($carrito[$idx])) {
            $carrito[$idx]['cantidad'] = $cantidad;
            file_put_contents($jsonFile, json_encode($carrito));
            $mensaje = 'Cantidad actualizada.';
            $tipo    = 'success';
        } else {
            $mensaje = 'Producto no encontrado en el carrito.';
            $tipo    = 'error';
        }
    }
}

/* DELETE */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'eliminar') {
    $idx     = isset($_POST['idx']) ? intval($_POST['idx']) : -1;
    $carrito = cargar_carrito($jsonFile);
    if (isset($carrito[$idx])) {
        array_splice($carrito, $idx, 1);
        file_put_contents($jsonFile, json_encode($carrito));
        $mensaje = 'Producto eliminado del carrito.';
        $tipo    = 'success';
    } else {
        $mensaje = 'Producto no encontrado.';
        $tipo    = 'error';
    }
}

/* COMPRAR */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'comprar') {
    $carrito = cargar_carrito($jsonFile);
    if (count($carrito) == 0) {
        $mensaje = 'El carrito esta vacio.';
        $tipo    = 'error';
    } else {
        foreach ($carrito as $item) {
            $idP      = intval($item['idP']);
            $cantidad = intval($item['cantidad']);
            $sql = "UPDATE `productos` SET `existenciaP` = `existenciaP` - $cantidad WHERE `idP` = $idP AND `existenciaP` >= $cantidad";
            mysqli_query($conexion, $sql);
        }
        file_put_contents($jsonFile, json_encode(array()));
        $compraOk = true;
    }
}

/* READ */
$items   = array();
$total   = 0;
$carrito = cargar_carrito($jsonFile);
foreach ($carrito as $it) {
    $items[] = array(
        'idP'      => (int)$it['idP'],
        'nombre'   => (string)$it['nombre'],
        'precio'   => (float)$it['precio'],
        'cantidad' => (int)$it['cantidad'],
    );
    $total += (float)$it['precio'] * (int)$it['cantidad'];
}
$jsonRaw = file_exists($jsonFile) ? file_get_contents($jsonFile) : '[]';
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_carrito.css">
</head>
<body>
<?php if ($compraOk) { ?>
<div class="modal-ov show" id="mc">
    <div class="modal">
        <div class="ico">&#9989;</div>
        <h2>Compra Realizada!</h2>
        <p>Tu compra fue procesada. Las existencias han sido actualizadas en la base de datos MySQL.</p>
        <a href="galeria.php" class="btn-modal">Seguir Comprando</a>
    </div>
</div>
<?php } ?>
<nav>
    <a class="brand" href="index.php"><img src="img/Logo_motostore.png" alt="MotoStore" class="nav-logo"></a>
    <div class="nav-r">
        <a href="index.php" class="btn-n btn-out">Inicio</a>
        <a href="galeria.php" class="btn-n btn-out">&#128661; Catalogo</a>
        <a href="logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesion?')" class="btn-n">Cerrar Sesion</a>
    </div>
</nav>

<div class="page">
    <div class="page-lbl">Tu pedido</div>
    <h1>Carrito de<br>Compras</h1>
    <?php if ($mensaje != '' && !$compraOk) { ?>
        <div class="alert alert-<?php echo $tipo; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php } ?>
    <?php if (count($items) == 0) { ?>
        <div class="empty">
            <div class="ico">&#128722;</div>
            <h2>Tu carrito esta vacio</h2>
            <p>Explora la galeria y agrega las motos que te interesen.</p>
            <a href="galeria.php" class="btn-pri">Ver Galeria</a>
        </div>
    <?php } else { ?>
        <div class="tw">
            <div class="table-responsive">
            <table>
                <thead><tr><th>#</th><th>Producto</th><th>Precio Unit.</th><th>Cantidad</th><th>Subtotal</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($items as $i => $item) { ?>
                <tr>
                    <td style="color:#64748b"><?php echo $i + 1; ?></td>
                    <td class="td-n"><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td class="td-p">$<?php echo number_format($item['precio'], 0, '.', ','); ?></td>
                    <td>
                        <form method="POST" action="" class="upd-f" onsubmit="return valU(this)">
                            <input type="hidden" name="action" value="actualizar">
                            <input type="hidden" name="idx" value="<?php echo $i; ?>">
                            <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1" max="99" class="qty">
                            <button type="submit" class="btn-u">&#10003;</button>
                        </form>
                    </td>
                    <td class="td-s">$<?php echo number_format($item['precio'] * $item['cantidad'], 0, '.', ','); ?></td>
                    <td>
                        <form method="POST" action="" onsubmit="return confirm('Quitar este producto?')">
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="idx" value="<?php echo $i; ?>">
                            <button type="submit" class="btn-d">&#10005; Quitar</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
        <div class="summary">
            <div>
                <div class="s-lbl">Total a pagar</div>
                <div class="s-tot">$<?php echo number_format($total, 0, '.', ','); ?> MXN</div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <a href="ver_json.php" class="btn-json" title="Ver el archivo carrito.json">&#128196; Ver JSON</a>
                <a href="checkout.php" class="btn-buy">&#128661; Proceder al Pago</a>
            </div>
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
            <a href="ver_json.php">Ver JSON</a>
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
<script src="../../assets/js/integradora_carrito.js"></script>
</body>
</html>
