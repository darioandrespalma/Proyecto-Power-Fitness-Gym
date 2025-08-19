<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'gym_project');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración del sitio
define('SITE_NAME', 'Power Fitness Gym');
define('SITE_URL', 'http://localhost/gym-project');
define('ADMIN_EMAIL', 'admin@gym.com');

// Configuración de seguridad
define('SECRET_KEY', 'gym_project_secret_key_2024');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configuración de archivos
define('UPLOAD_PATH', 'assets/uploads/');
define('PROFILE_PICS_PATH', 'assets/uploads/profile-pics/');

// Configuración de moneda
define('CURRENCY', 'MXN');
define('CURRENCY_SYMBOL', '$');

// Iniciar sesión
session_start();

// Función para obtener configuración del sistema
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}
?>