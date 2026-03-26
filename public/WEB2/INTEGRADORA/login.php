<?php
/* login.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
$redirect = isset($_GET['redirect']) ? basename($_GET['redirect']) : 'galeria.php';
if (isset($_SESSION['usuario'])) {
    header("Location: " . $redirect);
    exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario    = isset($_POST['usuario'])    ? trim($_POST['usuario'])    : '';
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
    $redirect   = isset($_POST['redirect'])  ? basename($_POST['redirect']) : 'galeria.php';
    if ($usuario == '' || $contrasena == '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        include 'conectbd.php';
        $sql = "SELECT * FROM usuarios WHERE usuarioU = '" . mysqli_real_escape_string($conexion, $usuario) . "'";
        $res = mysqli_query($conexion, $sql);
        if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_array($res);
            if ($row['contrasenaU'] == md5($contrasena)) {
                $_SESSION['usuario'] = $row['usuarioU'];
                $_SESSION['idU']     = $row['idU'];
                mysqli_close($conexion);
                header("Location: " . $redirect);
                exit();
            } else {
                $error = 'Contrasena incorrecta.';
            }
        } else {
            $error = 'Usuario no encontrado.';
        }
        mysqli_close($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_login.css">
</head>
<body>
<div class="wrap">
    <div class="brand">
        <h1>MotoStore</h1>
        <p>Centro de Ensenanza Tecnica Industrial</p>
    </div>
    <div class="card">
        <h2>Iniciar Sesion</h2>
        <p class="sub">Accede a la galeria y carrito de compras</p>
        <?php if ($error != '') { echo '<div class="alert-error">' . htmlspecialchars($error) . '</div>'; } ?>
        <form method="POST" action="" onsubmit="return val()">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <div class="field">
                <label for="u">Usuario</label>
                <input type="text" id="u" name="usuario" placeholder="Tu usuario" required
                       value="<?php echo htmlspecialchars(isset($_POST['usuario']) ? $_POST['usuario'] : ''); ?>">
            </div>
            <div class="field">
                <label for="p">Contrasena</label>
                <input type="password" id="p" name="contrasena" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
        <div class="back">&iquest;No tienes cuenta? <a href="registro.php">Registrarse</a></div>
        <div class="back" style="margin-top:8px"><a href="index.php">&larr; Regresar al Inicio</a></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/integradora_login.js"></script>
</body>
</html>
