<?php
/* paypal_create_order.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */

/* ── Proteccion ante errores fatales y output espurio ── */
ob_start();
error_reporting(0);
ini_set('display_errors', '0');

function _co_shutdown() {
    $e = error_get_last();
    if ($e !== null && in_array($e['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
        ob_end_clean();
        if (!headers_sent()) {
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/json');
        }
        echo json_encode(array('error' => 'Error del servidor: ' . $e['message']));
    } else {
        ob_end_flush();
    }
}
register_shutdown_function('_co_shutdown');

/* ── Inicio ─────────────────────────────────────────── */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    ob_end_clean();
    echo json_encode(array('error' => 'No autorizado. Inicia sesion para continuar.'));
    exit;
}

/* ── Leer cuerpo JSON ───────────────────────────────── */
$_input = file_get_contents('php://input');
$body   = json_decode($_input, true);

$nombre   = (is_array($body) && isset($body['nombre']))   ? trim($body['nombre'])   : '';
$apellido = (is_array($body) && isset($body['apellido'])) ? trim($body['apellido']) : '';
$telefono = (is_array($body) && isset($body['telefono'])) ? trim($body['telefono']) : '';
$email    = (is_array($body) && isset($body['email']))    ? trim($body['email'])    : '';
$notas    = (is_array($body) && isset($body['notas']))    ? trim($body['notas'])    : '';

if ($nombre === '' || $apellido === '' || $telefono === '' || $email === '') {
    ob_end_clean();
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

/* ── Dependencias ───────────────────────────────────── */
include 'paypal_config.php';
include 'conectbd.php';

/* ── Leer carrito.json ──────────────────────────────── */
$carrito_path = __DIR__ . '/carrito.json';

if (!file_exists($carrito_path)) {
    ob_end_clean();
    echo json_encode(array('error' => 'El carrito esta vacio.'));
    exit;
}

$carrito = json_decode(file_get_contents($carrito_path), true);

if (!is_array($carrito) || count($carrito) == 0) {
    ob_end_clean();
    echo json_encode(array('error' => 'El carrito esta vacio.'));
    exit;
}

$total = 0.0;
foreach ($carrito as $item) {
    $total += (float)$item['precio'] * (int)$item['cantidad'];
}

if ($total <= 0) {
    ob_end_clean();
    echo json_encode(array('error' => 'El total del carrito es invalido.'));
    exit;
}

/* ── Crear orden en PayPal ──────────────────────────── */
$order = paypal_create_order($total);

ob_end_clean();

if (!$order || !isset($order['id'])) {
    $msg = (is_array($order) && isset($order['message'])) ? $order['message'] : 'No se pudo crear la orden en PayPal. Verifica las credenciales en .env';
    echo json_encode(array('error' => $msg));
    exit;
}

echo json_encode(array('id' => $order['id']));
?>
