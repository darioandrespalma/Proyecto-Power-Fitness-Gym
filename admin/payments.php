<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar autenticación y rol de administrador
if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$gym_name = getSetting('gym_name', 'Power Fitness Gym');

// Obtener lista de pagos
try {
    $stmt = $pdo->query("
        SELECT p.*, u.name as user_name, m.name as membership_name
        FROM payments p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN memberships m ON p.membership_id = m.id
        ORDER BY p.payment_date DESC
    ");
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error al cargar pagos";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - <?php echo $gym_name; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <nav class="navbar">
            <a href="dashboard.php" class="logo"><?php echo $gym_name; ?> - Admin</a>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="members.php" class="nav-link">Miembros</a></li>
                <li><a href="payments.php" class="nav-link">Pagos</a></li>
                <li><a href="classes.php" class="nav-link">Clases</a></li>
                <li><a href="trainers.php" class="nav-link">Entrenadores</a></li>
                <li><a href="../process/logout.php" class="nav-link">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Payments Content -->
    <section class="admin-content">
        <div class="container">
            <div class="admin-header-section">
                <h1>Gestión de Pagos</h1>
                <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID Transacción</th>
                            <th>Usuario</th>
                            <th>Membresía</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($payment['user_name'] ?: 'Usuario no registrado'); ?></td>
                            <td><?php echo htmlspecialchars($payment['membership_name'] ?: 'N/A'); ?></td>
                            <td><?php echo formatCurrency($payment['amount']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></td>
                            <td>
                                <span class="status-<?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>