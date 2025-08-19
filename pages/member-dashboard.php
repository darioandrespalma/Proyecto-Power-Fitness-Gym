<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar autenticación
if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$gym_name = getSetting('gym_name', 'Power Fitness Gym');

// Obtener información del miembro
try {
    $stmt = $pdo->prepare("
        SELECT u.*, m.membership_id, m.start_date, m.end_date, mem.name as membership_name, mem.price
        FROM users u
        LEFT JOIN members m ON u.id = m.user_id
        LEFT JOIN memberships mem ON m.membership_id = mem.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        redirect('../login.php');
    }
} catch (PDOException $e) {
    $error_message = "Error al cargar la información del usuario";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo $gym_name; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Header -->
    <header>
        <nav class="navbar">
            <a href="home.php" class="logo"><?php echo $gym_name; ?></a>
            <ul class="nav-menu">
                <li><a href="home.php" class="nav-link">Inicio</a></li>
                <li><a href="pricing.php" class="nav-link">Planes</a></li>
                <li><a href="payment.php" class="nav-link">Pago</a></li>
                <li><a href="contact.php" class="nav-link">Contacto</a></li>
                <li><a href="../process/logout.php" class="nav-link">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <!-- Dashboard Section -->
    <section class="auth-section">
        <div class="container">
            <h1>Mi Perfil</h1>
            
            <div class="dashboard-content">
                <div class="profile-card">
                    <h2>Información Personal</h2>
                    <div class="profile-info">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                        <p><strong>Estado:</strong> 
                            <span class="status-<?php echo $user['status']; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="membership-card">
                    <h2>Membresía Actual</h2>
                    <?php if ($user['membership_id']): ?>
                        <div class="membership-info">
                            <p><strong>Plan:</strong> <?php echo htmlspecialchars($user['membership_name']); ?></p>
                            <p><strong>Precio:</strong> <?php echo formatCurrency($user['price']); ?></p>
                            <p><strong>Inicio:</strong> <?php echo formatDate($user['start_date']); ?></p>
                            <p><strong>Fin:</strong> <?php echo formatDate($user['end_date']); ?></p>
                            <p><strong>Estado:</strong> 
                                <?php 
                                $today = date('Y-m-d');
                                if ($today > $user['end_date']) {
                                    echo '<span class="status-expired">Expirada</span>';
                                } else {
                                    echo '<span class="status-active">Activa</span>';
                                }
                                ?>
                            </p>
                            <a href="payment.php?renew=1" class="btn btn-primary">Renovar Membresía</a>
                        </div>
                    <?php else: ?>
                        <p>No tienes una membresía activa</p>
                        <a href="payment.php" class="btn btn-primary">Comprar Membresía</a>
                    <?php endif; ?>
                </div>
                
                <div class="actions-card">
                    <h2>Acciones Rápidas</h2>
                    <div class="actions-grid">
                        <a href="payment.php" class="action-button">
                            <div class="action-icon">💳</div>
                            <div class="action-text">Renovar Membresía</div>
                        </a>
                        <a href="contact.php" class="action-button">
                            <div class="action-icon">✉️</div>
                            <div class="action-text">Contactar Soporte</div>
                        </a>
                        <a href="../process/logout.php" class="action-button">
                            <div class="action-icon">🚪</div>
                            <div class="action-text">Cerrar Sesión</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>