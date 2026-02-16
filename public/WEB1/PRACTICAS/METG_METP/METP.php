<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta POST - CETI</title>
    <link rel="stylesheet" href="../../assets/css/response.css">
</head>
<body>
    <a href="metg_p.html" class="back-button">← Volver al formulario</a>

    <div class="logo-container">
        <img src="../../../../logo-ceti.png" alt="Logo CETI" class="logo-img">
    </div>

    <div class="container">
        <div class="success-icon">✅</div>
        <h2>Datos recibidos correctamente:</h2>
        <div class="divider"></div>
        <h1>
            <?php
                $nombre = isset($_POST['nombrePost']) ? htmlspecialchars($_POST['nombrePost']) : '';
                $apellido = isset($_POST['apellidoPost']) ? htmlspecialchars($_POST['apellidoPost']) : '';
                echo $nombre . " " . $apellido;
            ?>
        </h1>
        <span class="method-badge method-post">MÉTODO: POST</span>
        <p class="description">
            Los datos fueron enviados mediante el método POST. Los parámetros NO son visibles en la URL del navegador.
            El método POST es más seguro y se usa típicamente para enviar información sensible o grandes cantidades de datos.
        </p>
    </div>
</body>
</html>