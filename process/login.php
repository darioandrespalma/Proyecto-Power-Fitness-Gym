<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : '../pages/member-dashboard.php';
        
        if (empty($email) || empty($password)) {
            throw new Exception('Por favor completa todos los campos');
        }
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }
        
        // Buscar usuario
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Regenerar ID de sesión para seguridad
            session_regenerate_id(true);
            
            // Redirigir según rol
            if ($user['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            } else {
                redirect($redirect_url);
            }
        } else {
            throw new Exception('Credenciales incorrectas');
        }
        
    } catch (Exception $e) {
        $_SESSION['login_error'] = $e->getMessage();
        redirect('../login.php');
    }
} else {
    redirect('../login.php');
}
?>