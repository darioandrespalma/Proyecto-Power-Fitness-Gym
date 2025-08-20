<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar datos
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Las contraseñas no coinciden.";
        redirect('../register.php');
    }
    
    // Llama a la función centralizada de registro
    $result = registerUser($pdo, $name, $email, $password, $phone);

    if ($result === true) {
        // Opcional: Iniciar sesión automáticamente después del registro
        loginUser($pdo, $email, $password);
        redirect('../pages/profile.php'); // O a una página de bienvenida
    } else {
        // La función devuelve un mensaje de error específico
        $_SESSION['register_error'] = $result;
        redirect('../register.php');
    }
}
?>