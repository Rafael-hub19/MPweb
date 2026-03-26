<?php
/* paypal_capture_order.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */

/* ── Proteccion ante errores fatales y output espurio ── */
ob_start();
error_reporting(0);
ini_set('display_errors', '0');

function _cap_shutdown() {
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
register_shutdown_function('_cap_shutdown');

/* ── Inicio ─────────────────────────────────────────── */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    ob_end_clean();
    echo json_encode(array('error' => 'No autorizado. Inicia sesion para continuar.'));
    exit;
}

/* ── Leer cuerpo JSON ───────────────────────────────── */
$_input  = file_get_contents('php://input');
$body    = json_decode($_input, true);
$orderID = (is_array($body) && isset($body['orderID'])) ? $body['orderID'] : null;

if (!$orderID) {
    ob_end_clean();
    echo json_encode(array('error' => 'orderID no proporcionado.'));
    exit;
}

/* ── Dependencias ───────────────────────────────────── */
include 'paypal_config.php';
include 'conectbd.php';

/* ── Capturar orden en PayPal ───────────────────────── */
$capture = paypal_capture_order($orderID);

if (!$capture) {
    ob_end_clean();
    echo json_encode(array('error' => 'No se pudo conectar con PayPal para capturar la orden.'));
    exit;
}

$status = isset($capture['status']) ? $capture['status'] : '';

if ($status !== 'COMPLETED') {
    ob_end_clean();
    $msg = isset($capture['message']) ? $capture['message'] : 'Captura no completada. Estado: ' . $status;
    echo json_encode(array('error' => $msg));
    exit;
}

/* ── Pago completado: guardar en BD ─────────────────── */
if (!isset($_SESSION['pedido_data'])) {
    ob_end_clean();
    echo json_encode(array('error' => 'Datos del pedido no encontrados en sesion.'));
    exit;
}

$pedido   = $_SESSION['pedido_data'];
$nombre   = isset($pedido['nombre'])   ? $pedido['nombre']   : '';
$apellido = isset($pedido['apellido']) ? $pedido['apellido'] : '';
$telefono = isset($pedido['telefono']) ? $pedido['telefono'] : '';
$email    = isset($pedido['email'])    ? $pedido['email']    : '';
$notas    = isset($pedido['notas'])    ? $pedido['notas']    : '';

/* ── Leer carrito.xml ───────────────────────────────── */
$carrito_path = __DIR__ . '/carrito.xml';
$items = array();
$total = 0.0;

if (file_exists($carrito_path)) {
    $xml = simplexml_load_file($carrito_path);
    if ($xml !== false && count($xml->item) > 0) {
        foreach ($xml->item as $item) {
            $idP      = (int)$item->idP;
            $cantidad = (int)$item->cantidad;
            $precio   = (float)$item->precio;
            if ($idP > 0 && $cantidad > 0) {
                $items[] = array(
                    'idP'      => $idP,
                    'nombre'   => (string)$item->nombre,
                    'precio'   => $precio,
                    'cantidad' => $cantidad,
                );
                $total += $precio * $cantidad;
            }
        }
    }
}

if ($total <= 0) {
    ob_end_clean();
    echo json_encode(array('error' => 'El total del pedido es invalido.'));
    exit;
}

/* ── Obtener idUsuario ──────────────────────────────── */
$usuarioEsc = mysqli_real_escape_string($conexion, $_SESSION['usuario']);
$resU       = mysqli_query($conexion, "SELECT idU FROM usuarios WHERE usuarioU = '$usuarioEsc'");
$rowU       = $resU ? mysqli_fetch_assoc($resU) : null;

if (!$rowU) {
    ob_end_clean();
    echo json_encode(array('error' => 'Usuario no encontrado en la base de datos.'));
    exit;
}
$idUsuario = (int)$rowU['idU'];

/* ── INSERT en ordenes ──────────────────────────────── */
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
$idOrden = (int)mysqli_insert_id($conexion);

/* ── INSERT en orden_detalle + UPDATE stock ─────────── */
foreach ($items as $item) {
    $idP      = (int)$item['idP'];
    $nombreP  = mysqli_real_escape_string($conexion, $item['nombre']);
    $precioP  = (float)$item['precio'];
    $cantidad = (int)$item['cantidad'];

    mysqli_query($conexion,
        "INSERT INTO orden_detalle (idOrden, idP, nombreP, precioP, cantidad)
         VALUES ($idOrden, $idP, '$nombreP', $precioP, $cantidad)"
    );
    mysqli_query($conexion,
        "UPDATE productos SET existenciaP = existenciaP - $cantidad
         WHERE idP = $idP AND existenciaP >= $cantidad"
    );
}

/* ── Vaciar carrito y limpiar sesion ────────────────── */
file_put_contents($carrito_path,
    '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<carrito/>' . "\n"
);
unset($_SESSION['pedido_data']);

ob_end_clean();
echo json_encode(array('success' => true, 'idOrden' => $idOrden));
?>
