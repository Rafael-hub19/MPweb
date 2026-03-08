<?php
/* carrito.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
include 'conectbd.php';

$xmlFile  = 'carrito.xml';
$mensaje  = '';
$tipo     = '';
$compraOk = false;

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
    } elseif (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        if (isset($xml->item[$idx])) {
            $xml->item[$idx]->cantidad = $cantidad;
            $xml->asXML($xmlFile);
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
    $idx = isset($_POST['idx']) ? intval($_POST['idx']) : -1;
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
        if (isset($xml->item[$idx])) {
            unset($xml->item[$idx]);
            $xml->asXML($xmlFile);
            $mensaje = 'Producto eliminado del carrito.';
            $tipo    = 'success';
        } else {
            $mensaje = 'Producto no encontrado.';
            $tipo    = 'error';
        }
    }
}

/* COMPRAR */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'comprar') {
    if (!file_exists($xmlFile)) {
        $mensaje = 'El carrito esta vacio.';
        $tipo    = 'error';
    } else {
        $xml = simplexml_load_file($xmlFile);
        if (count($xml->item) == 0) {
            $mensaje = 'El carrito esta vacio.';
            $tipo    = 'error';
        } else {
            foreach ($xml->item as $item) {
                $idP      = intval($item->idP);
                $cantidad = intval($item->cantidad);
                $sql = "UPDATE `productos` SET `existenciaP` = `existenciaP` - $cantidad WHERE `idP` = $idP AND `existenciaP` >= $cantidad";
                mysqli_query($conexion, $sql);
            }
            $xmlNuevo = new SimpleXMLElement('<carrito/>');
            $xmlNuevo->asXML($xmlFile);
            $compraOk = true;
        }
    }
}

/* READ */
$items = array();
$total = 0;
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    foreach ($xml->item as $it) {
        $items[] = array(
            'idP'      => (int)$it->idP,
            'nombre'   => (string)$it->nombre,
            'precio'   => (float)$it->precio,
            'cantidad' => (int)$it->cantidad,
        );
        $total += (float)$it->precio * (int)$it->cantidad;
    }
}
$xmlRaw = file_exists($xmlFile) ? file_get_contents($xmlFile) : '<carrito/>';
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <a class="brand" href="galeria.php">MotoStore</a>
    <div class="nav-r">
        <a href="galeria.php" class="btn-n">&larr; Galeria</a>
        <a href="logout.php" class="btn-n">Cerrar Sesion</a>
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
        <div class="summary">
            <div>
                <div class="s-lbl">Total a pagar</div>
                <div class="s-tot">$<?php echo number_format($total, 0, '.', ','); ?> MXN</div>
            </div>
            <form method="POST" action="" onsubmit="return confirm('Confirmar la compra? Se vaciara el carrito.')">
                <input type="hidden" name="action" value="comprar">
                <button type="submit" class="btn-buy">&#128661; Comprar Ahora</button>
            </form>
        </div>
        <div class="xml-box">
            <h3>&#128196; Contenido actual de carrito.xml</h3>
            <pre><?php echo htmlspecialchars($xmlRaw); ?></pre>
        </div>
    <?php } ?>
</div>
<script src="../../assets/js/integradora_carrito.js"></script>
</body>
</html>
