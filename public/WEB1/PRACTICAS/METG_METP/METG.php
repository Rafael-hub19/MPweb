<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuesta GET - CETI</title>
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
                $nombre = isset($_GET['nombreGet']) ? htmlspecialchars($_GET['nombreGet']) : '';
                $apellido = isset($_GET['apellidoGet']) ? htmlspecialchars($_GET['apellidoGet']) : '';
                echo $nombre . " " . $apellido;
            ?>
        </h1>
        <span class="method-badge method-get">MÉTODO: GET</span>
        <p class="description">
            Los datos fueron enviados mediante el método GET. Puedes ver los parámetros en la URL del navegador.
            El método GET es visible y se usa comúnmente para consultas y búsquedas.
        </p>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>