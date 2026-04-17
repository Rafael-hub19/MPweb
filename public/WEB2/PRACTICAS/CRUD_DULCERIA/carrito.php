<?php
// Genera el nombre del archivo JSON con la fecha actual
$fechaHora = date("F_j_Y");
$ruta = $fechaHora . "_pedido.json";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: #fff8f0;">

    <!-- Navbar -->
    <nav class="navbar navbar-dark sticky-top" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <div class="container-fluid">
            <a href="galeria_dinamica.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Galería
            </a>
            <span class="navbar-brand mx-auto fw-bold">
                <i class="fas fa-candy-cane me-2"></i>Dulcería "La Patita"
            </span>
            <span style="width:90px;"></span>
        </div>
    </nav>

    <!-- Encabezado -->
    <header class="text-center text-white py-4" style="background: linear-gradient(135deg, #c0392b, #922b21);">
        <h1 class="fw-bold mb-0"><i class="fas fa-shopping-cart me-2"></i>Carrito de compras</h1>
    </header>

    <!-- Contenido -->
    <main class="container py-4 flex-grow-1">
<?php
if (file_exists($ruta)) {
    $archivo = file_get_contents($ruta);
    $pedido  = json_decode($archivo, true);

    if (!empty($pedido)) {
        $total = 0;

        echo '<div class="table-responsive shadow-sm rounded mb-4">';
        echo '<table class="table table-hover align-middle mb-0 bg-white">';
        echo '<thead class="text-white" style="background:linear-gradient(135deg,#ff6f61,#c0392b);">';
        echo '<tr>
                <th>Producto</th>
                <th class="d-none d-sm-table-cell">Marca</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th colspan="2" class="text-center">Acciones</th>
              </tr>';
        echo '</thead><tbody>';

        foreach ($pedido as $index => $item) {
            echo "<tr>
                <form method='POST' action='actualizar_json.php'>
                    <td class='fw-semibold'>
                        {$item['nombre']}
                        <input type='hidden' name='index' value='$index'>
                    </td>
                    <td class='text-muted small d-none d-sm-table-cell'>{$item['marca']}</td>
                    <td>\$" . number_format($item['precio'], 2) . "</td>
                    <td style='width:100px;'>
                        <input type='number' name='cantidad' value='{$item['cantidad']}' min='1' class='form-control form-control-sm text-center'>
                    </td>
                    <td class='fw-bold' style='color:#c0392b;'>\$" . number_format($item['subTotal'], 2) . "</td>
                    <td>
                        <button type='submit' class='btn btn-sm btn-success w-100'>
                            <i class='fas fa-sync-alt'></i><span class='d-none d-md-inline ms-1'>Actualizar</span>
                        </button>
                    </td>
                </form>
                <form method='POST' action='eliminar_json.php'>
                    <input type='hidden' name='index' value='$index'>
                    <td>
                        <button type='submit' class='btn btn-sm btn-danger w-100'>
                            <i class='fas fa-trash'></i><span class='d-none d-md-inline ms-1'>Eliminar</span>
                        </button>
                    </td>
                </form>
            </tr>";
            $total += $item['subTotal'];
        }

        echo "<tr class='table-light'>
                <td colspan='4' class='text-end fw-bold fs-5'>Total:</td>
                <td colspan='3' class='fw-bold fs-5' style='color:#c0392b;'>
                    \$" . number_format($total, 2) . " MXN
                </td>
              </tr>";
        echo '</tbody></table></div>';

        // Resumen y botón de pagar
        echo '<div class="row justify-content-end">';
        echo '<div class="col-12 col-md-5">';
        echo '<div class="card border-0 shadow-sm" style="border-radius:16px;">';
        echo '<div class="card-body px-4 py-3">';
        echo '<h6 class="fw-bold mb-3 text-muted">RESUMEN DEL PEDIDO</h6>';
        echo '<div class="d-flex justify-content-between mb-1"><span class="text-muted">Productos</span><span>' . count($pedido) . '</span></div>';
        echo '<div class="d-flex justify-content-between mb-3"><span class="text-muted">Total de piezas</span><span>' . array_sum(array_column($pedido, 'cantidad')) . '</span></div>';
        echo '<hr>';
        echo '<div class="d-flex justify-content-between fw-bold fs-5 mb-3">';
        echo '<span>Total</span><span style="color:#c0392b;">$' . number_format($total, 2) . ' MXN</span>';
        echo '</div>';
        echo '<form method="POST" action="pagar.php">';
        echo '<button type="submit" class="btn btn-lg w-100 text-white fw-bold" style="background:linear-gradient(135deg,#27ae60,#1e8449); border-radius:12px;">';
        echo '<i class="fas fa-lock me-2"></i>Pagar ahora</button>';
        echo '</form>';
        echo '</div></div>';
        echo '</div></div>';

    } else {
        echo '<div class="alert alert-warning text-center py-5">
                <i class="fas fa-shopping-cart fa-3x mb-3 d-block opacity-50"></i>
                <h5 class="fw-bold">El carrito está vacío</h5>
                <p class="text-muted mb-0">Agrega productos desde la galería.</p>
              </div>';
    }
} else {
    echo '<div class="alert alert-info text-center py-5">
            <i class="fas fa-info-circle fa-3x mb-3 d-block opacity-50"></i>
            <h5 class="fw-bold">No hay pedido para hoy</h5>
            <p class="text-muted mb-0">Aún no has agregado ningún producto al carrito.</p>
          </div>';
}
?>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 mt-4">
            <a href="galeria_dinamica.php" class="btn btn-outline-secondary px-4 fw-semibold" style="border-radius:50px;">
                <i class="fas fa-store me-2"></i> Seguir comprando
            </a>
            <a href="leer_json.php" class="btn btn-outline-dark px-4 fw-semibold" style="border-radius:50px;">
                <i class="fas fa-code me-2"></i> Ver JSON
            </a>
        </div>
    </main>

    <footer class="text-center py-3 text-white mt-auto" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <small><i class="fas fa-copyright me-1"></i>2025 Patricia Torres | Dulcería La Patita</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
