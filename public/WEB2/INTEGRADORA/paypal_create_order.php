<?php
/* paypal_create_order.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(array('error' => 'No autorizado. Inicia sesion para continuar.'));
    exit;
}

$_input = file_get_contents('php://input');
$body   = json_decode($_input, true);

$nombre   = isset($body['nombre'])   ? trim($body['nombre'])   : '';
$apellido = isset($body['apellido']) ? trim($body['apellido']) : '';
$telefono = isset($body['telefono']) ? trim($body['telefono']) : '';
$email    = isset($body['email'])    ? trim($body['email'])    : '';
$notas    = isset($body['notas'])    ? trim($body['notas'])    : '';

if ($nombre === '' || $apellido === '' || $telefono === '' || $email === '') {
    echo json_encode(array('error' => 'Nombre, apellido, telefono y email son obligatorios.'));
    exit;
}

$_SESSION['pedido_data'] = array(
    'nombre'   => $nombre,
    'apellido' => $apellido,
    'telefono' => $telefono,
    'email'    => $email,
    'notas'    => $notas,
);

include 'paypal_config.php';
include 'conectbd.php';

$carrito_path = __DIR__ . '/carrito.xml';

if (!file_exists($carrito_path)) {
    echo json_encode(array('error' => 'El carrito esta vacio.'));
    exit;
}

$xml = simplexml_load_file($carrito_path);

if ($xml === false || !isset($xml->item)) {
    echo json_encode(array('error' => 'El carrito esta vacio.'));
    exit;
}

$total = 0.0;
foreach ($xml->item as $item) {
    $precio   = (float)$item->precio;
    $cantidad = (int)$item->cantidad;
    $total   += $precio * $cantidad;
}

if ($total <= 0) {
    echo json_encode(array('error' => 'El total del carrito es invalido.'));
    exit;
}

$order = paypal_create_order($total);

if (!$order || !isset($order['id'])) {
    $msg = isset($order['message']) ? $order['message'] : 'No se pudo crear la orden en PayPal.';
    echo json_encode(array('error' => $msg));
    exit;
}

echo json_encode(array('id' => $order['id']));
?>
