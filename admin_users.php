<?php
require_once __DIR__ . '/auth.php';
requerirAdmin();
require_once __DIR__ . '/conexion.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$buscar     = trim($_GET['buscar'] ?? '');
$sql        = 'SELECT id, `user`, email, dni, rol, creado_en FROM users WHERE archivado_en IS NULL';
$parametros = [];

if ($buscar !== '') {
    $sql .= ' AND (`user` LIKE :user OR dni LIKE :dni OR email LIKE :email)';
    $termino    = '%' . addcslashes($buscar, '%_\\') . '%';
    $parametros = ['user' => $termino, 'dni' => $termino, 'email' => $termino];
}
$sql .= ' ORDER BY id DESC';

$usuarios = [];
$error    = '';

try {
    $stmt = $conexion->prepare($sql);
    $stmt->execute($parametros);
    $usuarios = $stmt->fetchAll();
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $error = 'No pudimos cargar los usuarios. Intentá de nuevo en unos minutos.';
}

$total = count($usuarios);
$token = csrfToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Administración</title>
</head>
<body>

    <nav class="navbar">
        <a id="loguito" href="admin_users.php">
            <img src="img/logo.png" alt="Logo del teatro" height="48">
        </a>
        <ul class="nav-links">
            <li><a href="admin_users.php">Usuarios</a></li>
            <li><a href="form.php">Reservar entradas</a></li>
            <li><a href="signout.php">Cerrar sesión</a></li>
        </ul>
    </nav>

    <div class="tarjeta-ancha">
        <h1>Usuarios</h1>
        <p class="subtitulo">
            <?= $total === 1 ? '1 usuario' : $total . ' usuarios' ?>
            <?php if ($buscar !== ''): ?>
                que coinciden con «<?= e($buscar) ?>»
            <?php endif; ?>
        </p>

        <?php if ($flash): ?>
            <p class="mensaje <?= $flash['tipo'] === 'exito' ? 'mensaje-exito' : 'mensaje-error' ?>" role="status">
                <?= e($flash['texto']) ?>
            </p>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <p class="mensaje mensaje-error" role="alert"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="GET" action="admin_users.php" class="busqueda">
            <input type="search" name="buscar" maxlength="100" value="<?= e($buscar) ?>"
                placeholder="Usuario, DNI o correo">
            <button type="submit" class="btn-guardar">Buscar</button>
        </form>

<?php if ($total === 0): ?>
            <p class="vacio">No hay usuarios para mostrar.</p>
<?php else: 
        
?>
            <div class="tabla-contenedor">
                <table class="tabla-usuarios">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>DNI</th>
                            <th>Rol</th>
                            <th>Alta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?= e($u['user']) ?></td>
                                <td><?= e($u['email']) ?></td>
                                <td><?= e($u['dni']) ?></td>
                                <td><?= e($u['rol']) ?></td>
                                <td><?= e(date('d/m/Y', strtotime($u['creado_en']))) ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="edit_user.php?id=<?= (int)$u['id'] ?>" class="btn-chico">Editar</a>
                                        <?php if ((int)$u['id'] !== (int)$_SESSION['usuario_id']): ?>
                                            <form method="POST" action="archivar_user.php" class="form-inline"
                                                onsubmit="return confirm('¿Archivar a este usuario?');">
                                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                                <button type="submit" class="btn-chico btn-peligro">Archivar</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Teatro. Todos los derechos reservados.
    </footer>

</body>
</html>
