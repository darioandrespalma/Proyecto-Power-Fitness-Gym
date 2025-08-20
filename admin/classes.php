<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar autenticación y rol de administrador
if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$gym_name = getSetting('gym_name', 'Power Fitness Gym');
$page_title = 'Gestión de Clases';
$error_message = '';
$success_message = '';

// --- LÓGICA DE PROCESAMIENTO (CRUD) ---

// Manejar envío de formulario para agregar/editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $schedule = $_POST['schedule'];
    // ✅ CORRECCIÓN: Usar los nombres de columna correctos de la BD
    $duration_minutes = (int)$_POST['duration_minutes'];
    $trainer_id = (int)$_POST['trainer_id'];
    $capacity = (int)$_POST['capacity'];

    // Validación simple
    if (empty($name) || empty($schedule) || $duration_minutes <= 0 || $trainer_id <= 0 || $capacity <= 0) {
        $error_message = "Por favor, completa todos los campos requeridos.";
    } else {
        try {
            if ($class_id) {
                // ✅ CORRECCIÓN: Actualizar con los nombres de columna correctos
                $stmt = $pdo->prepare("UPDATE classes SET name = ?, description = ?, schedule = ?, duration_minutes = ?, trainer_id = ?, capacity = ? WHERE id = ?");
                $stmt->execute([$name, $description, $schedule, $duration_minutes, $trainer_id, $capacity, $class_id]);
                $success_message = "Clase actualizada correctamente.";
            } else {
                // ✅ CORRECCIÓN: Insertar con los nombres de columna correctos
                $stmt = $pdo->prepare("INSERT INTO classes (name, description, schedule, duration_minutes, trainer_id, capacity, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $description, $schedule, $duration_minutes, $trainer_id, $capacity]);
                $success_message = "Clase creada correctamente.";
            }
        } catch (PDOException $e) {
            $error_message = "Error al guardar la clase: " . $e->getMessage();
        }
    }
}

// Manejar acciones GET (eliminar, editar)
$action = $_GET['action'] ?? null;
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$editing_class = null;

if ($action === 'delete' && $class_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        header("Location: classes.php"); // Redirigir para limpiar la URL
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al eliminar la clase.";
    }
}

if ($action === 'edit' && $class_id) {
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$class_id]);
    $editing_class = $stmt->fetch();
}

// --- OBTENCIÓN DE DATOS ---
// ✅ CORRECCIÓN: Inicializar arrays para evitar errores
$classes = [];
$trainers = [];
try {
    // ✅ CORRECCIÓN: Consulta con JOIN a la tabla 'users' para obtener el nombre del entrenador
    $stmt = $pdo->query("
        SELECT c.*, u.name as trainer_name 
        FROM classes c 
        LEFT JOIN trainers t ON c.trainer_id = t.id
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY c.schedule DESC
    ");
    $classes = $stmt->fetchAll();

    // ✅ CORRECCIÓN: Obtener entrenadores para el formulario desde la tabla 'users'
    $trainers_stmt = $pdo->query("
        SELECT t.id, u.name 
        FROM trainers t
        JOIN users u ON t.user_id = u.id
        WHERE u.role = 'trainer' AND u.status = 'active'
        ORDER BY u.name ASC
    ");
    $trainers = $trainers_stmt->fetchAll();

} catch (PDOException $e) {
    $error_message = "Error al cargar los datos.";
    // Para depurar: $error_message = "Error al cargar los datos: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - <?php echo htmlspecialchars($gym_name); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <header class="admin-header">
        <nav class="navbar">
            <a href="dashboard.php" class="logo"><?php echo htmlspecialchars($gym_name); ?> - Admin</a>
            <ul class="nav-menu">
                <li><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                <li><a href="members.php" class="nav-link">Miembros</a></li>
                <li><a href="payments.php" class="nav-link">Pagos</a></li>
                <li><a href="classes.php" class="nav-link active">Clases</a></li>
                <li><a href="trainers.php" class="nav-link">Entrenadores</a></li>
                <li><a href="../process/logout.php" class="nav-link">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <section class="admin-content">
        <div class="container">
            <div class="admin-header-section">
                <h1><?php echo $editing_class ? 'Editar Clase' : 'Gestión de Clases'; ?></h1>
                <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
            </div>

            <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
            <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>

            <div class="form-container">
                <h2><?php echo $editing_class ? 'Actualizar datos de la Clase' : 'Agregar Nueva Clase'; ?></h2>
                <form action="classes.php" method="POST">
                    <input type="hidden" name="class_id" value="<?php echo $editing_class['id'] ?? ''; ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nombre de la Clase</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editing_class['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="trainer_id">Entrenador</label>
                            <select id="trainer_id" name="trainer_id" required>
                                <option value="">Seleccionar entrenador</option>
                                <?php foreach ($trainers as $trainer): ?>
                                    <option value="<?php echo $trainer['id']; ?>" <?php echo (isset($editing_class['trainer_id']) && $editing_class['trainer_id'] == $trainer['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($trainer['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="schedule">Fecha y Hora</label>
                            <input type="datetime-local" id="schedule" name="schedule" value="<?php echo htmlspecialchars($editing_class['schedule'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <!-- ✅ CORRECCIÓN: Usar el nombre de campo correcto -->
                            <label for="duration_minutes">Duración (minutos)</label>
                            <input type="number" id="duration_minutes" name="duration_minutes" value="<?php echo htmlspecialchars($editing_class['duration_minutes'] ?? '60'); ?>" required>
                        </div>
                         <div class="form-group">
                            <!-- ✅ CORRECCIÓN: Usar el nombre de campo correcto -->
                            <label for="capacity">Capacidad Máxima</label>
                            <input type="number" id="capacity" name="capacity" value="<?php echo htmlspecialchars($editing_class['capacity'] ?? '20'); ?>" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="description">Descripción</label>
                            <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($editing_class['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $editing_class ? 'Actualizar Clase' : 'Agregar Clase'; ?></button>
                        <?php if ($editing_class): ?>
                            <a href="classes.php" class="btn btn-secondary">Cancelar Edición</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <h2>Clases Programadas</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Entrenador</th>
                            <th>Horario</th>
                            <th>Duración</th>
                            <th>Capacidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($class['name']); ?></td>
                            <td><?php echo htmlspecialchars($class['trainer_name'] ?? 'No asignado'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($class['schedule'])); ?></td>
                            <!-- ✅ CORRECCIÓN: Usar la columna correcta -->
                            <td><?php echo $class['duration_minutes']; ?> min</td>
                            <!-- ✅ CORRECCIÓN: Usar la columna correcta -->
                            <td><?php echo $class['capacity']; ?></td>
                            <td>
                                <a href="?action=edit&id=<?php echo $class['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="?action=delete&id=<?php echo $class['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta clase?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
</body>
</html>