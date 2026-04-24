<?php
/* registro.php - Registro de nuevos usuarios
   Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193 */
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: galeria.php");
    exit();
}
include 'conectbd.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario    = isset($_POST['usuario'])    ? trim($_POST['usuario'])    : '';
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
    $confirmar  = isset($_POST['confirmar'])  ? trim($_POST['confirmar'])  : '';

    if ($usuario == '' || $contrasena == '' || $confirmar == '') {
        $error = 'Todos los campos son obligatorios.';
    } elseif (strlen($usuario) < 3) {
        $error = 'El usuario debe tener al menos 3 caracteres.';
    } elseif (strlen($contrasena) < 4) {
        $error = 'La contrasena debe tener al menos 4 caracteres.';
    } elseif ($contrasena != $confirmar) {
        $error = 'Las contrasenas no coinciden.';
    } else {
        // Verificar si el usuario ya existe
        $sqlCheck = "SELECT idU FROM `usuarios` WHERE `usuarioU` = '" . mysqli_real_escape_string($conexion, $usuario) . "'";
        $resCheck = mysqli_query($conexion, $sqlCheck);
        if ($resCheck && mysqli_num_rows($resCheck) > 0) {
            $error = 'Ese nombre de usuario ya esta en uso.';
        } else {
            $hash   = md5($contrasena);
            $sqlIns = "INSERT INTO `usuarios` (`usuarioU`, `contrasenaU`) VALUES ('" . mysqli_real_escape_string($conexion, $usuario) . "', '$hash')";
            if (mysqli_query($conexion, $sqlIns)) {
                $success = 'Usuario registrado exitosamente. Ya puedes iniciar sesion.';
            } else {
                $error = 'Error al registrar: ' . mysqli_error($conexion);
            }
        }
    }
    mysqli_close($conexion);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MotoStore</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/integradora_login.css">
    <style>
        .alert-success{padding:11px 14px;border-radius:8px;font-size:14px;margin-bottom:18px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#86efac}
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <img src="img/Logo_motostore.png" alt="MotoStore" style="height:90px;filter:drop-shadow(0 0 8px rgba(255,107,53,0.4));margin-bottom:6px;">
        <p>Centro de Ensenanza Tecnica Industrial</p>
    </div>
    <div class="card">
        <h2>Crear Cuenta</h2>
        <p class="sub">Registra un nuevo usuario para acceder</p>

        <?php if ($error != '')   { echo '<div class="alert-error">'   . htmlspecialchars($error)   . '</div>'; } ?>
        <?php if ($success != '') { echo '<div class="alert-success">' . htmlspecialchars($success) . '</div>'; } ?>

        <?php if ($success == '') { ?>
        <form method="POST" action="" onsubmit="return valReg()">
            <div class="field">
                <label for="u">Usuario</label>
                <input type="text" id="u" name="usuario" placeholder="Minimo 3 caracteres" required
                       value="<?php echo htmlspecialchars(isset($_POST['usuario']) ? $_POST['usuario'] : ''); ?>">
            </div>
            <div class="field">
                <label for="p">Contrasena</label>
                <input type="password" id="p" name="contrasena" placeholder="Minimo 4 caracteres" required>
            </div>
            <div class="field">
                <label for="c">Confirmar Contrasena</label>
                <input type="password" id="c" name="confirmar" placeholder="Repite tu contrasena" required>
            </div>
            <div class="field" style="flex-direction:row;align-items:flex-start;gap:10px;margin-top:4px;">
                <input type="checkbox" id="terminos" name="terminos" required style="margin-top:3px;accent-color:#f97316;width:16px;height:16px;flex-shrink:0;">
                <label for="terminos" style="font-size:13px;color:#94a3b8;font-weight:400;cursor:pointer;">
                    He le&iacute;do y acepto los
                    <a href="terminos.html" target="_blank" style="color:#f97316;text-decoration:underline;">T&eacute;rminos y Condiciones</a>
                    y el Aviso de Privacidad de MotoStore.
                </label>
            </div>
            <button type="submit" class="btn">Registrarse</button>
        </form>
        <?php } ?>

        <div class="back">
            <?php if ($success != '') { ?>
                <a href="login.php">Iniciar Sesion &rarr;</a>
            <?php } else { ?>
                &iquest;Ya tienes cuenta? <a href="login.php">Inicia Sesion</a>
            <?php } ?>
        </div>
        <div class="back" style="margin-top:8px"><a href="index.php">&larr; Regresar al Inicio</a></div>
    </div>
</div>
<footer>
    <div class="footer-inner">
        <div class="footer-brand">MotoStore</div>
        <div class="footer-links">
            <a href="index.php">Inicio</a>
            <a href="galeria.php">Cat&aacute;logo</a>
            <a href="terminos.html">T&eacute;rminos y Condiciones</a>
            <a href="login.php">Iniciar Sesi&oacute;n</a>
        </div>
        <div class="footer-copy">
            &copy; 2026 MotoStore &mdash; Rafael Avila Sanchez &middot; CETI 8F &middot; 22300193<br>
            Programacion Web 2 &mdash; Mtra. Patricia Torres
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/integradora_login.js"></script>
<script>
function valReg() {
    var u = document.getElementById('u').value.trim();
    var p = document.getElementById('p').value.trim();
    var c = document.getElementById('c').value.trim();
    var t = document.getElementById('terminos');
    if (u.length < 3) { alert('El usuario debe tener al menos 3 caracteres.'); return false; }
    if (p.length < 4) { alert('La contrasena debe tener al menos 4 caracteres.'); return false; }
    if (p !== c) { alert('Las contrasenas no coinciden.'); return false; }
    if (!t.checked) { alert('Debes aceptar los Terminos y Condiciones para registrarte.'); return false; }
    return true;
}
</script>
</body>
</html>
