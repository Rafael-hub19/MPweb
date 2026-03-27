<?php
/* agregar.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
include 'conectbd.php';

$mensaje = '';
$tipo    = '';
$moto    = null;
$xmlFile = 'carrito.xml';

if (isset($_GET['id'])) {
    $id  = intval($_GET['id']);
    $sql = "SELECT * FROM `productos` WHERE `idP` = $id";
    $res = mysqli_query($conexion, $sql);
    if ($res && mysqli_num_rows($res) > 0) {
        $moto = mysqli_fetch_assoc($res);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'acumular') {
    $idP      = isset($_POST['idP'])      ? intval($_POST['idP'])      : 0;
    $nombre   = isset($_POST['nombre'])   ? trim($_POST['nombre'])     : '';
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad'])  : 0;
    if ($idP <= 0 || $nombre == '' || $cantidad < 1) {
        $mensaje = 'Todos los campos son obligatorios y la cantidad debe ser valida.';
        $tipo    = 'error';
    } else {
        $sqlP = "SELECT `precioP`, `existenciaP` FROM `productos` WHERE `idP` = $idP";
        $resP = mysqli_query($conexion, $sqlP);
        if ($resP && $rowP = mysqli_fetch_assoc($resP)) {
            if ($cantidad > $rowP['existenciaP']) {
                $mensaje = 'No hay suficiente existencia disponible.';
                $tipo    = 'error';
            } else {
                if (file_exists($xmlFile)) {
                    $xml = simplexml_load_file($xmlFile);
                } else {
                    $xml = new SimpleXMLElement('<carrito/>');
                }
                $encontrado = false;
                foreach ($xml->item as $item) {
                    if ((int)$item->idP == $idP) {
                        $item->cantidad = (int)$item->cantidad + $cantidad;
                        $encontrado = true;
                        break;
                    }
                }
                if (!$encontrado) {
                    $item = $xml->addChild('item');
                    $item->addChild('idP',      $idP);
                    $item->addChild('nombre',   htmlspecialchars($nombre));
                    $item->addChild('precio',   $rowP['precioP']);
                    $item->addChild('cantidad', $cantidad);
                }
                $xml->asXML($xmlFile);
                $mensaje = 'Producto agregado al carrito exitosamente.';
                $tipo    = 'success';
            }
        } else {
            $mensaje = 'Producto no encontrado.';
            $tipo    = 'error';
        }
    }
}

if ($moto == null && $tipo == '') {
    mysqli_close($conexion);
    header("Location: galeria.php");
    exit();
}
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar al Carrito - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/integradora_agregar.css">
</head>
<body>
<div class="card">
    <div class="brand"><img src="img/Logo_motostore.png" alt="MotoStore" style="height:60px;filter:drop-shadow(0 0 6px rgba(255,107,53,0.35));"></div>
    <h2>Agregar al Carrito</h2>
    <?php if ($mensaje != '') {
        $cls = ($tipo == 'success') ? 'alert-s' : 'alert-e';
        echo '<div class="' . $cls . '">' . htmlspecialchars($mensaje) . '</div>';
    } ?>
    <?php if ($moto != null) { ?>
    <div class="pinfo">
        <img src="imagen.php?id=<?php echo $moto['idP']; ?>" alt="<?php echo htmlspecialchars($moto['nombreP']); ?>"
             style="width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:12px;display:block;">
        <div class="nm"><?php echo htmlspecialchars($moto['nombreP']); ?></div>
        <div class="pr">$<?php echo number_format($moto['precioP'], 0, '.', ','); ?> MXN</div>
    </div>
    <form method="POST" action="agregar.php?id=<?php echo $moto['idP']; ?>" onsubmit="return val()">
        <input type="hidden" name="action" value="acumular">
        <input type="hidden" name="idP" value="<?php echo $moto['idP']; ?>">
        <div class="field">
            <label>Nombre del Producto</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($moto['nombreP']); ?>" readonly class="borde">
        </div>
        <div class="field">
            <label>Precio</label>
            <input type="text" name="precio" value="$<?php echo number_format($moto['precioP'], 2); ?> MXN" readonly class="borde">
        </div>
        <div class="field">
            <label for="cant">Cantidad a Pedir</label>
            <input type="number" id="cant" name="cantidad" placeholder="1" min="1" max="10" value="1">
        </div>
        <button type="submit" class="btn-a">Agregar al Carrito</button>
    </form>
    <?php } ?>
    <div class="links">
        <a href="galeria.php">&larr; Volver a Galeria</a>
        <a href="carrito.php">Ver Carrito &#128722;</a>
    </div>
</div>
<script src="../../assets/js/integradora_agregar.js"></script>
</body>
</html>
