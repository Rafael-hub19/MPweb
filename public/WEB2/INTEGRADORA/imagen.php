<?php
/* imagen.php - Sirve la imagen de un producto desde la BD
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */

include 'conectbd.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("HTTP/1.0 400 Bad Request");
    exit();
}

$sql = "SELECT `imagenP`, `tipoImagenP` FROM `productos` WHERE `idP` = $id";
$res = mysqli_query($conexion, $sql);

if ($res && $row = mysqli_fetch_assoc($res)) {
    $tipo = isset($row['tipoImagenP']) && $row['tipoImagenP'] != '' ? $row['tipoImagenP'] : 'image/jpeg';
    header("Content-Type: " . $tipo);
    header("Cache-Control: max-age=86400");
    echo $row['imagenP'];
} else {
    header("HTTP/1.0 404 Not Found");
}

mysqli_close($conexion);
?>
