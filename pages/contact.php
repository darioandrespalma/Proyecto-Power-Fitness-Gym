<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$gym_name = getSetting('gym_name', 'Power Fitness Gym');
$gym_phone = getSetting('gym_phone', '+1 (555) 123-4567');
$gym_email = getSetting('gym_email', 'info@powerfitness.com');
$gym_address = getSetting('gym_address', 'Av. Deportiva 123, Ciudad Fitness');

$success_message = '';
$error_message = '';

// Manejar mensajes flash
if (isset($_SESSION['contact_success'])) {
    $success_message = $_SESSION['contact_success'];
    unset($_SESSION['contact_success']);
}

if (isset($_SESSION['contact_error'])) {
    $error_message = $_SESSION['contact_error'];
    unset($_SESSION['contact_error']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - <?php echo $gym_name; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="container">
            <h1>Contáctanos</h1>
            <p class="subtitle">Estamos aquí para ayudarte. Envíanos un mensaje y te responderemos pronto.</p>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="contact-content">
                <!-- Formulario de Contacto -->
                <div class="contact-form">
                    <form action="../process/contact-process.php" method="POST">
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input type="tel" id="phone" name="phone">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Asunto *</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Mensaje *</label>
                            <textarea id="message" name="message" rows="6" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                    </form>
                </div>

                <!-- Información de Contacto -->
                <div class="contact-info">
                    <h2>Información de Contacto</h2>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-details">
                            <h3>Dirección</h3>
                            <p><?php echo htmlspecialchars($gym_address); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-details">
                            <h3>Teléfono</h3>
                            <p><?php echo htmlspecialchars($gym_phone); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-details">
                            <h3>Email</h3>
                            <p><?php echo htmlspecialchars($gym_email); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div class="contact-details">
                            <h3>Horarios</h3>
                            <p>Lunes a Viernes: 6:00 AM - 10:00 PM</p>
                            <p>Sábado y Domingo: 8:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mapa (opcional) -->
    <section class="map-section">
        <div class="container">
            <h2>Encuéntranos</h2>
            <div class="map-placeholder">
                <p>Mapa de ubicación del gimnasio</p>
                <!-- Aquí iría el mapa real con Google Maps o similar -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>