
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inicio de Sesión de Administrador</title>
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
            </div>

        </form>
</div>

<?php

session_start();
if (
!isset($_SESSION['usuario_id']) ||
($_SESSION['usuario_rol'] ?? '') !== 'admin'
) {
http_response_code(403);
exit('Acceso denegado.');
}

