<?php
require_once 'includes/database.php';
require_once 'includes/functions.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
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
                redirect('admin/dashboard.php');
            } else {
                redirect('pages/member-dashboard.php');
            }
        } else {
            throw new Exception('Credenciales incorrectas');
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

$gym_name = getSetting('gym_name', 'Power Fitness Gym');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo $gym_name; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="auth-section">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Iniciar Sesión</h1>
                <p>Accede a tu cuenta de <?php echo $gym_name; ?></p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>
            
            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p><a href="pages/home.php">Volver al inicio</a></p>
            </div>
        </div>
    </div>
</body>
</html>