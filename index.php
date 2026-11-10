<?php
session_start();

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function destinoSegunRol(string $rol): string
{
    return $rol === 'admin' ? 'vistaadmin.php' : 'vistauser.php';
}

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . destinoSegunRol($_SESSION['usuario_rol'] ?? ''));
    exit;
}

$errores = [];
$mensaje = '';
$email   = '';

if (($_GET['registro'] ?? '') === 'ok') {
    $mensaje = 'Cuenta creada. Ya podés iniciar sesión.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }

    if (empty($errores)) {
        try {
            require_once __DIR__ . '/conexion.php';

            $stmt = $conexion->prepare(
                'SELECT id, `user`, password_hash, rol FROM users WHERE email = :email LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_rol']    = $usuario['rol'];
                $_SESSION['usuario_nombre'] = $usuario['user'];

                header('Location: ' . destinoSegunRol($usuario['rol']));
                exit;
            }

            $errores[] = 'Correo o contraseña incorrectos.';
        } catch (Throwable $ex) {
            error_log($ex->getMessage());
            $errores[] = 'Ocurrió un error. Intentá de nuevo en unos minutos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Iniciar sesión</title>
</head>
<body>

    <div>
        <h1>Iniciar sesión</h1>
        <p class="subtitulo">Ingresá con tu correo y contraseña</p>

        <?php if ($mensaje !== ''): ?>
            <p class="mensaje mensaje-exito" role="status"><?= e($mensaje) ?></p>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error" role="alert">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST" id="loginForm">

            <fieldset>

                <div class="control-group">
                    <label for="email">Correo electrónico*</label>
                    <input type="email" id="email" name="email" placeholder="nombre@correo.com"
                           value="<?= e($email) ?>" required>
                </div>

                <div class="control-group">
                    <label for="password">Contraseña*</label>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                </div>
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Iniciar sesión</button>
                <a href="login.php" class="btn-guardar2">Crear cuenta</a>
            </div>

        </form>
    </div>

</body>
</html>