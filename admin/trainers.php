<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar autenticación y rol de administrador
if (!isLoggedIn() || getUserRole() !== 'admin') {
    redirect('../login.php');
}

$gym_name = getSetting('gym_name', 'Power Fitness Gym');
$page_title = 'Gestión de Entrenadores';
$error_message = '';
$success_message = '';
// La ruta de subida de imágenes ahora apunta a la carpeta de fotos de la tabla 'users'
$upload_dir = '../assets/images/trainers/'; 

// --- LÓGICA DE PROCESAMIENTO (CRUD) ---

// Manejar envío de formulario para agregar/editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $trainer_id = $_POST['trainer_id'] ?? null;
    
    // Datos de la tabla 'users'
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password']; // Solo para nuevos entrenadores
    $current_photo = $_POST['current_photo'] ?? '';
    $photo_name = $current_photo;

    // Datos de la tabla 'trainers'
    $specialization = trim($_POST['specialization']);
    $experience_years = (int)$_POST['experience_years'];
    $certification = trim($_POST['certification']);
    $bio = trim($_POST['bio']);

    // Manejar subida de archivo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $photo_name = uniqid('trainer_', true) . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $photo_name)) {
            if ($user_id && !empty($current_photo) && file_exists($upload_dir . $current_photo)) {
                unlink($upload_dir . $current_photo);
            }
        } else {
            $error_message = "Error al subir la imagen.";
            $photo_name = $current_photo;
        }
    }

    if (empty($name) || empty($email) || empty($specialization)) {
        $error_message = "Nombre, email y especialidad son obligatorios.";
    } else {
        try {
            $pdo->beginTransaction();

            if ($user_id) { // --- ACTUALIZAR ENTRENADOR EXISTENTE ---
                // 1. Actualizar tabla 'users'
                $stmt_user = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, photo = ? WHERE id = ?");
                $stmt_user->execute([$name, $email, $phone, $photo_name, $user_id]);

                // 2. Actualizar tabla 'trainers'
                $stmt_trainer = $pdo->prepare("UPDATE trainers SET specialization = ?, experience_years = ?, certification = ?, bio = ? WHERE id = ?");
                $stmt_trainer->execute([$specialization, $experience_years, $certification, $bio, $trainer_id]);
                
                $success_message = "Entrenador actualizado correctamente.";

            } else { // --- INSERTAR NUEVO ENTRENADOR ---
                if (empty($password)) {
                    throw new Exception("La contraseña es obligatoria para nuevos entrenadores.");
                }
                 // 1. Insertar en tabla 'users'
                $password_hash = generatePasswordHash($password);
                $stmt_user = $pdo->prepare("INSERT INTO users (name, email, password, phone, role, status, photo) VALUES (?, ?, ?, ?, 'trainer', 'active', ?)");
                $stmt_user->execute([$name, $email, $password_hash, $phone, $photo_name]);
                $new_user_id = $pdo->lastInsertId();

                // 2. Insertar en tabla 'trainers'
                $stmt_trainer = $pdo->prepare("INSERT INTO trainers (user_id, specialization, experience_years, certification, bio) VALUES (?, ?, ?, ?, ?)");
                $stmt_trainer->execute([$new_user_id, $specialization, $experience_years, $certification, $bio]);
                
                $success_message = "Entrenador agregado correctamente.";
            }

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error_message = "Error al guardar los datos del entrenador: " . $e->getMessage();
        }
    }
}


// Manejar acciones GET (eliminar, editar)
$action = $_GET['action'] ?? null;
$user_id_get = isset($_GET['id']) ? (int)$_GET['id'] : null;
$editing_trainer = null;

if ($action === 'delete' && $user_id_get) {
    try {
        // La base de datos está configurada con ON DELETE CASCADE,
        // así que solo necesitamos borrar el usuario.
        $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt->execute([$user_id_get]);
        $photo_to_delete = $stmt->fetchColumn();

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'trainer'");
        $stmt->execute([$user_id_get]);
        
        if ($photo_to_delete && file_exists($upload_dir . $photo_to_delete)) {
            unlink($upload_dir . $photo_to_delete);
        }

        header("Location: trainers.php");
        exit;
    } catch (PDOException $e) {
        $error_message = "Error al eliminar el entrenador.";
    }
}

if ($action === 'edit' && $user_id_get) {
    $stmt = $pdo->prepare("
        SELECT u.id as user_id, u.name, u.email, u.phone, u.photo, t.* FROM users u
        JOIN trainers t ON u.id = t.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id_get]);
    $editing_trainer = $stmt->fetch();
}

// --- OBTENCIÓN DE DATOS ---
$trainers = []; 
try {
    // Consulta CORREGIDA con JOIN
    $stmt = $pdo->query("
        SELECT u.id as user_id, u.name, u.photo, t.id as trainer_id, t.specialization
        FROM users u
        JOIN trainers t ON u.id = t.user_id
        WHERE u.role = 'trainer'
        ORDER BY u.name ASC
    ");
    $trainers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_message = "Error al cargar los entrenadores.";
    // Para depurar, puedes descomentar la siguiente línea:
    // $error_message = "Error al cargar los entrenadores: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
        <header class="admin-header">
        <nav class="navbar">
            <a href="dashboard.php" class="logo"><?php echo $gym_name; ?> - Admin</a>
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
    <header class="admin-header">
        </header>

    <section class="admin-content">
        <div class="container">
            <?php if ($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
            <?php if ($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>

            <div class="form-container">
                <h2><?php echo $editing_trainer ? 'Actualizar Entrenador' : 'Agregar Nuevo Entrenador'; ?></h2>
                <form action="trainers.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $editing_trainer['user_id'] ?? ''; ?>">
                    <input type="hidden" name="trainer_id" value="<?php echo $editing_trainer['id'] ?? ''; ?>">
                    <input type="hidden" name="current_photo" value="<?php echo $editing_trainer['photo'] ?? ''; ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nombre Completo</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($editing_trainer['name'] ?? ''); ?>" required>
                        </div>
                         <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($editing_trainer['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($editing_trainer['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="password">Contraseña <?php if($editing_trainer) echo "(Dejar en blanco para no cambiar)"; ?></label>
                            <input type="password" id="password" name="password" <?php if(!$editing_trainer) echo "required"; ?>>
                        </div>
                        <div class="form-group">
                            <label for="specialization">Especialidad</label>
                            <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($editing_trainer['specialization'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="experience_years">Años de Experiencia</label>
                            <input type="number" id="experience_years" name="experience_years" value="<?php echo htmlspecialchars($editing_trainer['experience_years'] ?? '0'); ?>">
                        </div>
                         <div class="form-group full-width">
                            <label for="certification">Certificaciones</label>
                            <textarea id="certification" name="certification" rows="2"><?php echo htmlspecialchars($editing_trainer['certification'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="bio">Biografía</label>
                            <textarea id="bio" name="bio" rows="3"><?php echo htmlspecialchars($editing_trainer['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="photo">Foto</label>
                            <input type="file" id="photo" name="photo" accept="image/png, image/jpeg, image/webp">
                            <?php if (!empty($editing_trainer['photo'])): ?>
                                <p>Foto actual: <img src="<?php echo $upload_dir . htmlspecialchars($editing_trainer['photo']); ?>" alt="Foto actual" width="50"></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $editing_trainer ? 'Actualizar' : 'Agregar'; ?></button>
                        <?php if ($editing_trainer): ?>
                            <a href="trainers.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <h2>Lista de Entrenadores</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Especialidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trainers as $trainer): ?>
                        <tr>
                            <td>
                                <?php if (!empty($trainer['photo'])): ?>
                                    <img src="<?php echo $upload_dir . htmlspecialchars($trainer['photo']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" class="table-image">
                                <?php else: ?>
                                    <span>Sin foto</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($trainer['name']); ?></td>
                            <td><?php echo htmlspecialchars($trainer['specialization']); ?></td>
                            <td>
                                <a href="?action=edit&id=<?php echo $trainer['user_id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="?action=delete&id=<?php echo $trainer['user_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro?')">Eliminar</a>
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