<?php
require_once __DIR__ . '/auth.php';
requerirAdmin();
require_once __DIR__ . '/conexion.php';

$id = filter_input(
    $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);
if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Identificador inválido.');
}

$stmt = $conexion->prepare(
    'SELECT id, `user`, email, dni, rol FROM users WHERE id = :id AND archivado_en IS NULL LIMIT 1'
);
$stmt->execute(['id' => $id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    http_response_code(404);
    exit('Usuario no encontrado.');
}

$errores = [];
$user    = $usuario['user'];
$email   = $usuario['email'];
$dni     = $usuario['dni'];
$rol     = $usuario['rol'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf'] ?? null)) {
        http_response_code(403);
        exit('Solicitud no válida.');
    }

    $user  = trim($_POST['user'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $dni   = trim($_POST['dni'] ?? '');
    $rol   = trim($_POST['rol'] ?? '');

    if ($user === '' || mb_strlen($user) < 3 || mb_strlen($user) > 50) {
        $errores[] = 'El usuario debe tener entre 3 y 50 caracteres.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }
    if (!in_array($rol, ['usuario', 'admin'], true)) {
        $errores[] = 'Elegí un rol válido.';
    }
    if ($id === (int)$_SESSION['usuario_id'] && $rol !== 'admin') {
        $errores[] = 'No podés quitarte a vos mismo el rol de administrador.';
    }

    if (empty($errores)) {
        try {
            $stmt = $conexion->prepare(
                'UPDATE users
                SET `user` = :user, email = :email, dni = :dni, rol = :rol
                WHERE id = :id AND archivado_en IS NULL'
            );
            $stmt->execute([
                'user'  => $user,
                'email' => $email,
                'dni'   => $dni,
                'rol'   => $rol,
                'id'    => $id,
            ]);

            if ($id === (int)$_SESSION['usuario_id']) {
                $_SESSION['usuario_user'] = $user;
            }

            $_SESSION['flash'] = ['tipo' => 'exito', 'texto' => 'Usuario actualizado correctamente.'];
            header('Location: admin_users.php');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ese usuario, correo o DNI ya está en uso por otra cuenta.';
            } else {
                error_log($ex->getMessage());
                $errores[] = 'Ocurrió un error al guardar los cambios.';
            }
        }
    }
}

$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Editar usuario</title>
</head>
<body>

    <nav class="navbar">
        <a id="loguito" href="admin_users.php">
            <img src="img/logo.png" alt="Logo del teatro" height="48">
        </a>
        <ul class="nav-links">
            <li><a href="admin_users.php">Usuarios</a></li>
            <li><a href="signout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <div>
        <h1>Editar usuario</h1>
        <p class="subtitulo">Modificá los datos de la cuenta</p>

        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error" role="alert">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="edit_user.php" method="POST" id="editForm">
            <input type="hidden" name="id" value="<?= (int)$id ?>">
            <input type="hidden" name="csrf" value="<?= e($token) ?>">

            <fieldset>
                <legend>Datos de la cuenta</legend>

                <div class="control-group">
                    <label for="user">Usuario*</label>
                    <input type="text" id="user" name="user" maxlength="50"
                        value="<?= e($user) ?>" required>
                </div>

                <div class="control-group">
                    <label for="email">Correo electrónico*</label>
                    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
                </div>

                <div class="control-group">
                    <label for="dni">DNI*</label>
                    <input type="text" id="dni" name="dni" inputmode="numeric" maxlength="8"
                        pattern="[0-9]{7,8}" value="<?= e($dni) ?>" required>
                </div>

                <div class="control-group">
                    <label for="rol">Rol*</label>
                    <select id="rol" name="rol" required>
                        <option value="usuario" <?= $rol === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin" <?= $rol === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Guardar cambios</button>
                <a href="admin_users.php" class="btn-guardar2">Volver</a>
            </div>

        </form>
    </div>

</body>
</html>