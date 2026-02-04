<?php
// setup_admin.php - Script para crear la cuenta del administrador
// IMPORTANTE: Ejecuta este archivo UNA SOLA VEZ y luego BÓRRALO por seguridad

require_once 'auth_config.php';
require_once 'auth_functions.php';

// Datos del administrador
$adminEmail = 'abrahanmoises987@gmail.com';
$adminPassword = '92127026';
$securityQuestion = '¿Cuál es el nombre de tu primera mascota?';
$securityAnswer = 'admin'; // Cámbialo por algo que solo tú sepas

// Verificar si ya existe
if (userExists($usersFile, $adminEmail)) {
    die('❌ El usuario administrador ya existe. Por seguridad, BORRA este archivo (setup_admin.php)');
}

// Crear cuenta de administrador
$result = registerUser(
    $usersFile, 
    $adminEmail, 
    $adminPassword, 
    $securityQuestion, 
    $securityAnswer
);

if ($result['success']) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Administrador Creado</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: #000;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 500px;
                background: #111;
                border: 2px solid #51cf66;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
            }
            .icon {
                font-size: 64px;
                margin-bottom: 20px;
            }
            h1 {
                color: #51cf66;
                margin-bottom: 16px;
            }
            .info {
                background: #1a1a1a;
                border: 1px solid #333;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                text-align: left;
            }
            .info-item {
                margin-bottom: 12px;
                font-size: 14px;
            }
            .info-label {
                color: #888;
                display: inline-block;
                width: 120px;
            }
            .info-value {
                color: #51cf66;
            }
            .warning {
                background: #2d1b1b;
                border: 1px solid #ff6b6b;
                color: #ff6b6b;
                padding: 16px;
                border-radius: 8px;
                margin-top: 20px;
                font-size: 14px;
                font-weight: bold;
            }
            .btn {
                display: inline-block;
                background: #0066cc;
                color: #fff;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 6px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="icon">✅</div>
            <h1>Cuenta de Administrador Creada</h1>
            <p style="color: #888; margin-bottom: 24px;">Tu cuenta de administrador ha sido configurada exitosamente</p>
            
            <div class="info">
                <div class="info-item">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value">' . htmlspecialchars($adminEmail) . '</span>
                </div>
                <div class="info-item">
                    <span class="info-label">👑 Rol:</span>
                    <span class="info-value">Administrador</span>
                </div>
                <div class="info-item">
                    <span class="info-label">🔑 API Keys:</span>
                    <span class="info-value">Ilimitadas</span>
                </div>
                <div class="info-item">
                    <span class="info-label">⚡ Rate Limit:</span>
                    <span class="info-value">1000 req/min</span>
                </div>
            </div>
            
            <div class="warning">
                ⚠️ IMPORTANTE: Por seguridad, BORRA el archivo "setup_admin.php" de tu servidor AHORA
            </div>
            
            <a href="login.php" class="btn">Ir a Iniciar Sesión</a>
        </div>
    </body>
    </html>';
} else {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error</title>
        <style>
            body {
                font-family: sans-serif;
                background: #000;
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 500px;
                background: #2d1b1b;
                border: 2px solid #ff6b6b;
                border-radius: 12px;
                padding: 40px;
                text-align: center;
            }
            h1 {
                color: #ff6b6b;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>❌ Error</h1>
            <p>' . htmlspecialchars($result['error']) . '</p>
        </div>
    </body>
    </html>';
}
?>