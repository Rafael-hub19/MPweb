<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Productos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Ícono de carrito usando la librereía Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="d-flex flex-column min-vh-100" style="background-color: #fff8f0;">

    <!-- Navbar -->
    <nav class="navbar navbar-dark sticky-top" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <div class="container-fluid">
            <!-- Botón de retroceso -->
            <a href="index.html" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Inicio
            </a>
            <span class="navbar-brand mx-auto fw-bold">
                <i class="fas fa-candy-cane me-2"></i>Dulcería "La Patita"
            </span>
            <!-- Ícono de carrito que enlaza a carrito.php -->
            <a href="carrito.php" class="btn btn-outline-light btn-sm" title="Ver productos en el carrito">
                <i class="fas fa-shopping-cart me-1"></i>
                <!-- La i en Font Awesome se usa para insertar íconos -->
                <!-- fa-shopping-cart es el nombre en especifico del ícono de carrito -->
                Carrito
            </a>
        </div>
    </nav>

    <!-- Encabezado principal -->
    <header class="text-center text-white py-4" style="background: linear-gradient(135deg, #c0392b, #922b21);">
        <h1 class="fw-bold mb-0"><i class="fas fa-store me-2"></i>Nuestros Productos</h1>
        <p class="mb-0 opacity-75">Selecciona un producto para agregarlo al carrito</p>
    </header>

    <!-- Sección donde se mostrará la galería -->
    <main class="container py-4 flex-grow-1">
        <div class="row g-4 justify-content-center">
        <?php
        // Conexión a la base de datos MySQL
        $conexion = mysqli_connect("localhost", "mueblesweb", "429afaa4d", "mueblesweb");
        // Consulta para obtener productos con existencia mayor a 5
        $Resultado = mysqli_query($conexion,"SELECT * FROM `dulceria` WHERE `existencia`>5;");
        // Bucle para recorrer cada producto y mostrarlo en la galería
        while ($row = mysqli_fetch_array($Resultado)) {
            // Contenedor individual del producto — 1 col en xs, 2 en sm, 3 en md, 4 en lg
            echo '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
            echo '<div class="card h-100 shadow-sm border-0 producto-card">';
            // Wrapper de imagen: fondo neutro, imagen completa sin recortar
            echo '<a href="agregar_json.php?id_producto=' . $row['id_producto'] . '" class="text-decoration-none">';
            echo '<div class="producto-img-wrap">';
            echo '<img src="img/' . $row['imagen'] . '" class="producto-img" alt="' . $row['nombre'] . '">';
            echo '</div>';
            echo '</a>';
            echo '<div class="card-body d-flex flex-column text-center px-3 py-2">';
            // Nombre del producto
            echo '<h6 class="card-title fw-bold mb-1 lh-sm">';
            echo '<a href="agregar_json.php?id_producto=' . $row['id_producto'] . '" class="text-dark text-decoration-none nombre-producto">' . $row['nombre'] . '</a>';
            echo '</h6>';
            echo '<p class="text-muted small mb-2">' . $row['marca'] . '</p>';  // Marca del producto
            echo '<p class="fw-bold mt-auto mb-0 fs-6" style="color:#c0392b;">$' . number_format($row['precio'], 2) . ' <span class="text-muted fw-normal" style="font-size:0.75rem;">MXN</span></p>';  // Precio del producto
            echo '</div>';
            echo '<div class="card-footer bg-transparent border-0 text-center pb-3 pt-1">';
            echo '<a href="agregar_json.php?id_producto=' . $row['id_producto'] . '" class="btn btn-sm w-100 text-white fw-semibold" style="background:linear-gradient(135deg,#ff6f61,#c0392b);">';
            echo '<i class="fas fa-cart-plus me-1"></i> Agregar al carrito</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        // Cierre de la conexión
        mysqli_close($conexion);
        ?>
        </div>

        <?php
        $nombreArchivo = date("F_j_Y")."_Pedido.json";//En lugar de pedido se sugiere el nombre del usuario
        if(file_exists($nombreArchivo)){
            echo '<div class="text-center mt-4">';
            echo '<a href="carrito.php" class="btn btn-success px-4"><i class="fas fa-receipt me-2"></i>Ver pedido del día</a>';
            echo '</div>';
        }
        ?>
    </main>

    <!-- Pie de página -->
    <footer class="text-center py-3 text-white mt-auto" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <small><i class="fas fa-copyright me-1"></i>2025 Patricia Torres | Dulcería La Patita</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
