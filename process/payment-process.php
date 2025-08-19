<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar datos
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $membership_id = (int)$_POST['membership_id'];
        $payment_method = sanitizeInput($_POST['payment_method']);
        
        // Validar datos requeridos
        if (empty($name) || empty($email) || empty($phone) || empty($membership_id) || empty($payment_method)) {
            throw new Exception('Todos los campos son requeridos');
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        // Verificar que el plan exista
        $stmt = $pdo->prepare("SELECT * FROM memberships WHERE id = ? AND status = 'active'");
        $stmt->execute([$membership_id]);
        $membership = $stmt->fetch();

        if (!$membership) {
            throw new Exception('Plan no válido');
        }

        // Procesar según método de pago
        $transaction_id = '';
        $status = 'pending';
        $upload_path = '';

        if ($payment_method === 'credit_card' || $payment_method === 'debit_card') {
            // Simular procesamiento de tarjeta
            $card_number = sanitizeInput($_POST['card_number']);
            $expiry_date = sanitizeInput($_POST['expiry_date']);
            $cvv = sanitizeInput($_POST['cvv']);
            
            // Validar datos de tarjeta
            if (empty($card_number) || empty($expiry_date) || empty($cvv)) {
                throw new Exception('Por favor completa todos los datos de la tarjeta');
            }
            
            if (strlen(str_replace(' ', '', $card_number)) < 16) {
                throw new Exception('Número de tarjeta inválido');
            }
            
            if (!preg_match('/^\d{2}\/\d{2}$/', $expiry_date)) {
                throw new Exception('Fecha de expiración inválida');
            }
            
            if (strlen($cvv) < 3) {
                throw new Exception('CVV inválido');
            }
            
            // Simular aprobación de tarjeta (en producción usar pasarela real)
            $transaction_id = 'TXN' . time() . rand(1000, 9999);
            $status = 'completed';
            
        } elseif ($payment_method === 'bank_transfer') {
            // Procesar transferencia bancaria
            if (!isset($_FILES['transfer_receipt']) || $_FILES['transfer_receipt']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Por favor sube el comprobante de transferencia');
            }
            
            $file = $_FILES['transfer_receipt'];
            
            // Validar tipo de archivo
            $allowed_types = ['application/pdf'];
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Solo se aceptan archivos PDF');
            }
            
            // Validar tamaño (máximo 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new Exception('El archivo es demasiado grande (máximo 5MB)');
            }
            
            // Generar nombre único para el archivo
            $upload_dir = '../assets/uploads/payments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_filename = 'transfer_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Error al subir el comprobante');
            }
            
            $transaction_id = 'TRANSFER' . time() . rand(1000, 9999);
            $status = 'pending'; // Pendiente de verificación
            
        } else {
            throw new Exception('Método de pago no válido');
        }

        // Registrar el pago en la base de datos
        $amount = $membership['price'];
        $user_id = null;
        
        // Si el usuario está logueado, asociar el pago a su cuenta
        if (isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
        }
        
        $stmt = $pdo->prepare("INSERT INTO payments (user_id, membership_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $membership_id, $amount, $payment_method, $transaction_id, $status]);

        // Si es un pago con tarjeta completado, actualizar membresía del usuario
        if (($payment_method === 'credit_card' || $payment_method === 'debit_card') && $status === 'completed' && isLoggedIn()) {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+$membership[duration_days] days"));
            
            // Verificar si el usuario ya tiene membresía
            $stmt = $pdo->prepare("SELECT id FROM members WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                // Actualizar membresía existente
                $stmt = $pdo->prepare("UPDATE members SET membership_id = ?, start_date = ?, end_date = ? WHERE user_id = ?");
                $stmt->execute([$membership_id, $start_date, $end_date, $_SESSION['user_id']]);
            } else {
                // Crear nueva membresía
                $stmt = $pdo->prepare("INSERT INTO members (user_id, membership_id, start_date, end_date) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $membership_id, $start_date, $end_date]);
            }
        }

        // Registrar contacto del usuario
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
        $stmt->execute([
            $name, 
            $email, 
            $phone, 
            'Pago de membresía', 
            'Pago realizado por membresía: ' . $membership['name'] . ' - Método: ' . $payment_method
        ]);

        // Redirigir con mensaje de éxito
        if ($status === 'completed') {
            $_SESSION['payment_success'] = '¡Pago procesado exitosamente! Tu membresía ha sido activada.';
        } else {
            $_SESSION['payment_success'] = '¡Pago registrado exitosamente! Estamos verificando tu transferencia. Recibirás un email de confirmación pronto.';
        }
        
        header('Location: ../pages/payment.php?success=1');
        exit();

    } catch (Exception $e) {
        $_SESSION['payment_error'] = 'Error: ' . $e->getMessage();
        header('Location: ../pages/payment.php');
        exit();
    }
} else {
    header('Location: ../pages/payment.php');
    exit();
}
?>