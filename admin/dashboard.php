<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar autenticación y rol de administrador
if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$gym_name = getSetting('gym_name', 'Power Fitness Gym');

// Estadísticas del sistema
try {
    // Total de miembros
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'member' AND status = 'active'");
    $total_members = $stmt->fetch()['total'];
    
    // Miembros activos este mes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM members WHERE start_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $new_members = $stmt->fetch()['total'];
    
    // Pagos este mes
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(amount) as total_amount FROM payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND status = 'completed'");
    $payments_data = $stmt->fetch();
    $total_payments = $payments_data['total'];
    $payments_amount = $payments_data['total_amount'] ?: 0;
    
    // Clases programadas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM classes WHERE schedule >= NOW() AND status = 'active'");
    $upcoming_classes = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $error_message = "Error al cargar estadísticas";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - <?php echo $gym_name; ?></title>
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

    <!-- Dashboard Content -->
    <section class="admin-dashboard">
        <div class="container">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3><?php echo $total_members; ?></h3>
                        <p>Miembros Totales</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🆕</div>
                    <div class="stat-content">
                        <h3><?php echo $new_members; ?></h3>
                        <p>Nuevos Miembros</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3><?php echo formatCurrency($payments_amount); ?></h3>
                        <p>Ingresos este mes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🗓️</div>
                    <div class="stat-content">
                        <h3><?php echo $upcoming_classes; ?></h3>
                        <p>Clases Programadas</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Actividad Reciente</h2>
                <div class="activity-grid">
                    <div class="activity-card">
                        <h3>Pagos Recientes</h3>
                        <div class="activity-list">
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT p.*, u.name as user_name, m.name as membership_name FROM payments p LEFT JOIN users u ON p.user_id = u.id LEFT JOIN memberships m ON p.membership_id = m.id WHERE p.status = 'completed' ORDER BY p.payment_date DESC LIMIT 5");
                                while ($payment = $stmt->fetch()) {
                                    echo '<div class="activity-item">';
                                    echo '<div class="activity-info">';
                                    echo '<strong>' . htmlspecialchars($payment['user_name']) . '</strong>';
                                    echo '<span>' . htmlspecialchars($payment['membership_name']) . '</span>';
                                    echo '</div>';
                                    echo '<div class="activity-amount">' . formatCurrency($payment['amount']) . '</div>';
                                    echo '</div>';
                                }
                            } catch (PDOException $e) {
                                echo '<p>Error al cargar pagos</p>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="activity-card">
                        <h3>Nuevos Miembros</h3>
                        <div class="activity-list">
                            <?php
                            try {
                                $stmt = $pdo->query("SELECT name, email, created_at FROM users WHERE role = 'member' ORDER BY created_at DESC LIMIT 5");
                                while ($member = $stmt->fetch()) {
                                    echo '<div class="activity-item">';
                                    echo '<div class="activity-info">';
                                    echo '<strong>' . htmlspecialchars($member['name']) . '</strong>';
                                    echo '<span>' . htmlspecialchars($member['email']) . '</span>';
                                    echo '</div>';
                                    echo '<div class="activity-date">' . date('d/m/Y', strtotime($member['created_at'])) . '</div>';
                                    echo '</div>';
                                }
                            } catch (PDOException $e) {
                                echo '<p>Error al cargar miembros</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>