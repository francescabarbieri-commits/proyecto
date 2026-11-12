<?php
require_once __DIR__ . '/auth.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . destinoSegunRol($_SESSION['usuario_rol'] ?? ''));
    exit;
}

$errores = [];
$user    = '';
$email   = '';
$dni     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user         = trim($_POST['user'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $dni          = trim($_POST['dni'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirmacion = $_POST['password_confirm'] ?? '';

    if ($user === '' || mb_strlen($user) < 3 || mb_strlen($user) > 50) {
        $errores[] = 'El usuario debe tener entre 3 y 50 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($password !== $confirmacion) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        try {
            require_once __DIR__ . '/conexion.php';

            $stmt = $conexion->prepare(
                'INSERT INTO users (`user`, email, dni, password_hash, rol)
                VALUES (:user, :email, :dni, :password_hash, :rol)'
            );
            $stmt->execute([
                'user'          => $user,
                'email'         => $email,
                'dni'           => $dni,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'rol'           => 'usuario',
            ]);

            header('Location: index.php?registro=ok');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ese usuario, correo o DNI ya está registrado.';
            } else {
                error_log($ex->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
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
    <title>Crear cuenta</title>
</head>
<body>

    <div>
        <h1>Crear cuenta</h1>
        <p class="subtitulo">Completá los datos para registrarte</p>

        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error" role="alert">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="registroForm">

            <fieldset>
                <legend>Tus datos</legend>

                <div class="control-group">
                    <label for="user">Crear usuario*</label>
                    <input type="text" id="user" name="user" placeholder="Usuario" maxlength="50"
                        value="<?= e($user) ?>" required>
                </div>

                <div class="control-group">
                    <label for="email">Correo electrónico*</label>
                    <input type="email" id="email" name="email" placeholder="nombre@correo.com"
                        value="<?= e($email) ?>" required>
                </div>

                <div class="control-group">
                    <label for="dni">DNI*</label>
                    <input type="text" id="dni" name="dni" placeholder="Solo números" inputmode="numeric"
                        maxlength="8" pattern="[0-9]{7,8}" value="<?= e($dni) ?>" required>
                </div>

                <div class="control-group">
                    <label for="password">Crear contraseña*</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres"
                        minlength="8" required>
                </div>

                <div class="control-group">
                    <label for="password_confirm">Repetir contraseña*</label>
                    <input type="password" id="password_confirm" name="password_confirm"
                        placeholder="Repetí la contraseña" minlength="8" required>
                </div>
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Crear cuenta</button>
                <a href="index.php" class="btn-guardar2">Volver</a>
            </div>

        </form>
    </div>

</body>
</html>