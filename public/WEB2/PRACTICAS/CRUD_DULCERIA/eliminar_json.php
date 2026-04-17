<?php
// Genera el nombre del archivo JSON con la fecha actual
$fechaHora = date("F_j_Y");
$ruta = $fechaHora . "_pedido.json";

// Verifica si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene el índice del producto a eliminar
    $index = $_POST['index'];

    // Verifica si el archivo existe
    if (file_exists($ruta)) {
        // Lee y decodifica el archivo JSON
        $archivo = file_get_contents($ruta);
        $pedido = json_decode($archivo, true);

        // Elimina el producto del arreglo
        array_splice($pedido, $index, 1);

        // Guarda los cambios en el archivo
        file_put_contents($ruta, json_encode($pedido, JSON_PRETTY_PRINT));
    }

    // Redirige de vuelta al carrito igual que header("Location: carrito.php");
    echo '<meta http-equiv="refresh" content="0;url=carrito.php">';
    exit;
}
?>
