<?php
session_start();
header('Content-Type: application/json');

// Require login
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado. Inicia sesion para continuar.']);
    exit;
}

include 'conectbd.php';
include 'paypal_config.php';

// Read carrito.xml and calculate total
$carrito_path = __DIR__ . '/carrito.xml';

if (!file_exists($carrito_path)) {
    echo json_encode(['error' => 'El carrito esta vacio.']);
    exit;
}

$xml = simplexml_load_file($carrito_path);

if ($xml === false || !isset($xml->item)) {
    echo json_encode(['error' => 'El carrito esta vacio.']);
    exit;
}

$total = 0.0;
foreach ($xml->item as $item) {
    $precio   = (float)$item->precio;
    $cantidad = (int)$item->cantidad;
    $total   += $precio * $cantidad;
}

if ($total <= 0) {
    echo json_encode(['error' => 'El total del carrito es invalido.']);
    exit;
}

// Create PayPal order
$order = paypal_create_order($total);

if (!$order || !isset($order['id'])) {
    $msg = isset($order['message']) ? $order['message'] : 'No se pudo crear la orden en PayPal.';
    echo json_encode(['error' => $msg]);
    exit;
}

echo json_encode(['id' => $order['id']]);
?>
