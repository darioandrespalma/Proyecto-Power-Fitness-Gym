-- Crear base de datos
CREATE DATABASE gym_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_project;

-- Tabla de usuarios (administradores, miembros, entrenadores)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'member', 'trainer') DEFAULT 'member',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de membresías
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    features TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de miembros (información adicional)
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    membership_id INT,
    start_date DATE,
    end_date DATE,
    emergency_contact VARCHAR(100),
    medical_conditions TEXT,
    goals TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL
);

-- Tabla de pagos
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    membership_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'cash', 'bank_transfer') NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (membership_id) REFERENCES memberships(id) ON DELETE SET NULL
);

-- Tabla de entrenadores
CREATE TABLE trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialization VARCHAR(100),
    experience_years INT,
    certification TEXT,
    bio TEXT,
    rating DECIMAL(3,2),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla de clases
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    trainer_id INT,
    schedule DATETIME,
    duration_minutes INT DEFAULT 60,
    capacity INT DEFAULT 20,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    status ENUM('active', 'inactive', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE SET NULL
);

-- Tabla de inscripciones a clases
CREATE TABLE class_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    user_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('registered', 'attended', 'cancelled') DEFAULT 'registered',
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (class_id, user_id)
);

-- Tabla de contactos (mensajes del formulario de contacto)
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL
);

-- Tabla de configuración del sistema
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description VARCHAR(255)
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_payments_user ON payments(user_id);
CREATE INDEX idx_payments_date ON payments(payment_date);
CREATE INDEX idx_members_user ON members(user_id);
CREATE INDEX idx_classes_schedule ON classes(schedule);
CREATE INDEX idx_classes_trainer ON classes(trainer_id);

-- ======================================================================
-- 📥 INSERCIÓN DE DATOS DE EJEMPLO
-- ======================================================================

-- Insertar usuarios (admin, miembros, entrenadores)
INSERT INTO users (name, email, password, phone, address, role, status, photo) VALUES
-- Admin principal
('Administrador Principal', 'admin@gym.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 'Av. Principal 123', 'admin', 'active', 'admin.jpg'),
-- Miembros
('Juan Pérez', 'juan.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'Calle Falsa 123', 'member', 'active', 'member1.jpg'),
('María García', 'maria.garcia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5551234567', 'Av. Siempre Viva 456', 'member', 'active', 'member2.jpg'),
('Carlos López', 'carlos.lopez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5559876543', 'Calle Luna 789', 'member', 'inactive', 'member3.jpg'),
('Ana Martínez', 'ana.martinez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5554567890', 'Boulevard Sol 321', 'member', 'active', 'member4.jpg'),
-- Entrenadores
('Roberto Sánchez', 'roberto.sanchez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5551112233', 'Zona Deportiva 100', 'trainer', 'active', 'trainer1.jpg'),
('Laura Torres', 'laura.torres@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5554445566', 'Parque Central 200', 'trainer', 'active', 'trainer2.jpg'),
('Miguel Hernández', 'miguel.hernandez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '5557778899', 'Colinas Verdes 300', 'trainer', 'active', 'trainer3.jpg');

-- Insertar membresías
INSERT INTO memberships (name, description, price, duration_days, features, status) VALUES
('Básico', 'Acceso a instalaciones básicas y cardio', 199.99, 30, 'Gimnasio, Cardio, Duchas, Taquillas', 'active'),
('Premium', 'Acceso completo + clases grupales', 299.99, 30, 'Gimnasio completo, Clases grupales, Spa, Nutricionista', 'active'),
('VIP', 'Acceso ilimitado + entrenador personal', 499.99, 30, 'Gimnasio ilimitado, Entrenador personal, Clases VIP, Spa premium', 'active'),
('Mensual Básico', 'Plan mensual económico', 149.99, 30, 'Gimnasio básico, Duchas', 'active'),
('Trimestral', 'Ahorra con plan trimestral', 399.99, 90, 'Gimnasio completo, 10% descuento en servicios', 'active');

-- Insertar información de miembros
INSERT INTO members (user_id, membership_id, start_date, end_date, emergency_contact, medical_conditions, goals) VALUES
(2, 2, '2024-01-15', '2024-02-15', 'María Pérez - 5551234567', 'Ninguna', 'Perder peso y ganar masa muscular'),
(3, 3, '2024-01-10', '2024-02-10', 'Pedro García - 5559876543', 'Hipertensión leve', 'Mejorar resistencia'),
(4, 1, '2023-12-01', '2023-12-31', 'Luis López - 5551112233', 'Lesión en rodilla', 'Rehabilitación'),
(5, 2, '2024-01-20', '2024-02-20', 'Sofía Martínez - 5554445566', 'Ninguna', 'Tonificación general');

-- Insertar información de entrenadores
INSERT INTO trainers (user_id, specialization, experience_years, certification, bio, rating) VALUES
(6, 'Entrenamiento Funcional', 8, 'Certificado NASM, CrossFit Level 2', 'Especialista en entrenamiento funcional y crossfit con 8 años de experiencia ayudando a clientes a alcanzar sus objetivos de fitness.', 4.8),
(7, 'Yoga y Pilates', 6, 'Instructora certificada de Yoga y Pilates', 'Instructora de yoga y pilates con enfoque en bienestar integral y reducción de estrés.', 4.9),
(8, 'Nutrición Deportiva', 10, 'Nutricionista deportivo certificado', 'Especialista en nutrición deportiva y entrenamiento de fuerza con más de 10 años de experiencia.', 4.7);

-- Insertar clases con los IDs CORRECTOS de entrenadores (1, 2, 3)
INSERT INTO classes (name, description, trainer_id, schedule, duration_minutes, capacity, difficulty, status) VALUES
('Yoga Matutino', 'Clase de yoga para principiantes y nivel intermedio', 2, '2024-02-01 08:00:00', 60, 15, 'beginner', 'active'),
('CrossFit Intensivo', 'Entrenamiento de alta intensidad para avanzados', 1, '2024-02-01 18:00:00', 75, 12, 'advanced', 'active'),
('Pilates Core', 'Fortalecimiento del core y postura', 2, '2024-02-02 10:00:00', 50, 10, 'intermediate', 'active'),
('Entrenamiento Funcional', 'Clase completa de entrenamiento funcional', 1, '2024-02-02 19:00:00', 60, 20, 'intermediate', 'active'),
('Meditación y Relajación', 'Sesión de meditación guiada', 2, '2024-02-03 07:00:00', 45, 25, 'beginner', 'active');

-- Insertar pagos
INSERT INTO payments (user_id, membership_id, amount, payment_method, transaction_id, status, notes) VALUES
(2, 2, 299.99, 'credit_card', 'TXN001', 'completed', 'Pago mensual Premium'),
(3, 3, 499.99, 'debit_card', 'TXN002', 'completed', 'Pago mensual VIP'),
(4, 1, 199.99, 'cash', 'TXN003', 'completed', 'Pago en efectivo'),
(5, 2, 299.99, 'bank_transfer', 'TXN004', 'completed', 'Transferencia bancaria');

-- Insertar inscripciones a clases
INSERT INTO class_enrollments (class_id, user_id, status) VALUES
(16, 2, 'registered'),
(17, 3, 'registered'),
(20, 2, 'registered'),
(18, 5, 'registered'),
(19, 3, 'registered');

-- Insertar mensajes de contacto
INSERT INTO contacts (name, email, phone, subject, message, status) VALUES
('Cliente Interesado', 'cliente1@email.com', '5551111111', 'Información sobre membresías', 'Hola, me gustaría saber más sobre las membresías disponibles.', 'new'),
('Sugerencia', 'cliente2@email.com', '5552222222', 'Sugerencia de mejora', 'Sería genial tener más clases de zumba en la tarde.', 'read'),
('Problema técnico', 'cliente3@email.com', '5553333333', 'Problema con el acceso', 'No puedo acceder a mi cuenta, ¿me pueden ayudar?', 'replied');

-- Insertar configuración del sistema
INSERT INTO settings (setting_key, setting_value, description) VALUES
('gym_name', 'Power Fitness Gym', 'Nombre del gimnasio'),
('gym_email', 'info@powerfitness.com', 'Email principal del gimnasio'),
('gym_phone', '+1 (555) 123-4567', 'Teléfono del gimnasio'),
('gym_address', 'Av. Deportiva 123, Ciudad Fitness', 'Dirección del gimnasio'),
('currency', 'MXN', 'Moneda utilizada'),
('tax_rate', '16.00', 'Tasa de impuesto aplicable'),
('max_class_capacity', '25', 'Capacidad máxima por clase'),
('default_membership', '2', 'ID de membresía por defecto');

-- ======================================================================
-- 🔐 NOTA IMPORTANTE SOBRE CONTRASEÑAS
-- ======================================================================
-- Las contraseñas en este ejemplo usan el hash por defecto de Laravel:
-- password = "password" (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
-- En producción, usa password_hash() con PASSWORD_DEFAULT
-- Ejemplo: password_hash('tu_contraseña', PASSWORD_DEFAULT)