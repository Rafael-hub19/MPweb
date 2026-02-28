<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD XML - Jugueteria | Programacion Web 2</title>
    <link rel="stylesheet" href="../../../assets/css/xml.css">
    <link rel="stylesheet" href="../../../assets/css/styles.css">
    <link rel="stylesheet" href="../../../assets/css/_variables.css">
</head>
<body>

<a href="../../web2.html" class="back-button">&larr; Regresar a WEB 2</a>

<div class="logo-container">
    <img src="../../../assets/img/logo-ceti.png" alt="Logo CETI" class="logo-img">
</div>

<?php
/* =============================================
   CRUD con XML - PHP 5.5 compatible
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8 Semestre
   ============================================= */

$xmlFile = 'jugueteria.xml';
$mensaje = '';
$tipo    = '';

/* ---------- CREATE ---------- */
if (isset($_POST['action']) && $_POST['action'] === 'crear') {
    $id       = trim($_POST['id']);
    $nombre   = trim($_POST['nombre']);
    $precio   = trim($_POST['precio']);
    $cantidad = trim($_POST['cantidad']);

    if ($id !== '' && $nombre !== '' && $precio !== '' && $cantidad !== '') {
        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
        } else {
            $xml = new SimpleXMLElement('<productos/>');
        }

        $existe = false;
        foreach ($xml->producto as $p) {
            if ((string)$p['id'] === (string)$id) {
                $existe = true;
                break;
            }
        }

        if ($existe) {
            $mensaje = 'Ya existe un producto con ID ' . htmlspecialchars($id) . '.';
            $tipo    = 'error';
        } else {
            $producto = $xml->addChild('producto');
            $producto->addAttribute('id', $id);
            $producto->addChild('nombre',   htmlspecialchars($nombre));
            $producto->addChild('precio',   $precio);
            $producto->addChild('cantidad', $cantidad);
            $xml->asXML($xmlFile);
            $mensaje = 'Producto "' . htmlspecialchars($nombre) . '" creado correctamente.';
            $tipo    = 'success';
        }
    } else {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo    = 'error';
    }
}

/* ---------- UPDATE ---------- */
if (isset($_POST['action']) && $_POST['action'] === 'actualizar') {
    $id             = trim($_POST['id']);
    $nuevo_nombre   = trim($_POST['nombre']);
    $nuevo_precio   = trim($_POST['precio']);
    $nuevo_cantidad = trim($_POST['cantidad']);

    if ($id !== '' && file_exists($xmlFile)) {
        $xml        = simplexml_load_file($xmlFile);
        $encontrado = false;

        foreach ($xml->producto as $producto) {
            if ((string)$producto['id'] === (string)$id) {
                if ($nuevo_nombre !== '')   { $producto->nombre   = $nuevo_nombre;   }
                if ($nuevo_precio !== '')   { $producto->precio   = $nuevo_precio;   }
                if ($nuevo_cantidad !== '') { $producto->cantidad = $nuevo_cantidad; }
                $encontrado = true;
                break;
            }
        }

        if ($encontrado) {
            $xml->asXML($xmlFile);
            $mensaje = 'Producto con ID ' . htmlspecialchars($id) . ' actualizado correctamente.';
            $tipo    = 'success';
        } else {
            $mensaje = 'No se encontro ningun producto con ID ' . htmlspecialchars($id) . '.';
            $tipo    = 'error';
        }
    } else {
        $mensaje = 'Ingresa el ID del producto a actualizar.';
        $tipo    = 'error';
    }
}

/* ---------- DELETE ---------- */
if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id = trim($_POST['id']);

    if ($id !== '' && file_exists($xmlFile)) {
        $xml   = simplexml_load_file($xmlFile);
        $index = 0;
        $found = false;

        foreach ($xml->producto as $producto) {
            if ((string)$producto['id'] === (string)$id) {
                unset($xml->producto[$index]);
                $found = true;
                break;
            }
            $index++;
        }

        if ($found) {
            $xml->asXML($xmlFile);
            $mensaje = 'Producto con ID ' . htmlspecialchars($id) . ' eliminado correctamente.';
            $tipo    = 'success';
        } else {
            $mensaje = 'Producto con ID ' . htmlspecialchars($id) . ' no encontrado.';
            $tipo    = 'error';
        }
    } else {
        $mensaje = 'Ingresa el ID del producto a eliminar.';
        $tipo    = 'error';
    }
}

/* ---------- READ ---------- */
$productos = array();
if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    foreach ($xml->producto as $p) {
        $productos[] = array(
            'id'       => (string)$p['id'],
            'nombre'   => (string)$p->nombre,
            'precio'   => (string)$p->precio,
            'cantidad' => (string)$p->cantidad
        );
    }
}

$totalProductos = count($productos);
$xmlRaw         = file_exists($xmlFile) ? file_get_contents($xmlFile) : '';
?>

<div class="container">

    <!-- TITULO -->
    <h1>CRUD con <span>XML</span></h1>
    <p class="subtitle">Programacion Web 2 &mdash; Mtra. Patricia Torres &nbsp;|&nbsp; Rafael Avila &middot; CETI</p>
    <div class="divider"></div>

    <!-- ALERTA -->
    <?php if ($mensaje !== '') { ?>
    <div class="alert alert-<?php echo $tipo; ?>">
        <?php echo $mensaje; ?>
    </div>
    <?php } ?>

    <!-- GRID: formularios | tabla -->
    <div class="crud-grid">

        <!-- COLUMNA IZQUIERDA: Formularios -->
        <div class="panel">
            <h2>Operaciones</h2>
            <span class="panel-sub">Gestion de productos &mdash; jugueteria.xml</span>
            <div class="panel-divider"></div>

            <div class="info-box">
                <p>
                    Selecciona una operacion <strong>CRUD</strong>:
                    Create &middot; Read &middot; Update &middot; Delete.<br>
                    Los datos se guardan en el archivo <strong>jugueteria.xml</strong>.
                </p>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('crear', this)">+ Crear</button>
                <button class="tab"        onclick="switchTab('actualizar', this)">Editar</button>
                <button class="tab"        onclick="switchTab('eliminar', this)">Eliminar</button>
            </div>

            <!-- CREAR -->
            <div id="tab-crear" class="form-panel active">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="crear">
                    <div class="form-group">
                        <label for="c-id">ID del Producto</label>
                        <input type="number" id="c-id" name="id" placeholder="Ej: 3" required>
                    </div>
                    <div class="form-group">
                        <label for="c-nombre">Nombre del Producto</label>
                        <input type="text" id="c-nombre" name="nombre" placeholder="Ej: Yoyo" required>
                    </div>
                    <div class="form-group">
                        <label for="c-precio">Precio</label>
                        <input type="number" step="0.01" id="c-precio" name="precio" placeholder="Ej: 49.99" required>
                    </div>
                    <div class="form-group">
                        <label for="c-cantidad">Cantidad</label>
                        <input type="number" id="c-cantidad" name="cantidad" placeholder="Ej: 10" required>
                    </div>
                    <button type="submit" class="btn-crud btn-create">Crear Producto</button>
                </form>
            </div>

            <!-- ACTUALIZAR -->
            <div id="tab-actualizar" class="form-panel">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="actualizar">
                    <div class="form-group">
                        <label for="u-id">ID del Producto a Editar *</label>
                        <input type="number" id="u-id" name="id" placeholder="ID del producto existente" required>
                    </div>
                    <div class="form-group">
                        <label for="u-nombre">Nuevo Nombre (opcional)</label>
                        <input type="text" id="u-nombre" name="nombre" placeholder="Dejar vacio para no cambiar">
                    </div>
                    <div class="form-group">
                        <label for="u-precio">Nuevo Precio (opcional)</label>
                        <input type="number" step="0.01" id="u-precio" name="precio" placeholder="Dejar vacio para no cambiar">
                    </div>
                    <div class="form-group">
                        <label for="u-cantidad">Nueva Cantidad (opcional)</label>
                        <input type="number" id="u-cantidad" name="cantidad" placeholder="Dejar vacio para no cambiar">
                    </div>
                    <button type="submit" class="btn-crud btn-update">Actualizar Producto</button>
                </form>
            </div>

            <!-- ELIMINAR -->
            <div id="tab-eliminar" class="form-panel">
                <form method="POST" action="" onsubmit="return confirm('Seguro que deseas eliminar este producto?');">
                    <input type="hidden" name="action" value="eliminar">
                    <div class="form-group">
                        <label for="d-id">ID del Producto a Eliminar</label>
                        <input type="number" id="d-id" name="id" placeholder="Ej: 2" required>
                    </div>
                    <button type="submit" class="btn-crud btn-delete">Eliminar Producto</button>
                </form>
            </div>

            <!-- Preview XML -->
            <div class="xml-preview">
                <h3>Contenido actual de jugueteria.xml</h3>
                <pre><?php echo htmlspecialchars($xmlRaw); ?></pre>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Tabla -->
        <div class="panel">
            <div class="table-header">
                <div>
                    <h2>Productos en jugueteria.xml</h2>
                    <span class="panel-sub">Lectura en tiempo real del archivo XML</span>
                </div>
                <span class="badge-count">
                    <?php echo $totalProductos; ?> producto<?php echo ($totalProductos !== 1) ? 's' : ''; ?>
                </span>
            </div>
            <div class="panel-divider"></div>

            <?php if (empty($productos)) { ?>
                <div class="empty-state">
                    <span class="icon">&#128230;</span>
                    <p>No hay productos en el archivo XML.<br>Agrega el primero usando el formulario.</p>
                </div>
            <?php } else { ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $p) { ?>
                        <tr>
                            <td class="td-id">#<?php echo htmlspecialchars($p['id']); ?></td>
                            <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                            <td class="td-price">$<?php echo number_format((float)$p['precio'], 2); ?></td>
                            <td><span class="td-qty"><?php echo htmlspecialchars($p['cantidad']); ?> uds.</span></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>

    </div><!-- /crud-grid -->

</div><!-- /container -->

<script src="../../../assets/js/xml.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>