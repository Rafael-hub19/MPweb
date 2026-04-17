<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar al Carrito</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Ícono de carrito usando la librereía Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: #fff8f0;">

    <!-- Navbar -->
    <nav class="navbar navbar-dark sticky-top" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <div class="container-fluid">
            <!-- Ícono de carrito que enlaza a ver_pedido.php -->
            <!-- Botón de retroceso -->
            <a href="galeria_dinamica.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Galería
            </a>
            <span class="navbar-brand mx-auto fw-bold">
                <i class="fas fa-candy-cane me-2"></i>Dulcería "La Patita"
            </span>
            <a href="carrito.php" class="btn btn-outline-light btn-sm" title="Ver productos en el carrito">
                <i class="fas fa-shopping-cart me-1"></i>
                <!-- La i en Font Awesome se usa para insertar íconos -->
                <!-- fa-shopping-cart es el nombre en especifico del ícono de carrito -->
                Carrito
            </a>
        </div>
    </nav>

    <!-- Encabezado -->
    <header class="text-center text-white py-4" style="background: linear-gradient(135deg, #c0392b, #922b21);">
        <h1 class="fw-bold mb-0"><i class="fas fa-cart-plus me-2"></i>Agregar al Carrito</h1>
    </header>

    <!-- Contenido -->
    <main class="container py-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5">
        <?php
        if (isset($_GET['id_producto'])) {
            $id = $_GET['id_producto'];
            $conexion = mysqli_connect("localhost", "mueblesweb", "429afaa4d", "mueblesweb");
            if (!$conexion) {
                die('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error de conexión: ' . mysqli_connect_error() . '</div>');
            }
            $Resultado = mysqli_query($conexion, "SELECT * FROM `dulceria` WHERE `id_producto`='" . $id . "';");
            if (!$Resultado) {
                die('<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error en la consulta: ' . mysqli_error($conexion) . '</div>');
            }
            while ($row = mysqli_fetch_array($Resultado)) {
                echo '<div class="card shadow border-0">';
                // Imagen del producto
                echo '<img src="img/' . $row['imagen'] . '" class="card-img-top" alt="' . $row['nombre'] . '" style="max-height:260px; object-fit:contain; padding:1.5rem; background:#f9f9f9;">';
                echo '<div class="card-body">';
                echo '<form action="acumular_json.php" method="post" name="AgregarCarrito">';

                echo '<div class="mb-3">';
                echo '<label class="form-label fw-semibold text-secondary small">Nombre del producto</label>';
                echo '<input type="text" class="form-control" name="nombre" value="' . $row['nombre'] . '" readonly>';
                echo '</div>';

                echo '<div class="mb-3">';
                echo '<label class="form-label fw-semibold text-secondary small">Marca</label>';
                echo '<input type="text" class="form-control" name="marca" value="' . $row['marca'] . '" readonly>';
                echo '</div>';

                echo '<div class="mb-3">';
                echo '<label class="form-label fw-semibold text-secondary small">Precio</label>';
                echo '<div class="input-group">';
                echo '<span class="input-group-text">$</span>';
                echo '<input type="number" class="form-control" name="precio" value=' . $row['precio'] . ' readonly>';
                echo '<span class="input-group-text">MXN</span>';
                echo '</div>';
                echo '</div>';

                echo '<div class="mb-4">';
                echo '<label class="form-label fw-semibold text-secondary small">Cantidad a agregar</label>';
                echo '<input type="number" class="form-control" name="cantidad" min="1" max="10" placeholder="Ej. 2" required>';
                echo '</div>';

                echo '<div class="d-grid gap-2">';
                echo '<input type="submit" id="btnagregar" class="btn btn-lg text-white fw-semibold" style="background:linear-gradient(135deg,#ff6f61,#c0392b);" value="🛒 Agregar al carrito">';
                echo '<a href="galeria_dinamica.php" class="btn btn-outline-secondary">Cancelar</a>';
                echo '</div>';

                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
            mysqli_close($conexion);
        } else {
            echo '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>No se especificó un producto.</div>';
        }
        ?>
            </div>
        </div>
    </main>

    <!-- Pie de página -->
    <footer class="text-center py-3 text-white mt-auto" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <small><i class="fas fa-copyright me-1"></i>2025 Patricia Torres | Dulcería La Patita</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
