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
                    <label for="nombre">Usuario*</label>
                    <input type="text" id="user" name="user" placeholder="Usuario" required>
                </div>                
                
                <div class="control-group">
                    <label for="email">Correo electrónico*</label>
                    <input type="email" id="email" name="email" placeholder="nombre@correo.com" required>
                </div>

                <div class="control-group">
                    <label for="password">Contraseña*</label>
                    <input type="password" id="password" name="pass" placeholder= "Contraseña" required>
                </div>
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Iniciar Sesión</button>
                <button type="submit2" class="btn-guardar2">Crear Cuenta <href= login.php></button>
            </div>

        </form>
</div>


<?php
    $errores = [];
    $user = '';
    $email = '';
    $password = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user = trim($_POST['user'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
    }
    if ($user === '') {
        $errores[] = 'El nombre de usuario es obligatorio.';
    }
    if ($password === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }


    if (empty($errores)) {
        try {
            $sql = 'INSERT INTO users (user, email, password) VALUES (:user, :email, :password)';
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'user' => $user,
                'email' => $email,
                'password' => $password
            ]);
        $mensaje = 'Usuario registrado correctamente.';
        $user = '';
        $email = '';
        $password = '';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'Ese usuario ya está registrado.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
?>