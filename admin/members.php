<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar autenticación y rol de administrador
if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$gym_name = getSetting('gym_name', 'Power Fitness Gym');

// Manejar acciones
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];
    
    try {
        if ($action === 'activate') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'member'");
            $stmt->execute([$user_id]);
        } elseif ($action === 'deactivate') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = ? AND role = 'member'");
            $stmt->execute([$user_id]);
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'member'");
            $stmt->execute([$user_id]);
        }
    } catch (PDOException $e) {
        $error_message = "Error al procesar la acción";
    }
}

// Obtener lista de miembros
try {
    $stmt = $pdo->query("
        SELECT u.*, m.membership_id, mem.name as membership_name, mem.price
        FROM users u
        LEFT JOIN members m ON u.id = m.user_id
        LEFT JOIN memberships mem ON m.membership_id = mem.id
        WHERE u.role = 'member'
        ORDER BY u.created_at DESC
    ");
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error al cargar miembros";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Miembros - <?php echo $gym_name; ?></title>
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

    <!-- Members Content -->
    <section class="admin-content">
        <div class="container">
            <div class="admin-header-section">
                <h1>Gestión de Miembros</h1>
                <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
            </div>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Membresía</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo htmlspecialchars($member['phone']); ?></td>
                            <td>
                                <?php 
                                echo $member['membership_name'] ? 
                                    htmlspecialchars($member['membership_name']) . ' (' . formatCurrency($member['price']) . ')' : 
                                    'Sin membresía';
                                ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $member['status']; ?>">
                                    <?php echo ucfirst($member['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($member['created_at'])); ?></td>
                            <td>
                                <?php if ($member['status'] === 'active'): ?>
                                    <a href="?action=deactivate&id=<?php echo $member['id']; ?>" class="btn btn-warning btn-sm">Desactivar</a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?php echo $member['id']; ?>" class="btn btn-success btn-sm">Activar</a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $member['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este miembro?')">Eliminar</a>
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