<?php
// recover.php - Recuperar contraseña

require_once 'auth_config.php';
require_once 'auth_functions.php';

$step = $_GET['step'] ?? 'email';
$message = '';
$messageType = '';
$email = '';
$securityQuestion = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Paso 1: Verificar email y mostrar pregunta
    if ($action === 'verify_email') {
        $email = $_POST['email'] ?? '';
        
        if (userExists($usersFile, $email)) {
            $result = getSecurityQuestion($usersFile, $email);
            
            if ($result['success']) {
                $step = 'question';
                $securityQuestion = $result['question'];
            } else {
                $message = 'Error al obtener la pregunta de seguridad';
                $messageType = 'error';
            }
        } else {
            $message = 'Este email no está registrado';
            $messageType = 'error';
        }
    }
    
    // Paso 2: Verificar respuesta de seguridad
    elseif ($action === 'verify_answer') {
        $email = $_POST['email'] ?? '';
        $answer = $_POST['security_answer'] ?? '';
        
        $result = verifySecurityAnswer($usersFile, $email, $answer);
        
        if ($result['success']) {
            $step = 'reset';
        } else {
            $step = 'question';
            $securityQuestion = getSecurityQuestion($usersFile, $email)['question'];
            $message = 'Respuesta incorrecta. Inténtalo de nuevo.';
            $messageType = 'error';
        }
    }
    
    // Paso 3: Restablecer contraseña
    elseif ($action === 'reset_password') {
        $email = $_POST['email'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword !== $confirmPassword) {
            $step = 'reset';
            $message = 'Las contraseñas no coinciden';
            $messageType = 'error';
        } else {
            $result = resetPassword($usersFile, $email, $newPassword);
            
            if ($result['success']) {
                $step = 'success';
            } else {
                $step = 'reset';
                $message = $result['error'];
                $messageType = 'error';
            }
        }
    }
}

// Obtener pregunta de seguridad si estamos en ese paso
if ($step === 'question' && !empty($_POST['email'])) {
    $email = $_POST['email'];
    $result = getSecurityQuestion($usersFile, $email);
    if ($result['success']) {
        $securityQuestion = $result['question'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #000;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .recover-container {
            max-width: 400px;
            width: 100%;
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            padding: 30px;
        }
        
        .logo {
            text-align: center;
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .title {
            text-align: center;
            font-size: 24px;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .subtitle {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        label {
            display: block;
            font-size: 13px;
            color: #ccc;
            margin-bottom: 6px;
        }
        
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: #000;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 12px;
            color: #fff;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #0066cc;
        }
        
        .btn {
            width: 100%;
            background: #0066cc;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #0052a3;
        }
        
        .message {
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        
        .message.error {
            background: #2d1b1b;
            border: 1px solid #5c2626;
            color: #ff6b6b;
        }
        
        .message.success {
            background: #1b2d1b;
            border: 1px solid #265c26;
            color: #51cf66;
        }
        
        .link {
            color: #0066cc;
            text-decoration: none;
            font-size: 13px;
        }
        
        .link:hover {
            text-decoration: underline;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-bottom: 2px solid #333;
            font-size: 12px;
            color: #666;
        }
        
        .step.active {
            border-color: #0066cc;
            color: #0066cc;
        }
        
        .step.completed {
            border-color: #51cf66;
            color: #51cf66;
        }
        
        .success-icon {
            font-size: 64px;
            text-align: center;
            margin: 20px 0;
        }
        
        .info-box {
            background: #1a1a1a;
            border: 1px solid #333;
            border-radius: 6px;
            padding: 12px;
            font-size: 13px;
            color: #888;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="recover-container">
        <div class="logo">🔓</div>
        <h1 class="title">Recuperar Contraseña</h1>
        <p class="subtitle">Sigue los pasos para restablecer tu contraseña</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Indicador de pasos -->
        <div class="steps">
            <div class="step <?php echo $step === 'email' ? 'active' : ($step !== 'email' ? 'completed' : ''); ?>">
                1. Email
            </div>
            <div class="step <?php echo $step === 'question' ? 'active' : ($step === 'reset' || $step === 'success' ? 'completed' : ''); ?>">
                2. Pregunta
            </div>
            <div class="step <?php echo $step === 'reset' ? 'active' : ($step === 'success' ? 'completed' : ''); ?>">
                3. Nueva contraseña
            </div>
        </div>
        
        <!-- Paso 1: Ingresar email -->
        <?php if ($step === 'email'): ?>
            <form method="POST" action="">
                <input type="hidden" name="action" value="verify_email">
                
                <div class="form-group">
                    <label>Correo electrónico:</label>
                    <input type="email" name="email" placeholder="tu@email.com" required autofocus>
                </div>
                
                <button type