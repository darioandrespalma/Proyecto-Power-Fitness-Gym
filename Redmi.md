/gym-project/
│
├── /admin/
│   ├── dashboard.php
│   ├── members.php
│   ├── payments.php
│   ├── classes.php
│   └── trainers.php
│
├── /assets/
│   ├── /css/
│   │   ├── style.css
│   │   ├── auth.css
│   │   └── dashboard.css
│   ├── /js/
│   │   ├── main.js
│   │   ├── auth.js
│   │   └── payment.js
│   ├── /images/
│   │   ├── logo.png
│   │   ├── hero-bg.jpg
│   │   └── trainers/
│   └── /uploads/
│       └── profile-pics/
│
├── /includes/
│   ├── config.php
│   ├── database.php
│   ├── auth.php
│   ├── functions.php
│   └── header.php
│   └── footer.php
│
├── /pages/
│   ├── home.php
│   ├── about.php
│   ├── services.php
│   ├── trainers.php
│   ├── pricing.php
│   ├── contact.php
│   └── payment.php
│
├── /process/
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── payment-process.php
│   └── contact-process.php
│
├── index.php
├── login.php
├── register.php
└── .htaccess


--- 
Te explico en detalle qué hace cada archivo y carpeta en la estructura del proyecto:

## 📁 **ESTRUCTURA DEL PROYECTO GIMNASIO**

### **📂 admin/**
**Panel de administración** (requiere login de admin)

- **`dashboard.php`** → Panel principal con estadísticas, resumen de miembros, pagos recientes, etc.
- **`members.php`** → Gestión de miembros (ver, editar, eliminar, cambiar estado de membresías)
- **`payments.php`** → Visualización y gestión de todos los pagos realizados
- **`classes.php`** → CRUD de clases disponibles (crear, editar, eliminar horarios)
- **`trainers.php`** → Administración de entrenadores (agregar, modificar, asignar clases)

### **📂 assets/**
**Recursos estáticos** del sitio

- **`css/style.css`** → Estilos principales del sitio
- **`css/auth.css`** → Estilos específicos para login/registro
- **`css/dashboard.css`** → Estilos para el panel de administración
- **`js/main.js`** → Funcionalidades generales del sitio
- **`js/auth.js`** → Validaciones de formularios de autenticación
- **`js/payment.js`** → Validación de tarjetas y procesamiento de pagos
- **`images/`** → Logos, imágenes de fondo, fotos de entrenadores
- **`uploads/`** → Archivos subidos por usuarios (fotos de perfil, etc.)

### **📂 includes/**
**Archivos de inclusión** (funciones reutilizables)

- **`config.php`** → Configuración de la base de datos y constantes del sitio
- **`database.php`** → Conexión a la base de datos (usando PDO o MySQLi)
- **`auth.php`** → Funciones de autenticación (login, registro, verificación)
- **`functions.php`** → Utilidades generales (sanitización, validaciones, helpers)
- **`header.php`** → Cabecera común de todas las páginas
- **`footer.php`** → Pie de página común

### **📂 pages/**
**Páginas principales** del sitio web

- **`home.php`** → Página de inicio con hero section, características, planes
- **`about.php`** → Información sobre el gimnasio, historia, misión
- **`services.php`** → Servicios ofrecidos (entrenamiento personalizado, clases grupales)
- **`trainers.php`** → Galería de entrenadores con sus especialidades
- **`pricing.php`** → Detalles completos de planes y precios
- **`contact.php`** → Formulario de contacto
- **`payment.php`** → Formulario de pago para membresías

### **📂 process/**
**Procesamiento de formularios** (lógica backend)

- **`login.php`** → Valida credenciales e inicia sesión
- **`register.php`** → Registra nuevo usuario en la base de datos
- **`logout.php`** → Cierra la sesión del usuario
- **`payment-process.php`** → Procesa pagos de membresías
- **`contact-process.php`** → Envía emails de contacto

### **📄 Archivos raíz**

- **`index.php`** → Página principal que redirige a home.php
- **`login.php`** → Formulario de inicio de sesión
- **`register.php`** → Formulario de registro de usuario
- **`.htaccess`** → Configuración de URLs amigables y seguridad

---

## 🔄 **FLUJO DE FUNCIONAMIENTO**

### **Para usuarios no registrados:**
1. Ven la página principal (`home.php`)
2. Pueden ver planes (`pricing.php`) 
3. Pagar membresía sin registro (`payment.php` → `payment-process.php`)
4. Contactar (`contact.php` → `contact-process.php`)

### **Para usuarios registrados:**
1. Login (`login.php` → `process/login.php`)
2. Acceden a contenido exclusivo
3. Renuevan membresía desde su perfil

### **Para administradores:**
1. Login especial
2. Acceden a `/admin/dashboard.php`
3. Gestionan todo el sistema

---

## 🗃️ **BASE DE DATOS - Tablas principales**

### **`users`** → Usuarios del sistema
- **id, name, email, password, phone, created_at**

### **`members`** → Miembros con membresía activa  
- **id, user_id, membership_type, start_date, end_date, status**

### **`payments`** → Historial de pagos
- **id, user_id, guest_email, amount, payment_date, status, transaction_id**

### **`classes`** → Clases disponibles
- **id, name, description, schedule, duration, trainer_id, max_capacity**

### **`trainers`** → Entrenadores
- **id, name, specialty, bio, image**

---

## 🛡️ **MEDIDAS DE SEGURIDAD IMPLEMENTADAS**

1. **Hash de contraseñas** (password_hash)
2. **Prepared statements** para evitar SQL injection
3. **Sanitización de inputs** 
4. **Validación de datos** en frontend y backend
5. **Protección de archivos sensibles** (config.php fuera de root público)
6. **Verificación de sesiones** en áreas administrativas
7. **.htaccess** para restringir acceso directo a ciertas carpetas

