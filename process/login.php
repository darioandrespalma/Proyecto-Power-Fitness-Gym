<?php
require_once '../includes/database.php'; // Carga config.php (inicia sesión) y la conexión PDO
require_once '../includes/functions.php';
require_once '../includes/auth.php'; // Carga las nuevas funciones de autenticación

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Por favor, ingresa tu correo y contraseña.";
    } else {
        // Llama a la función centralizada de login
        if (loginUser($pdo, $email, $password)) {
            // La función loginUser ya guarda los datos en la sesión
            if (getUserRole() === 'admin') {
                redirect('../admin/dashboard.php');
            } else {
                redirect('../pages/profile.php'); // O a la página de perfil del miembro
            }
        } else {
            $error_message = "Credenciales incorrectas o cuenta inactiva.";
        }
    }
}

// Si hay un error, redirigir de vuelta al formulario de login con un mensaje
if (!empty($error_message)) {
    // Puedes usar sesiones para pasar el mensaje de error de forma segura
    $_SESSION['login_error'] = $error_message;
    redirect('../login.php');
}
?>