<?php
// Genera el nombre del archivo JSON con la fecha actual
$fechaHora = date("F_j_Y");
$ruta = $fechaHora . "_pedido.json";

// Verifica si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene el índice del producto y la nueva cantidad
    $index = $_POST['index'];
    $nuevaCantidad = intval($_POST['cantidad']);

    // Verifica si el archivo existe
    if (file_exists($ruta)) {
        // Lee y decodifica el archivo JSON
        $archivo = file_get_contents($ruta);
        $pedido = json_decode($archivo, true);

        // Actualiza la cantidad y el subtotal del producto
        $pedido[$index]['cantidad'] = $nuevaCantidad;
        $pedido[$index]['subTotal'] = $pedido[$index]['precio'] * $nuevaCantidad;

        // Guarda los cambios en el archivo
        file_put_contents($ruta, json_encode($pedido, JSON_PRETTY_PRINT));
    }

    // Redirige de vuelta al carrito igual a header("Location: carrito.php");
    echo '<meta http-equiv="refresh" content="0;url=carrito.php">';
    exit;
}
?>
