<?php
if ($_POST) {
    $nombre   = $_POST['nombre'];
    $marca    = $_POST['marca'];
    $precio   = $_POST['precio'];
    $cantidad = $_POST['cantidad'];
    $subTotal = $precio * $cantidad;
    $fechaHora = date("F_j_Y");
    $ruta = $fechaHora . "_pedido.json";
    $pedido = array();

    if (file_exists($ruta)) {
        // Leer el archivo JSON existente
        $archivo = file_get_contents($ruta);
        $pedido = json_decode($archivo, true);
        $encontrado = false;
        // Buscar si el producto ya existe
        foreach ($pedido as &$item) {
            if ($item['nombre'] === $nombre && $item['marca'] === $marca) {
                $item['cantidad'] += $cantidad;
                $item['subTotal'] += $subTotal;
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $pedido[] = array(
                'nombre'   => $nombre,
                'marca'    => $marca,
                'precio'   => $precio,
                'cantidad' => $cantidad,
                'subTotal' => $subTotal
            );
        }
        file_put_contents($ruta, json_encode($pedido));
    } else {
        // Si el archivo no existe, crear uno nuevo
        $pedido[] = array(
            'nombre'   => $nombre,
            'marca'    => $marca,
            'precio'   => $precio,
            'cantidad' => $cantidad,
            'subTotal' => $subTotal
        );
        file_put_contents($ruta, json_encode($pedido));
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producto agregado</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#fff8f0;">

    <!-- Navbar -->
    <nav class="navbar navbar-dark" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <div class="container-fluid">
            <a href="galeria_dinamica.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Galería
            </a>
            <span class="navbar-brand mx-auto fw-bold">
                <i class="fas fa-candy-cane me-2"></i>Dulcería "La Patita"
            </span>
            <a href="carrito.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-shopping-cart me-1"></i> Carrito
            </a>
        </div>
    </nav>

    <!-- Confirmación centrada -->
    <main class="flex-grow-1 d-flex align-items-center justify-content-center px-3 py-5">
        <div class="card shadow-lg border-0 w-100" style="max-width:460px; border-radius:20px; overflow:hidden;">

            <!-- Encabezado verde -->
            <div class="text-center text-white py-4 px-3" style="background:linear-gradient(135deg,#27ae60,#1e8449);">
                <i class="fas fa-check-circle fa-4x mb-2"></i>
                <h4 class="fw-bold mb-0">¡Producto agregado!</h4>
            </div>

            <!-- Detalle del producto -->
            <div class="card-body px-4 py-3">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted small fw-semibold">Producto</td>
                        <td class="fw-bold text-end"><?php echo htmlspecialchars($nombre); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small fw-semibold">Marca</td>
                        <td class="text-end"><?php echo htmlspecialchars($marca); ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted small fw-semibold">Precio unitario</td>
                        <td class="text-end">$<?php echo number_format($precio, 2); ?> MXN</td>
                    </tr>
                    <tr>
                        <td class="text-muted small fw-semibold">Cantidad</td>
                        <td class="text-end"><?php echo $cantidad; ?></td>
                    </tr>
                    <tr class="border-top">
                        <td class="fw-bold">Subtotal</td>
                        <td class="fw-bold text-end fs-5" style="color:#c0392b;">
                            $<?php echo number_format($subTotal, 2); ?> MXN
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Botones -->
            <div class="card-footer bg-white border-0 px-4 pb-4 pt-1">
                <div class="d-grid gap-2">
                    <a href="carrito.php" class="btn btn-lg fw-semibold text-white"
                       style="background:linear-gradient(135deg,#ff6f61,#c0392b); border-radius:12px;">
                        <i class="fas fa-shopping-cart me-2"></i> Ver carrito
                    </a>
                    <a href="galeria_dinamica.php" class="btn btn-lg btn-outline-secondary fw-semibold"
                       style="border-radius:12px;">
                        <i class="fas fa-store me-2"></i> Seguir comprando
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center py-3 text-white" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <small><i class="fas fa-copyright me-1"></i>2025 Patricia Torres | Dulcería La Patita</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
?>
