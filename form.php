<?php
session_start();

$obras       = ['Romeo y Julieta', 'La casa de Bernarda Alba', 'Hamlet'];
$horarios    = ['18:00', '20:30'];
$ubicaciones = ['Platea', 'Palco', 'Pullman', 'Galería'];

$errores  = [];
$mensaje  = '';

$nombre        = '';
$email         = '';
$obra          = '';
$fecha         = '';
$horario       = '';
$cantidad      = '';
$ubicacion     = '';
$accesibilidad = false;
$comentarios   = '';

$reserva = $_SESSION['reserva_ok'] ?? null;
unset($_SESSION['reserva_ok']);

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function fechaLarga(string $ymd): string
{
    $dias  = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    $f = new DateTime($ymd);
    return ucfirst($dias[(int)$f->format('w')]) . ' ' . $f->format('j')
        . ' de ' . $meses[(int)$f->format('n') - 1] . ' de ' . $f->format('Y');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre        = trim($_POST['nombre'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $obra          = trim($_POST['obra'] ?? '');
    $fecha         = trim($_POST['fecha'] ?? '');
    $horario       = trim($_POST['horario'] ?? '');
    $cantidad      = trim($_POST['cantidad'] ?? '');
    $ubicacion     = trim($_POST['ubicacion'] ?? '');
    $accesibilidad = isset($_POST['accesibilidad']);
    $comentarios   = trim($_POST['comentarios'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }
    if (!in_array($obra, $obras, true)) {
        $errores[] = 'Elegí una obra de la lista.';
    }

    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
        $errores[] = 'Elegí una fecha válida.';
    } elseif ($fecha < date('Y-m-d')) {
        $errores[] = 'La fecha no puede ser anterior a hoy.';
    }

    if (!in_array($horario, $horarios, true)) {
        $errores[] = 'Elegí un horario.';
    }
    if (!ctype_digit($cantidad) || (int)$cantidad < 1 || (int)$cantidad > 10) {
        $errores[] = 'La cantidad de entradas debe estar entre 1 y 10.';
    }
    if (!in_array($ubicacion, $ubicaciones, true)) {
        $errores[] = 'Elegí una ubicación en la sala.';
    }

    if (empty($errores)) {
        try {
            require_once __DIR__ . '/conexion.php';

            $sql = 'INSERT INTO reservas
                        (nombre, email, obra, fecha, horario, cantidad, ubicacion, accesibilidad, comentarios)
                    VALUES
                        (:nombre, :email, :obra, :fecha, :horario, :cantidad, :ubicacion, :accesibilidad, :comentarios)';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'nombre'        => $nombre,
                'email'         => $email,
                'obra'          => $obra,
                'fecha'         => $fecha,
                'horario'       => $horario,
                'cantidad'      => (int)$cantidad,
                'ubicacion'     => $ubicacion,
                'accesibilidad' => $accesibilidad ? 1 : 0,
                'comentarios'   => $comentarios,
            ]);

            $_SESSION['reserva_ok'] = [
                'id'            => $conexion->lastInsertId(),
                'nombre'        => $nombre,
                'email'         => $email,
                'obra'          => $obra,
                'fecha'         => $fecha,
                'horario'       => $horario,
                'cantidad'      => (int)$cantidad,
                'ubicacion'     => $ubicacion,
                'accesibilidad' => $accesibilidad,
            ];
            header('Location: form.php');
            exit;
        } catch (Throwable $ex) {
            error_log($ex->getMessage());
            $errores[] = 'No pudimos guardar tu reserva. Intentá de nuevo en unos minutos.';
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
    <title>Reservar entradas</title>
</head>
<body>

    <nav class="navbar">
        <a id="loguito" href="index.php">
            <img src="img/logo.png" alt="Logo del teatro" height="48">
        </a>
        <ul class="nav-links">
            <li><a href="index.php">Inicio</a></li>
            <li><a href="form.php">Reservar entradas</a></li>
        </ul>
    </nav>

    <div>
<?php if ($reserva): ?>
        <h1>¡Reserva confirmada!</h1>
        <p class="subtitulo">Te enviaremos los detalles a <?= e($reserva['email']) ?></p>

        <article class="entrada">
            <div class="entrada-cuerpo">
                <p class="entrada-etiqueta">Entrada</p>
                <h2 class="entrada-obra"><?= e($reserva['obra']) ?></h2>

                <dl class="entrada-datos">
                    <div class="dato-ancho">
                        <dt>Fecha</dt>
                        <dd><?= e(fechaLarga($reserva['fecha'])) ?></dd>
                    </div>
                    <div>
                        <dt>Horario</dt>
                        <dd><?= e($reserva['horario']) ?> hs</dd>
                    </div>
                    <div>
                        <dt>Ubicación</dt>
                        <dd><?= e($reserva['ubicacion']) ?></dd>
                    </div>
                </dl>

                <p class="entrada-titular">A nombre de <?= e($reserva['nombre']) ?></p>
            </div>

            <div class="entrada-talon">
                <p class="talon-etiqueta">Admite</p>
                <p class="talon-cantidad"><?= (int)$reserva['cantidad'] ?></p>
                <p class="talon-codigo">N.° <?= str_pad((string)$reserva['id'], 5, '0', STR_PAD_LEFT) ?></p>
                <?php if ($reserva['accesibilidad']): ?>
                    <p class="talon-nota">Lugar accesible</p>
                <?php endif; ?>
            </div>
        </article>

        <div class="botones-container">
            <a href="form.php" class="btn-guardar">Reservar otra función</a>
            <a href="index.php" class="btn-guardar2">Ir al inicio</a>
        </div>
<?php else: ?>
        <h1>Reservar entradas</h1>
        <p class="subtitulo">Completá los datos para asegurar tu lugar en la sala</p>

        <?php if ($mensaje !== ''): ?>
            <p class="mensaje mensaje-exito" role="status"><?= e($mensaje) ?></p>
        <?php endif; ?>

        <?php if (!empty($errores)): ?>
            <div class="mensaje mensaje-error" role="alert">
                <p>Revisá estos puntos:</p>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; 
        
?>

        <form action="form.php" method="POST" id="reservaForm">

            <fieldset>
                <legend>Datos Personales</legend>

                <div class="control-group">
                    <label for="nombre">Nombre y apellido*</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre y apellido"
                        value="<?= e($nombre) ?>" required>
                </div>

                <div class="control-group">
                    <label for="email">Correo electrónico*</label>
                    <input type="email" id="email" name="email" placeholder="nombre@correo.com"
                        value="<?= e($email) ?>" required>
                </div>
            </fieldset>

            <fieldset>
                <legend>Función</legend>

                <div class="control-group">
                    <label for="obra">Obra*</label>
                    <select id="obra" name="obra" required>
                        <option value="">Elegí una obra</option>
                        <?php foreach ($obras as $o): ?>
                            <option value="<?= e($o) ?>" <?= $obra === $o ? 'selected' : '' ?>><?= e($o) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="control-group">
                    <label for="fecha">Fecha*</label>
                    <input type="date" id="fecha" name="fecha" min="<?= date('Y-m-d') ?>"
                        value="<?= e($fecha) ?>" required>
                </div>

                <div class="control-group">
                    <label>Horario*</label>
                    <div class="opciones-group">
                        <?php foreach ($horarios as $i => $h): ?>
                            <label class="opcion-inline" for="horario<?= $i ?>">
                                <input type="radio" id="horario<?= $i ?>" name="horario"
                                    value="<?= e($h) ?>" <?= $horario === $h ? 'checked' : '' ?> required>
                                <?= e($h) ?> hs
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>Entradas</legend>

                <div class="control-group">
                    <label for="cantidad">Cantidad de entradas*</label>
                    <select id="cantidad" name="cantidad" required>
                        <option value="">Elegí la cantidad</option>
                        <?php for ($n = 1; $n <= 10; $n++): ?>
                            <option value="<?= $n ?>" <?= $cantidad === (string)$n ? 'selected' : '' ?>><?= $n ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="control-group">
                    <label for="ubicacion">Ubicación en la sala*</label>
                    <select id="ubicacion" name="ubicacion" required>
                        <option value="">Elegí una ubicación</option>
                        <?php foreach ($ubicaciones as $u): ?>
                            <option value="<?= e($u) ?>" <?= $ubicacion === $u ? 'selected' : '' ?>><?= e($u) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="control-group">
                    <label class="opcion-inline" for="accesibilidad">
                        <input type="checkbox" id="accesibilidad" name="accesibilidad"
                            <?= $accesibilidad ? 'checked' : '' ?>>
                        Necesito un lugar con accesibilidad
                    </label>
                </div>

                <div class="control-group">
                    <label for="comentarios">Comentarios</label>
                    <textarea id="comentarios" name="comentarios" rows="3"
                            placeholder="Algo que debamos saber"><?= e($comentarios) ?></textarea>
                </div>
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Reservar entradas</button>
                <button type="reset" class="btn-reset">Limpiar formulario</button>
            </div>

        </form>
<?php endif; ?>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Teatro de Francesca Barbieri.
    </footer>

</body>
</html>