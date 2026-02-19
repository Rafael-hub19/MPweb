<?php
require_once __DIR__ . '/db_connect.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.html');
    exit;
}

/* ------ Recoger y sanear entradas ------ */
$nombre   = trim($_POST['nombre']   ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm_password'] ?? '');

$errores = [];

/* ------ Validaciones del lado servidor ------ */
if (empty($nombre)) {
    $errores[] = 'El nombre es obligatorio.';
} elseif (mb_strlen($nombre) < 3) {
    $errores[] = 'El nombre debe tener al menos 3 caracteres.';
}

if (empty($email)) {
    $errores[] = 'El correo es obligatorio.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo no tiene un formato válido.';
}

if (empty($password)) {
    $errores[] = 'La contraseña es obligatoria.';
} elseif (mb_strlen($password) < 8) {
    $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
}

if ($password !== $confirm) {
    $errores[] = 'Las contraseñas no coinciden.';
}

/* ------ Si hay errores, regresar con mensaje ------ */
if (!empty($errores)) {
    $msg = implode(' | ', $errores);
    header('Location: registro.html?error=' . urlencode($msg));
    exit;
}

/* ------ Verificar que el correo no exista ------ */
try {
    $pdo  = getDB();

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $msg = 'Este correo ya está registrado. Intenta iniciar sesión.';
        header('Location: registro.html?error=' . urlencode($msg));
        exit;
    }

    /* ------ Insertar nuevo usuario ------ */
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nombre, email, password, created_at)
         VALUES (?, ?, ?, NOW())'
    );
    $insert->execute([$nombre, $email, $hash]);

    // Registro exitoso → redirigir al login
    header('Location: login.html?success=' . urlencode('¡Cuenta creada! Ya puedes iniciar sesión.'));
    exit;

} catch (PDOException $e) {
    error_log('Error al registrar usuario: ' . $e->getMessage());
    $msg = 'Ocurrió un error al guardar el registro. Intenta de nuevo.';
    header('Location: registro.html?error=' . urlencode($msg));
    exit;
}