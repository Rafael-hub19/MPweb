<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor JSON - Pedido del día</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .json-viewer {
            background: #1e1e2e;
            color: #cdd6f4;
            border-radius: 12px;
            padding: 1.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.88rem;
            line-height: 1.7;
            overflow-x: auto;
            white-space: pre;
        }
        .json-key   { color: #89b4fa; }
        .json-str   { color: #a6e3a1; }
        .json-num   { color: #fab387; }
        .json-bool  { color: #f38ba8; }
        .json-null  { color: #9399b2; }
        .json-brace { color: #cba6f7; }

        .file-badge {
            background: #1e1e2e;
            color: #a6e3a1;
            font-family: monospace;
            font-size: 0.82rem;
            border-radius: 8px;
            padding: 4px 12px;
        }
        .live-dot {
            width: 10px;
            height: 10px;
            background: #2ecc71;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.4s infinite;
        }
        @keyframes pulse {
            0%   { opacity: 1;   transform: scale(1); }
            50%  { opacity: 0.4; transform: scale(1.3); }
            100% { opacity: 1;   transform: scale(1); }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100" style="background-color:#fff8f0;">

    <!-- Navbar -->
    <nav class="navbar navbar-dark sticky-top" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <div class="container-fluid">
            <a href="carrito.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Carrito
            </a>
            <span class="navbar-brand mx-auto fw-bold">
                <i class="fas fa-candy-cane me-2"></i>Dulcería "La Patita"
            </span>
            <a href="galeria_dinamica.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-store me-1"></i> Galería
            </a>
        </div>
    </nav>

    <!-- Encabezado -->
    <header class="text-center text-white py-4" style="background: linear-gradient(135deg, #c0392b, #922b21);">
        <h1 class="fw-bold mb-1"><i class="fas fa-code me-2"></i>Visor JSON</h1>
        <p class="mb-0 opacity-75 small">Contenido del archivo de pedido en tiempo real</p>
    </header>

    <main class="container py-4 flex-grow-1">
<?php
$fechaHora     = date("F_j_Y");
$ruta          = $fechaHora . "_pedido.json";
$nombreArchivo = $fechaHora . "_pedido.json";

function colorearJSON($json) {
    $json = json_encode(json_decode($json), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $json = htmlspecialchars($json, ENT_QUOTES);
    $json = preg_replace('/"([^"]+)"\s*:/', '<span class="json-key">"$1"</span>:', $json);
    $json = preg_replace('/:\s*"([^"]*)"/', ': <span class="json-str">"$1"</span>', $json);
    $json = preg_replace('/:\s*(\d+\.?\d*)/', ': <span class="json-num">$1</span>', $json);
    $json = preg_replace('/:\s*(true|false)/', ': <span class="json-bool">$1</span>', $json);
    $json = preg_replace('/:\s*(null)/', ': <span class="json-null">$1</span>', $json);
    $json = preg_replace('/([{}\[\]])/', '<span class="json-brace">$1</span>', $json);
    return $json;
}
?>
        <!-- Info del archivo -->
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="file-badge">
                <i class="fas fa-file-code me-1"></i><?php echo $nombreArchivo; ?>
            </span>
            <span class="d-flex align-items-center gap-2 text-muted small">
                <span class="live-dot"></span> Actualizando cada 5 segundos
            </span>
            <span class="ms-sm-auto">
                <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-sync-alt me-1"></i>Actualizar ahora
                </button>
            </span>
        </div>

<?php if (file_exists($ruta)): ?>
    <?php
    $archivo = file_get_contents($ruta);
    $pedido  = json_decode($archivo, true);
    $total   = 0;
    ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-0" id="visorTab">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tabTabla">
                <i class="fas fa-table me-1"></i> Vista tabla
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tabJSON">
                <i class="fas fa-code me-1"></i> JSON crudo
            </button>
        </li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white p-3 shadow-sm mb-4">

        <!-- Vista tabla -->
        <div class="tab-pane fade show active" id="tabTabla">
            <?php if (!empty($pedido)): ?>
            <div class="table-responsive mt-2">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-white" style="background:linear-gradient(135deg,#ff6f61,#c0392b);">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Precio unit.</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pedido as $i => $item): ?>
                        <tr>
                            <td class="text-muted small"><?php echo $i + 1; ?></td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td class="text-muted small"><?php echo htmlspecialchars($item['marca']); ?></td>
                            <td>$<?php echo number_format($item['precio'], 2); ?></td>
                            <td><span class="badge rounded-pill bg-secondary"><?php echo $item['cantidad']; ?></span></td>
                            <td class="fw-bold" style="color:#c0392b;">$<?php echo number_format($item['subTotal'], 2); ?></td>
                        </tr>
                    <?php $total += $item['subTotal']; endforeach; ?>
                        <tr class="table-light fw-bold">
                            <td colspan="5" class="text-end">TOTAL</td>
                            <td style="color:#c0392b;">$<?php echo number_format($total, 2); ?> MXN</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex flex-wrap gap-3 mt-3 small text-muted">
                <span><i class="fas fa-box me-1"></i><?php echo count($pedido); ?> producto(s)</span>
                <span><i class="fas fa-cubes me-1"></i><?php echo array_sum(array_column($pedido, 'cantidad')); ?> pieza(s)</span>
                <span><i class="fas fa-weight me-1"></i><?php echo round(filesize($ruta) / 1024, 2); ?> KB</span>
                <span><i class="fas fa-clock me-1"></i>Modificado: <?php echo date("H:i:s", filemtime($ruta)); ?></span>
            </div>
            <?php else: ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                El archivo existe pero el carrito está vacío.
            </div>
            <?php endif; ?>
        </div>

        <!-- JSON crudo -->
        <div class="tab-pane fade" id="tabJSON">
            <div class="d-flex justify-content-end mb-2 mt-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="copiarJSON()">
                    <i class="fas fa-copy me-1"></i>Copiar JSON
                </button>
            </div>
            <div class="json-viewer" id="jsonContent"><?php echo colorearJSON($archivo); ?></div>
        </div>
    </div>

<?php else: ?>
    <div class="card border-0 shadow-sm text-center py-5 px-4" style="border-radius:16px;">
        <i class="fas fa-file-slash fa-4x mb-3 opacity-25"></i>
        <h5 class="fw-bold">No hay archivo JSON para hoy</h5>
        <p class="text-muted mb-3">
            El archivo <code><?php echo $nombreArchivo; ?></code> se creará automáticamente
            cuando agregues el primer producto al carrito.
        </p>
        <a href="galeria_dinamica.php" class="btn text-white fw-semibold mx-auto" style="background:linear-gradient(135deg,#ff6f61,#c0392b); border-radius:50px; width:fit-content;">
            <i class="fas fa-store me-1"></i> Ir a la galería
        </a>
    </div>
<?php endif; ?>

        <!-- Historial de archivos JSON -->
        <?php $archivosJSON = glob("*_pedido.json"); if (!empty($archivosJSON)): ?>
        <div class="mt-4">
            <h6 class="fw-bold text-muted mb-2">
                <i class="fas fa-history me-1"></i>Archivos JSON generados
            </h6>
            <div class="list-group shadow-sm">
            <?php foreach (array_reverse($archivosJSON) as $arch): ?>
                <a href="<?php echo $arch; ?>" target="_blank"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-file-code me-2 text-warning"></i>
                        <code><?php echo htmlspecialchars($arch); ?></code>
                    </span>
                    <span class="d-flex align-items-center gap-2">
                        <span class="text-muted small"><?php echo round(filesize($arch) / 1024, 2); ?> KB</span>
                        <?php if ($arch === $ruta): ?>
                            <span class="badge" style="background:#27ae60;">Activo hoy</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Anterior</span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <footer class="text-center py-3 text-white mt-auto" style="background: linear-gradient(135deg, #ff6f61, #c0392b);">
        <small><i class="fas fa-copyright me-1"></i>2025 Patricia Torres | Dulcería La Patita</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() { location.reload(); }, 5000);

        function copiarJSON() {
            const texto = document.getElementById('jsonContent').innerText;
            navigator.clipboard.writeText(texto).then(function() {
                const btn = document.querySelector('[onclick="copiarJSON()"]');
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Copiado!';
                btn.classList.replace('btn-outline-secondary', 'btn-success');
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-copy me-1"></i>Copiar JSON';
                    btn.classList.replace('btn-success', 'btn-outline-secondary');
                }, 2000);
            });
        }
    </script>
</body>
</html>
