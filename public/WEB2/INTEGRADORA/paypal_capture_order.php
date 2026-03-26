<?php
/* paypal_capture_order.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(array('error' => 'No autorizado. Inicia sesion para continuar.'));
    exit;
}

$_input  = file_get_contents('php://input');
$body    = json_decode($_input, true);
$orderID = isset($body['orderID']) ? $body['orderID'] : null;

if (!$orderID) {
    echo json_encode(array('error' => 'orderID no proporcionado.'));
    exit;
}

include 'paypal_config.php';
include 'conectbd.php';

$capture = paypal_capture_order($orderID);

if (!$capture) {
    echo json_encode(array('error' => 'No se pudo conectar con PayPal para capturar la orden.'));
    exit;
}

$status = isset($capture['status']) ? $capture['status'] : '';

if ($status !== 'COMPLETED') {
    $msg = isset($capture['message']) ? $capture['message'] : 'La captura no fue completada. Estado: ' . $status;
    echo json_encode(array('error' => $msg));
    exit;
}

// Payment completed
if (!isset($_SESSION['pedido_data'])) {
    echo json_encode(array('error' => 'Datos del pedido no encontrados en sesion.'));
    exit;
}

$pedido   = $_SESSION['pedido_data'];
$nombre   = isset($pedido['nombre'])   ? $pedido['nombre']   : '';
$apellido = isset($pedido['apellido']) ? $pedido['apellido'] : '';
$telefono = isset($pedido['telefono']) ? $pedido['telefono'] : '';
$email    = isset($pedido['email'])    ? $pedido['email']    : '';
$notas    = isset($pedido['notas'])    ? $pedido['notas']    : '';

$carrito_path = __DIR__ . '/carrito.xml';

$items = array();
$total = 0.0;

if (file_exists($carrito_path)) {
    $xml = simplexml_load_file($carrito_path);
    if ($xml !== false && isset($xml->item)) {
        foreach ($xml->item as $item) {
            $idP      = (int)$item->idP;
            $itemNombre = (string)$item->nombre;
            $precio   = (float)$item->precio;
            $cantidad = (int)$item->cantidad;
            if ($idP > 0 && $cantidad > 0) {
                $items[] = array(
                    'idP'      => $idP,
                    'nombre'   => $itemNombre,
                    'precio'   => $precio,
                    'cantidad' => $cantidad,
                );
                $total += $precio * $cantidad;
            }
        }
    }
}

if ($total <= 0) {
    echo json_encode(array('error' => 'El total del pedido es invalido.'));
    exit;
}

// Get idUsuario
$usuarioEsc = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
$resU = mysqli_query($conexion, "SELECT idU FROM usuarios WHERE usuarioU = '$usuarioEsc'");
$rowU = mysqli_fetch_assoc($resU);
if (!$rowU) {
    echo json_encode(array('error' => 'Usuario no encontrado en la base de datos.'));
    exit;
}
$idUsuario = (int)$rowU['idU'];

$paypal_order_id = isset($capture['id']) ? $capture['id'] : $orderID;

$nombreEsc   = mysqli_real_escape_string($conexion, $nombre);
$apellidoEsc = mysqli_real_escape_string($conexion, $apellido);
$telefonoEsc = mysqli_real_escape_string($conexion, $telefono);
$emailEsc    = mysqli_real_escape_string($conexion, $email);
$notasEsc    = mysqli_real_escape_string($conexion, $notas);
$paypalIdEsc = mysqli_real_escape_string($conexion, $paypal_order_id);

$sqlOrden = "INSERT INTO ordenes (idUsuario, nombre, apellido, telefono, email, notas, total, paypal_order_id, estado)
             VALUES ($idUsuario, '$nombreEsc', '$apellidoEsc', '$telefonoEsc', '$emailEsc', '$notasEsc', $total, '$paypalIdEsc', 'pagado')";
mysqli_query($conexion, $sqlOrden);
$idOrden = mysqli_insert_id($conexion);

foreach ($items as $item) {
    $idP       = (int)$item['idP'];
    $nombreP   = mysqli_real_escape_string($conexion, $item['nombre']);
    $precioP   = (float)$item['precio'];
    $cantidad  = (int)$item['cantidad'];
    $sqlDetalle = "INSERT INTO orden_detalle (idOrden, idP, nombreP, precioP, cantidad)
                   VALUES ($idOrden, $idP, '$nombreP', $precioP, $cantidad)";
    mysqli_query($conexion, $sqlDetalle);

    $sqlStock = "UPDATE productos SET existenciaP = existenciaP - $cantidad WHERE idP = $idP AND existenciaP >= $cantidad";
    mysqli_query($conexion, $sqlStock);
}

// Clear carrito.xml
file_put_contents($carrito_path, '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<carrito/>' . "\n");

unset($_SESSION['pedido_data']);

echo json_encode(array('success' => true, 'idOrden' => $idOrden));
?>
