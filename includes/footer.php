<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php echo getSetting('gym_name', 'Power Fitness Gym'); ?></h3>
                <p>Transforma tu cuerpo, transforma tu vida. Únete a la mejor experiencia fitness.</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="../index.php">Inicio</a></li>
                    <li><a href="pricing.php">Planes</a></li>
                    <li><a href="payment.php">Pago</a></li>
                    <li><a href="contact.php">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contacto</h3>
                <ul class="footer-links">
                    <li><?php echo getSetting('gym_address', 'Av. Deportiva 123'); ?></li>
                    <li><?php echo getSetting('gym_phone', '+1 (555) 123-4567'); ?></li>
                    <li><?php echo getSetting('gym_email', 'info@gym.com'); ?></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 <?php echo getSetting('gym_name', 'Power Fitness Gym'); ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>