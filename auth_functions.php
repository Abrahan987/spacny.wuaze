<?php
// auth_functions.php - Funciones del sistema de autenticación

require_once 'auth_config.php';

// Función para cargar usuarios
function loadUsers($file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        return json_decode($content, true) ?: [];
    }
    return [];
}

// Función para guardar usuarios
function saveUsers($file, $users) {
    return file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

// Función para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para verificar si el usuario existe
function userExists($usersFile, $email) {
    $users = loadUsers($usersFile);
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return true;
        }
    }
    return false;
}

// Función para registrar un nuevo usuario
function registerUser($usersFile, $email, $password, $securityQuestion, $securityAnswer) {
    global $passwordMinLength;
    
    // Validaciones
    if (!validateEmail($email)) {
        return ['success' => false, 'error' => 'Email no válido'];
    }
    
    if (strlen($password) < $passwordMinLength) {
        return ['success' => false, 'error' => "La contraseña debe tener al menos $passwordMinLength caracteres"];
    }
    
    if (empty($securityAnswer)) {
        return ['success' => false, 'error' => 'Debes proporcionar una respuesta de seguridad'];
    }
    
    if (userExists($usersFile, $email)) {
        return ['success' => false, 'error' => 'Este email ya está registrado'];
    }
    
    // Crear usuario
    $users = loadUsers($usersFile);
    
    $newUser = [
        'id' => uniqid(),
        'email' => strtolower($email),
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'security_question' => $securityQuestion,
        'security_answer' => password_hash(strtolower(trim($securityAnswer)), PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s'),
        'last_login' => null
    ];
    
    $users[] = $newUser;
    
    if (saveUsers($usersFile, $users)) {
        return ['success' => true, 'message' => 'Cuenta creada exitosamente'];
    }
    
    return ['success' => false, 'error' => 'Error al crear la cuenta'];
}

// Función para iniciar sesión
function loginUser($usersFile, $email, $password) {
    $users = loadUsers($usersFile);
    
    foreach ($users as &$user) {
        if (strtolower($user['email']) === strtolower($email)) {
            if (password_verify($password, $user['password'])) {
                // Login exitoso
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['last_activity'] = time();
                
                // Actualizar último login
                $user['last_login'] = date('Y-m-d H:i:s');
                saveUsers($usersFile, $users);
                
                return ['success' => true, 'message' => 'Login exitoso'];
            } else {
                return ['success' => false, 'error' => 'Contraseña incorrecta'];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Usuario no encontrado'];
}

// Función para cerrar sesión
function logoutUser() {
    session_unset();
    session_destroy();
    return ['success' => true, 'message' => 'Sesión cerrada'];
}

// Función para obtener pregunta de seguridad
function getSecurityQuestion($usersFile, $email) {
    $users = loadUsers($usersFile);
    
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return [
                'success' => true,
                'question' => $user['security_question']
            ];
        }
    }
    
    return ['success' => false, 'error' => 'Usuario no encontrado'];
}

// Función para verificar respuesta de seguridad
function verifySecurityAnswer($usersFile, $email, $answer) {
    $users = loadUsers($usersFile);
    
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            if (password_verify(strtolower(trim($answer)), $user['security_answer'])) {
                return ['success' => true, 'user_id' => $user['id']];
            } else {
                return ['success' => false, 'error' => 'Respuesta incorrecta'];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Usuario no encontrado'];
}

// Función para restablecer contraseña
function resetPassword($usersFile, $email, $newPassword) {
    global $passwordMinLength;
    
    if (strlen($newPassword) < $passwordMinLength) {
        return ['success' => false, 'error' => "La contraseña debe tener al menos $passwordMinLength caracteres"];
    }
    
    $users = loadUsers($usersFile);
    
    foreach ($users as &$user) {
        if (strtolower($user['email']) === strtolower($email)) {
            $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if (saveUsers($usersFile, $users)) {
                return ['success' => true, 'message' => 'Contraseña restablecida exitosamente'];
            } else {
                return ['success' => false, 'error' => 'Error al restablecer contraseña'];
            }
        }
    }
    
    return ['success' => false, 'error' => 'Usuario no encontrado'];
}

// Función para obtener datos del usuario actual
function getCurrentUserData($usersFile) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $email = getCurrentUser();
    $users = loadUsers($usersFile);
    
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            // No devolver datos sensibles
            unset($user['password']);
            unset($user['security_answer']);
            return $user;
        }
    }
    
    return null;
}
?>