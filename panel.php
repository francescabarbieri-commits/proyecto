<?php
require_once __DIR__ . '/auth.php';
requerirLogin();

$user  = $_SESSION['usuario_user'] ?? '';
$esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Mi panel</title>
</head>
<body>

    <nav class="navbar">
        <a id="loguito" href="panel.php">
            <img src="img/logo.png" alt="Logo del teatro" height="48">
        </a>
        <ul class="nav-links">
            <li><a href="form.php">Reservar entradas</a></li>
            <?php if ($esAdmin): ?>
                <li><a href="admin_users.php">Administración</a></li>
            <?php endif; ?>
            <li><a href="signout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <div>
        <h1>Hola, <?= e($user) ?></h1>
        <p class="subtitulo">Elegí tu función y reservá tus entradas</p>

        <div class="botones-container">
            <a href="form.php" class="btn-guardar">Reservar entradas</a>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Teatro. Todos los derechos reservados.
    </footer>

</body>
</html>