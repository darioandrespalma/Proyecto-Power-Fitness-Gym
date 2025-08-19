<?php
// Funciones de utilidad reutilizables

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../login.php');
    }
}

function requireAdmin() {
    if (!isLoggedIn() || getUserRole() !== 'admin') {
        redirect('../index.php');
    }
}

function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyToken($token) {
    // Implementar verificación de token CSRF si es necesario
    return true;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9\-\+\s\(\)]{7,20}$/', $phone);
}

function validatePassword($password) {
    // Al menos 6 caracteres
    return strlen($password) >= 6;
}

function validateCardNumber($card_number) {
    // Eliminar espacios y verificar longitud
    $clean_number = str_replace(' ', '', $card_number);
    return strlen($clean_number) >= 13 && strlen($clean_number) <= 19 && ctype_digit($clean_number);
}

function validateExpiryDate($expiry_date) {
    // Formato MM/AA
    return preg_match('/^\d{2}\/\d{2}$/', $expiry_date);
}

function validateCVV($cvv) {
    // 3 o 4 dígitos
    return preg_match('/^\d{3,4}$/', $cvv);
}

function validateFileSize($file_size, $max_size = 5242880) { // 5MB
    return $file_size <= $max_size;
}

function validateFileType($file_type, $allowed_types = ['application/pdf']) {
    return in_array($file_type, $allowed_types);
}

// La función getSetting() ya está definida en config.php
?>