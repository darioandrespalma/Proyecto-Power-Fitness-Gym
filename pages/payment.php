<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$gym_name = getSetting('gym_name', 'Power Fitness Gym');
$selected_membership = null;
$error_message = '';
$success_message = '';

// Manejar mensajes flash
if (isset($_SESSION['payment_success'])) {
    $success_message = $_SESSION['payment_success'];
    unset($_SESSION['payment_success']);
}

if (isset($_SESSION['payment_error'])) {
    $error_message = $_SESSION['payment_error'];
    unset($_SESSION['payment_error']);
}

// Verificar si se seleccionó un plan
if (isset($_GET['membership'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM memberships WHERE id = ? AND status = 'active'");
        $stmt->execute([$_GET['membership']]);
        $selected_membership = $stmt->fetch();
    } catch (PDOException $e) {
        $error_message = 'Error al cargar el plan seleccionado.';
    }
}

// Si el usuario está logueado, obtener su información
$user_info = null;
if (isLoggedIn()) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_info = $stmt->fetch();
    } catch (PDOException $e) {
        // No hacer nada, continuar sin información del usuario
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago - <?php echo $gym_name; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/payment.css">
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Payment Section -->
    <section class="payment-section">
        <div class="container">
            <h1>Completar Pago</h1>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="payment-content">
                <!-- Formulario de Pago -->
                <div class="payment-form">
                    <form action="../process/payment-process.php" method="POST" id="paymentForm" enctype="multipart/form-data">
                        <h2>Información de Contacto</h2>
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input type="text" id="name" name="name" value="<?php echo $user_info ? htmlspecialchars($user_info['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo $user_info ? htmlspecialchars($user_info['email']) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Teléfono *</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo $user_info ? htmlspecialchars($user_info['phone']) : ''; ?>" required>
                            </div>
                        </div>

                        <h2>Información de Pago</h2>
                        <div class="form-group">
                            <label for="payment_method">Método de Pago *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">-- Seleccionar Método --</option>
                                <option value="credit_card">Tarjeta de Crédito</option>
                                <option value="debit_card">Tarjeta de Débito</option>
                                <option value="bank_transfer">Transferencia Bancaria</option>
                            </select>
                        </div>

                        <!-- Campos para tarjeta de crédito/débito -->
                        <div id="cardFields" style="display: none;">
                            <div class="form-group">
                                <label for="card_number">Número de Tarjeta *</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_date">Fecha de Expiración *</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/AA">
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="123">
                                </div>
                            </div>
                        </div>

                        <!-- Campo para transferencia bancaria -->
                        <div id="transferFields" style="display: none;">
                            <div class="form-group">
                                <label for="transfer_receipt">Comprobante de Transferencia (PDF) *</label>
                                <input type="file" id="transfer_receipt" name="transfer_receipt" accept=".pdf">
                                <small>Solo se aceptan archivos PDF de hasta 5MB</small>
                            </div>
                        </div>

                        <?php if ($selected_membership): ?>
                            <input type="hidden" name="membership_id" value="<?php echo $selected_membership['id']; ?>">
                        <?php else: ?>
                            <div class="form-group">
                                <label for="membership_id">Seleccionar Plan *</label>
                                <select id="membership_id" name="membership_id" required>
                                    <option value="">-- Seleccionar Plan --</option>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT * FROM memberships WHERE status = 'active' ORDER BY price");
                                        while ($plan = $stmt->fetch()) {
                                            echo '<option value="' . $plan['id'] . '">' . htmlspecialchars($plan['name']) . ' - ' . CURRENCY_SYMBOL . number_format($plan['price'], 2) . '</option>';
                                        }
                                    } catch (PDOException $e) {
                                        echo '<option value="">Error al cargar planes</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-block">Completar Pago</button>
                    </form>
                </div>

                <!-- Resumen del Pedido -->
                <div class="order-summary">
                    <h2>Resumen del Pedido</h2>
                    <?php if ($selected_membership): ?>
                        <div class="summary-item">
                            <h3><?php echo htmlspecialchars($selected_membership['name']); ?></h3>
                            <p><?php echo htmlspecialchars($selected_membership['description']); ?></p>
                            <div class="price"><?php echo CURRENCY_SYMBOL . number_format($selected_membership['price'], 2); ?></div>
                        </div>
                    <?php else: ?>
                        <div class="summary-item">
                            <p>Selecciona un plan para ver el resumen</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="summary-total">
                        <strong>Total: <?php echo $selected_membership ? CURRENCY_SYMBOL . number_format($selected_membership['price'], 2) : CURRENCY_SYMBOL . '0.00'; ?></strong>
                    </div>
                    
                    <div class="security-info">
                        <p>🔒 Pago seguro con encriptación SSL</p>
                        <p>💳 Aceptamos todas las tarjetas principales</p>
                        <p>📄 Transferencias bancarias aceptadas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/payment.js"></script>
</body>
</html>