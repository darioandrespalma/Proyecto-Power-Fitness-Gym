<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Obtener configuración del sistema
$gym_name = getSetting('gym_name', 'Power Fitness Gym');
$gym_phone = getSetting('gym_phone', '+1 (555) 123-4567');
$gym_email = getSetting('gym_email', 'info@powerfitness.com');
$gym_address = getSetting('gym_address', 'Av. Deportiva 123, Ciudad Fitness');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $gym_name; ?> - Inicio</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenido a <?php echo $gym_name; ?></h1>
            <p>Transforma tu cuerpo, transforma tu vida. Únete a la mejor experiencia fitness de la ciudad.</p>
            <div class="hero-buttons">
                <a href="pricing.php" class="btn btn-primary">Ver Planes</a>
                <a href="payment.php" class="btn btn-secondary">Comenzar Ahora</a>
            </div>
        </div>
    </section>

    <!-- Características -->
    <section class="features">
        <div class="container">
            <h2>¿Por qué elegirnos?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">💪</div>
                    <h3>Entrenadores Expertos</h3>
                    <p>Nuestro equipo de entrenadores certificados te guiará en cada paso.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏋️</div>
                    <h3>Equipamiento Moderno</h3>
                    <p>Tecnología de vanguardia para maximizar tus resultados.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🧘</div>
                    <h3>Clases Variadas</h3>
                    <p>Desde yoga hasta crossfit, tenemos la clase perfecta para ti.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planes Destacados -->
    <section class="plans-preview">
        <div class="container">
            <h2>Nuestros Planes</h2>
            <div class="plans-grid">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM memberships WHERE status = 'active' ORDER BY price LIMIT 3");
                    while ($plan = $stmt->fetch()) {
                        echo '<div class="plan-card">';
                        echo '<h3>' . htmlspecialchars($plan['name']) . '</h3>';
                        echo '<div class="price">' . CURRENCY_SYMBOL . number_format($plan['price'], 2) . '</div>';
                        echo '<p>' . htmlspecialchars($plan['description']) . '</p>';
                        echo '<ul>';
                        $features = explode(',', $plan['features']);
                        foreach ($features as $feature) {
                            echo '<li>' . htmlspecialchars(trim($feature)) . '</li>';
                        }
                        echo '</ul>';
                        echo '<a href="payment.php?membership=' . $plan['id'] . '" class="btn btn-primary">Elegir Plan</a>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error al cargar planes</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>