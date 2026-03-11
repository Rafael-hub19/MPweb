<?php
/* galeria.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
$loggedIn = isset($_SESSION['usuario']);
include 'conectbd.php';

$mensaje = '';
$tipo    = '';
$xmlFile = 'carrito.xml';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'agregar') {
    if (!$loggedIn) {
        header("Location: login.php?redirect=galeria.php");
        exit();
    }
    $idP      = isset($_POST['idP'])      ? intval($_POST['idP'])      : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    if ($idP <= 0) {
        $mensaje = 'Producto no valido.';
        $tipo    = 'error';
    } elseif ($cantidad < 1 || $cantidad > 10) {
        $mensaje = 'La cantidad debe estar entre 1 y 10.';
        $tipo    = 'error';
    } else {
        $sql = "SELECT * FROM `productos` WHERE `idP` = $idP";
        $res = mysqli_query($conexion, $sql);
        if ($res && $row = mysqli_fetch_assoc($res)) {
            if ($cantidad > $row['existenciaP']) {
                $mensaje = 'No hay suficiente existencia. Solo quedan ' . $row['existenciaP'] . ' unidades.';
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
                    $item->addChild('nombre',   htmlspecialchars($row['nombreP']));
                    $item->addChild('precio',   $row['precioP']);
                    $item->addChild('cantidad', $cantidad);
                }
                $xml->asXML($xmlFile);
                $mensaje = htmlspecialchars($row['nombreP']) . ' agregado al carrito.';
                $tipo    = 'success';
            }
        } else {
            $mensaje = 'Producto no encontrado.';
            $tipo    = 'error';
        }
    }
}

$cartCount = 0;
if (file_exists($xmlFile)) {
    $xmlCart   = simplexml_load_file($xmlFile);
    $cartCount = count($xmlCart->item);
}

$sqlGal = "SELECT `idP`, `nombreP`, `marcaP`, `tipoImagenP`, `existenciaP`, `precioP` 
           FROM `productos` 
           WHERE `existenciaP` > 0 
           ORDER BY `idP` ASC";
$resultado = mysqli_query($conexion, $sqlGal);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria de Motos - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/integradora_galeria.css">
</head>
<body>
<nav>
    <a class="brand" href="index.php">MotoStore</a>
    <div class="nav-r">
        <?php if ($loggedIn) { ?>
            <span class="nav-u">Hola, <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span></span>
            <a href="index.php" class="btn-n btn-out">Inicio</a>
            <a href="carrito.php" class="btn-n btn-cart">
                &#128722; Carrito
                <?php if ($cartCount > 0) { echo '<span class="cart-n">' . $cartCount . '</span>'; } ?>
            </a>
            <a href="logout.php" onclick="return confirm('¿Seguro que deseas cerrar sesion?')" class="btn-n btn-out">Cerrar Sesion</a>
        <?php } else { ?>
            <a href="index.php" class="btn-n btn-out">Inicio</a>
            <a href="login.php?redirect=galeria.php" class="btn-n btn-cart">&#128274; Iniciar Sesion</a>
            <a href="registro.php" class="btn-n btn-out">Registrarse</a>
        <?php } ?>
    </div>
</nav>
<a href="../web2.html" class="btn-back-link">&larr; Regresar a WEB 2</a>
<div class="hero">
    <div class="hero-lbl">Catalogo 2026</div>
    <h1>Galeria de<br>Motocicletas</h1>
    <p>Explora nuestra seleccion de motocicletas premium y agregala al carrito.</p>
</div>
<?php if ($mensaje != '') { ?>
<div class="alert-w"><div class="alert alert-<?php echo $tipo; ?>"><?php echo $mensaje; ?></div></div>
<?php } ?>
<div class="gallery">
<?php
if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($moto = mysqli_fetch_assoc($resultado)) {
        echo '<div class="pcard">';
        echo '<img src="imagen.php?id=' . $moto['idP'] . '" alt="' . htmlspecialchars($moto['nombreP']) . '" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'flex\'">';
        echo '<div class="pcard-ph">&#128661;</div>';
        echo '<div class="pbody">';
        echo '<div class="pbrand">' . htmlspecialchars($moto['marcaP']) . '</div>';
        echo '<div class="pname">' . htmlspecialchars($moto['nombreP']) . '</div>';
        echo '<div class="pmeta">';
        echo '<div class="pprice">$' . number_format($moto['precioP'], 0, '.', ',') . ' MXN</div>';
        echo '<div class="pstock">&#128230; ' . $moto['existenciaP'] . ' en stock</div>';
        echo '</div>';
        if ($loggedIn) {
            echo '<form method="POST" action="" class="add-f" onsubmit="return valQty(this)">';
            echo '<input type="hidden" name="action" value="agregar">';
            echo '<input type="hidden" name="idP" value="' . $moto['idP'] . '">';
            echo '<input type="number" name="cantidad" value="1" min="1" max="' . $moto['existenciaP'] . '" class="qty" title="Cantidad">';
            echo '<button type="submit" class="btn-add">&#128722; Agregar</button>';
            echo '</form>';
        } else {
            echo '<a href="login.php?redirect=galeria.php" class="btn-add btn-add-login">&#128274; Iniciar sesion para comprar</a>';
        }
        echo '</div></div>';
    }
} else {
    echo '<div class="empty"><p>No hay productos disponibles.</p></div>';
}
mysqli_close($conexion);
?>
</div>
<script src="../../assets/js/integradora_galeria.js"></script>
</body>
</html>
