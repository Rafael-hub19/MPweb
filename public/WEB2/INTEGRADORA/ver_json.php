<?php
/* ver_json.php - Visualizador del carrito JSON
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

$jsonFile = 'carrito.json';
$carrito  = array();
$jsonRaw  = '[]';

if (file_exists($jsonFile)) {
    $jsonRaw = file_get_contents($jsonFile);
    $dec     = json_decode($jsonRaw, true);
    if (is_array($dec)) $carrito = $dec;
}

$total = 0;
foreach ($carrito as $item) {
    $total += (float)$item['precio'] * (int)$item['cantidad'];
}

$jsonPretty = json_encode($carrito, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver JSON del Carrito — MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/integradora_json.css">
</head>
<body>

<!-- NAV -->
<nav>
    <a class="brand" href="index.php">MotoStore</a>
    <div class="nav-r">
        <a href="carrito.php" class="btn-n">&larr; Carrito</a>
        <a href="galeria.php" class="btn-n">&#128661; Cat&aacute;logo</a>
        <a href="logout.php" onclick="return confirm('&iquest;Seguro?')" class="btn-n">Salir</a>
    </div>
</nav>

<!-- PAGE -->
<div class="page">
    <div class="page-lbl">Estructura de datos</div>
    <h1>Carrito JSON</h1>
    <p class="sub">Contenido actual del archivo <strong style="color:#f97316">carrito.json</strong> — almacenamiento del carrito de compras</p>

    <!-- RUTA DEL ARCHIVO -->
    <div class="json-path">
        &#128196;&nbsp;Archivo:
        <span>https://proyectosinformaticatnl.ceti.mx/mueblesweb/public/WEB2/INTEGRADORA/carrito.json</span>
        <a href="https://proyectosinformaticatnl.ceti.mx/mueblesweb/public/WEB2/INTEGRADORA/carrito.json" target="_blank">&#128279; Ver RAW</a>
    </div>

    <!-- STATS -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-num"><?php echo count($carrito); ?></div>
            <div class="stat-lbl">Productos</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?php echo array_sum(array_column($carrito, 'cantidad')); ?></div>
            <div class="stat-lbl">Unidades</div>
        </div>
        <div class="stat-box">
            <div class="stat-num">$<?php echo number_format($total, 0, '.', ','); ?></div>
            <div class="stat-lbl">Total MXN</div>
        </div>
        <div class="stat-box">
            <div class="stat-num"><?php echo strlen($jsonRaw); ?>B</div>
            <div class="stat-lbl">Tama&ntilde;o</div>
        </div>
    </div>

    <!-- PANELES -->
    <div class="panels">

        <!-- JSON FORMATEADO -->
        <div class="panel">
            <div class="panel-header">
                <h2>&#128196; JSON Formateado</h2>
                <button class="copy-btn" onclick="copiarJSON()">Copiar</button>
            </div>
            <div class="json-viewer">
                <pre id="json-pre"><?php echo resaltarJSON($jsonPretty); ?></pre>
            </div>
        </div>

        <!-- TABLA DE ITEMS -->
        <div class="panel">
            <div class="panel-header">
                <h2>&#128203; Items del Carrito</h2>
                <span class="badge">ARRAY[<?php echo count($carrito); ?>]</span>
            </div>
            <?php if (count($carrito) == 0): ?>
                <p class="empty-msg">El carrito est&aacute; vac&iacute;o — a&uacute;n no hay datos en el archivo JSON</p>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>idP</th>
                            <th>nombre</th>
                            <th>precio</th>
                            <th>cantidad</th>
                            <th>subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($carrito as $i => $item): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo (int)$item['idP']; ?></td>
                            <td class="td-nombre"><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td class="td-precio">$<?php echo number_format((float)$item['precio'], 0, '.', ','); ?></td>
                            <td><?php echo (int)$item['cantidad']; ?></td>
                            <td class="td-sub">$<?php echo number_format((float)$item['precio'] * (int)$item['cantidad'], 0, '.', ','); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /panels -->
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-links">
        <a href="index.php">Inicio</a>
        <a href="galeria.php">Cat&aacute;logo</a>
        <a href="carrito.php">Carrito</a>
        <a href="ver_json.php">Ver JSON</a>
        <a href="terminos.html" target="_blank">T&eacute;rminos y Condiciones</a>
    </div>
    <div class="footer-copy">
        &copy; 2026 MotoStore &mdash; Rafael Avila Sanchez &middot; CETI 8F &middot; 22300193<br>
        Programacion Web 2 &mdash; Mtra. Patricia Torres
    </div>
</footer>

<script>
function copiarJSON() {
    var texto = document.getElementById('json-pre').innerText;
    navigator.clipboard.writeText(texto).then(function() {
        var btn = document.querySelector('.copy-btn');
        btn.textContent = 'Copiado!';
        setTimeout(function() { btn.textContent = 'Copiar'; }, 2000);
    });
}
</script>
</body>
</html>
<?php
/* ── Resaltado de sintaxis JSON ── */
function resaltarJSON($json) {
    $json = htmlspecialchars($json, ENT_QUOTES, 'UTF-8');
    /* Claves */
    $json = preg_replace('/"([^"]+)"(\s*):/', '<span class="j-key">"$1"</span><span class="j-punc">$2:</span>', $json);
    /* Strings (valores) */
    $json = preg_replace('/:\s*"([^"]*)"/', ': <span class="j-str">"$1"</span>', $json);
    /* Numeros */
    $json = preg_replace('/:\s*(-?\d+\.?\d*)/', ': <span class="j-num">$1</span>', $json);
    /* Booleanos */
    $json = preg_replace('/:\s*(true|false)/', ': <span class="j-bool">$1</span>', $json);
    /* Null */
    $json = preg_replace('/:\s*(null)/', ': <span class="j-null">$1</span>', $json);
    /* Puntuacion restante */
    $json = preg_replace('/([{}\[\],])/', '<span class="j-punc">$1</span>', $json);
    return $json;
}
?>
