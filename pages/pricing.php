<?php
require_once '../includes/database.php';
require_once '../includes/functions.php';

$gym_name = getSetting('gym_name', 'Power Fitness Gym');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes y Precios - <?php echo $gym_name; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Pricing Section -->
    <section class="pricing-section">
        <div class="container">
            <h1>Nuestros Planes</h1>
            <p class="subtitle">Elige el plan perfecto para alcanzar tus objetivos</p>
            
            <div class="pricing-grid">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM memberships WHERE status = 'active' ORDER BY price");
                    while ($plan = $stmt->fetch()) {
                        echo '<div class="pricing-card">';
                        echo '<div class="plan-header">';
                        echo '<h3>' . htmlspecialchars($plan['name']) . '</h3>';
                        echo '<div class="price">' . CURRENCY_SYMBOL . number_format($plan['price'], 2) . '</div>';
                        echo '<div class="duration">' . $plan['duration_days'] . ' días</div>';
                        echo '</div>';
                        echo '<div class="plan-features">';
                        echo '<p>' . htmlspecialchars($plan['description']) . '</p>';
                        echo '<ul>';
                        $features = explode(',', $plan['features']);
                        foreach ($features as $feature) {
                            echo '<li>' . htmlspecialchars(trim($feature)) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                        echo '<div class="plan-actions">';
                        echo '<a href="payment.php?membership=' . $plan['id'] . '" class="btn btn-primary btn-block">Elegir Plan</a>';
                        echo '</div>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    echo '<p class="error">Error al cargar planes: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2>¿Listo para comenzar?</h2>
            <p>Únete hoy y comienza tu transformación fitness</p>
            <a href="payment.php" class="btn btn-secondary">Comenzar Ahora</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>