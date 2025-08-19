<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar datos
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $subject = sanitizeInput($_POST['subject']);
        $message = sanitizeInput($_POST['message']);

        // Validar datos requeridos
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            throw new Exception('Todos los campos marcados con * son requeridos');
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Guardar mensaje en la base de datos
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);

        // Redirigir con mensaje de éxito
        $_SESSION['contact_success'] = '¡Mensaje enviado exitosamente! Nos pondremos en contacto contigo pronto.';
        header('Location: ../pages/contact.php#contact-form');
        exit();

    } catch (Exception $e) {
        $_SESSION['contact_error'] = 'Error: ' . $e->getMessage();
        header('Location: ../pages/contact.php#contact-form');
        exit();
    }
} else {
    header('Location: ../pages/contact.php');
    exit();
}
?>