<?php

// auth_config.php - Configuración del sistema de autenticación

// Iniciar sesión

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}

// Archivo donde se guardarán los usuarios

$usersFile = 'users.json';

// Configuración de seguridad

$passwordMinLength = 6;

$sessionTimeout = 3600; // 1 hora en segundos

// Preguntas de seguridad disponibles

$securityQuestions = [

    '¿Cuál es el nombre de tu primera mascota?',

    '¿En qué ciudad naciste?',

    '¿Cuál es tu comida favorita?',

    '¿Cuál es el nombre de tu mejor amigo de la infancia?',

    '¿Cuál es tu película favorita?',

    '¿Cuál es el nombre de tu madre?',

    '¿Cuál fue el nombre de tu primera escuela?',

    '¿Cuál es tu color favorito?',

    '¿En qué año te graduaste?',

    '¿Cuál es tu libro favorito?'

];

// Función para verificar si el usuario está logueado

function isLoggedIn() {

    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

}

// Función para obtener el usuario actual

function getCurrentUser() {

    return $_SESSION['user_email'] ?? null;

}

// Función para verificar timeout de sesión

function checkSessionTimeout() {

    if (isset($_SESSION['last_activity'])) {

        global $sessionTimeout;

        if (time() - $_SESSION['last_activity'] > $sessionTimeout) {

            session_unset();

            session_destroy();

            return false;

        }

    }

    $_SESSION['last_activity'] = time();

    return true;

}

// Función para requerir login

function requireLogin() {

    if (!isLoggedIn() || !checkSessionTimeout()) {

        header('Location: login.php');

        exit;

    }

}

?>