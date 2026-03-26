<?php
session_start();
header('Content-Type: application/json');

// Require login
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'No autorizado. Inicia sesion para continuar.']);
    exit;
}

include 'paypal_config.php';
include 'conectbd.php';

// Read JSON body
$_input  = file_get_contents('php://input');
$body    = json_decode($_input, true);
$orderID = isset($body['orderID']) ? $body['orderID'] : null;

if (!$orderID) {
    echo json_encode(['error' => 'orderID no proporcionado.']);
    exit;
}

// Capture the PayPal order
$capture = paypal_capture_order($orderID);

if (!$capture) {
    echo json_encode(['error' => 'No se pudo conectar con PayPal para capturar la orden.']);
    exit;
}

$status = isset($capture['status']) ? $capture['status'] : '';

if ($status !== 'COMPLETED') {
    $msg = isset($capture['message']) ? $capture['message'] : 'La captura no fue completada. Estado: ' . $status;
    echo json_encode(['error' => $msg]);
    exit;
}

// Payment completed — update DB stock and clear cart
$carrito_path = __DIR__ . '/carrito.xml';

if (file_exists($carrito_path)) {
    $xml = simplexml_load_file($carrito_path);

    if ($xml !== false && isset($xml->item)) {
        foreach ($xml->item as $item) {
            $idP      = (int)$item->idP;
            $cantidad = (int)$item->cantidad;

            if ($idP > 0 && $cantidad > 0) {
                $sql = "UPDATE productos SET existenciaP = existenciaP - $cantidad WHERE idP = $idP AND existenciaP >= $cantidad";
                mysqli_query($conexion, $sql);
            }
        }
    }

    // Clear cart
    file_put_contents($carrito_path, '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<carrito/>' . "\n");
}

echo json_encode(['success' => true]);
?>
