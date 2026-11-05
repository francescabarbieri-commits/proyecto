<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inicio de Sesión</title>
</head>

<div>
        <h1>Iniciar Sesión</h1>
        <p class="subtitulo">Completar los campos solicitados</p>
        
        <form action="#" method="POST" id="ejercicioForm">


            <fieldset>

                <div class="control-group">
                    <label for="nombre">Crear usuario*</label>
                    <input type="text" id="user" name="user" placeholder="Usuario" required>
                </div>                
                
                <div class="control-group">
                    <label for="email">Agregar correo electrónico*</label>
                    <input type="email" id="email" name="email" placeholder="nombre@correo.com" required>
                </div>

                <div class="control-group">
                    <label for="password">Crear contraseña*</label>
                    <input type="password" id="password" name="pass" placeholder= "Contraseña" required>
                </div>

                <div class="control-group">
                    <label for="password">Repetir contraseña*</label>
                    <input type="password" id="password" name="pass" placeholder= "Contraseña" required>
                </div>
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Crear Cuenta</button>
                <button type="submit2" class="btn-guardar2">Volver<a href="index.php"></a></button>
            </div>

        </form>
</div>

<?php
    $errores = [];
    $user = '';
    $email = '';
    $DNI = '';
    $password = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = trim($_POST['user'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $DNI = trim($_POST['DNI'] ?? '');
        $ROL=trim($_POST['ROL'] ?? '');
        $password = trim($_POST['password'] ?? '');
    }
    if ($user === '') {
        $errores[] = 'El nombre de usuario es obligatorio.';
    }
    if ($DNI === '' || !preg_match('/^[0-9]{7,8}$/', $DNI)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }
    if ($password === '' || !preg_match('/^[0-9]{7,8}$/', $password)) {
        $errores[] = 'La contraseña debe tener mínimo 8 números.';
    }
    if ($password !== $_POST['password_confirm'] ?? '') {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }



$passwordHash = password_hash(
$password,
PASSWORD_DEFAULT
);

$sql = 'INSERT INTO teatro
(user, email, password_hash, rol)
VALUES (:user, :email, :password_hash, :rol)';
$stmt = $conexion->prepare($sql);
$stmt->execute([
'user' => $user,
'email' => $email,
'password_hash' => $passwordHash,
'rol' => 'usuario'
]);

$sql = 'SELECT id, user, email, password_hash, rol
FROM usuarios
WHERE email = :email
AND archivado_en IS NULL
LIMIT 1';
$stmt = $conexion->prepare($sql);
$stmt->execute(['email' => $email]);
$usuario = $stmt->fetch();

if ($user !== false &&
password_verify($password, $user['password_hash']))
{
session_start();
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_rol'] = $user['rol'];
// Redirigir según rol
if ($usuario['rol'] === 'admin') {
header('Location: admin_usuarios.php');
} else {
header('Location: panel.php');
}
exit;
}