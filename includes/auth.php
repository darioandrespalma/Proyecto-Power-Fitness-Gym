<?php
// includes/auth.php

/**
 * Registra un nuevo usuario en la base de datos.
 *
 * @param PDO $pdo Objeto de conexión a la base de datos.
 * @param string $name Nombre del usuario.
 * @param string $email Email del usuario.
 * @param string $password Contraseña en texto plano.
 * @param string $phone Teléfono del usuario.
 * @return bool|string True si el registro es exitoso, o un string con el mensaje de error si falla.
 */
function registerUser($pdo, $name, $email, $password, $phone) {
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return "El correo electrónico ya está registrado.";
    }

    // Hashear la contraseña
    $password_hash = generatePasswordHash($password);

    // Insertar el nuevo usuario
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'member', 'active')");
        $stmt->execute([$name, $email, $password_hash, $phone]);
        return true;
    } catch (PDOException $e) {
        // Podrías loguear el error real: error_log($e->getMessage());
        return "Hubo un error al crear la cuenta. Por favor, inténtalo de nuevo.";
    }
}

/**
 * Valida las credenciales de un usuario e inicia la sesión.
 *
 * @param PDO $pdo Objeto de conexión a la base de datos.
 * @param string $email Email del usuario.
 * @param string $password Contraseña en texto plano.
 * @return bool True si el login es exitoso, false si falla.
 */
function loginUser($pdo, $email, $password) {
    // Buscar al usuario por su email
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verificar si el usuario existe y la contraseña es correcta
    if ($user && verifyPassword($password, $user['password'])) {
        // Verificar si la cuenta está activa
        if ($user['status'] !== 'active') {
            return false; // O un mensaje de error específico
        }

        // Regenerar el ID de sesión por seguridad
        session_regenerate_id(true);

        // Guardar datos en la sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        return true;
    }

    return false;
}

/**
 * Cierra la sesión del usuario actual.
 */
function logoutUser() {
    // Limpiar todas las variables de sesión
    $_SESSION = [];

    // Destruir la sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}
?>