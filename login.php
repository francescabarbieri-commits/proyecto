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
    if ($password !== $_POST['password_confirm'] ?? '') {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }