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
            </fieldset>

            <div class="botones-container">
                <button type="submit" class="btn-guardar">Iniciar Sesión</button>
                <button type="reset" class="btn-reset">Borrar</button>
            </div>