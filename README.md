# 🏋️ Proyecto de Gimnasio - Sistema de Gestión

Este es un sistema de gestión de gimnasios completo desarrollado con **PHP**, **MySQL** y **JavaScript** puro. La aplicación permite a los administradores gestionar miembros, clases, entrenadores y pagos, mientras que los usuarios pueden ver planes, registrarse y realizar pagos.

## ✨ Características Principales

### Para Usuarios

  * **Visualización de Planes:** Página atractiva para ver las membresías disponibles.
  * **Registro y Autenticación:** Sistema seguro de registro e inicio de sesión de usuarios.
  * **Formulario de Contacto:** Permite a los visitantes enviar consultas.
  * **Proceso de Pago:** Interfaz para que los usuarios compren su membresía.

### Para Administradores

  * **Dashboard de Estadísticas:** Un panel principal con métricas clave como total de miembros, ingresos mensuales y próximas clases.
  * **Gestión de Miembros (CRUD):** Crear, ver, actualizar y eliminar miembros del gimnasio.
  * **Gestión de Entrenadores (CRUD):** Añadir, editar y eliminar perfiles de entrenadores, incluyendo la subida de fotos.
  * **Gestión de Clases (CRUD):** Programar, modificar y eliminar clases, asignando entrenadores y capacidad.
  * **Historial de Pagos:** Visualización de todos los pagos realizados en el sistema.

-----

## 🛠️ Tecnologías Utilizadas

  * **Backend:** PHP 8+
  * **Base de Datos:** MySQL
  * **Frontend:** HTML5, CSS3 (con Variables, Flexbox y Grid), JavaScript (Vanilla)
  * **Entorno de Desarrollo:** XAMPP (Servidor Apache + MariaDB)

-----

## 📂 Estructura del Proyecto

El proyecto sigue una estructura organizada para separar las responsabilidades:

```
/gym-project/
│
├── /admin/        # Panel de administración (vistas y lógica)
├── /assets/       # Archivos estáticos (CSS, JS, imágenes)
├── /includes/     # Módulos reutilizables (config, DB, funciones, auth)
├── /pages/        # Vistas públicas del sitio (inicio, precios, etc.)
├── /process/      # Scripts para procesar formularios (login, registro)
├── index.php      # Punto de entrada principal
└── README.md      # Este archivo
```

-----

## 🚀 Instalación y Puesta en Marcha

Sigue estos pasos para ejecutar el proyecto en tu entorno local usando XAMPP.

### Prerrequisitos

  * Tener **XAMPP** instalado ([Descargar aquí](https://www.apachefriends.org/index.html)).
  * Un editor de código (ej. Visual Studio Code).
  * Un navegador web.

### Pasos de Instalación

1.  **Clonar o Descargar el Repositorio**

      * Coloca la carpeta completa del proyecto `gym-project` dentro del directorio `htdocs` de tu instalación de XAMPP.
      * (Generalmente se encuentra en `C:\xampp\htdocs\`)

2.  **Iniciar los Servicios de XAMPP**

      * Abre el Panel de Control de XAMPP.
      * Inicia los servicios de **Apache** y **MySQL**.

3.  **Crear y Configurar la Base de Datos**

      * Abre tu navegador y ve a `http://localhost/phpmyadmin/`.
      * Crea una nueva base de datos llamada `gym_project`.
      * Selecciona la base de datos `gym_project` que acabas de crear.
      * Ve a la pestaña **"Importar"**.
      * Haz clic en "Seleccionar archivo" y busca el archivo `.sql` de tu proyecto (si no lo tienes, puedes exportar tu base de datos actual desde phpMyAdmin para crear este archivo).
      * Haz clic en **"Importar"** para ejecutar las consultas y crear las tablas con los datos.

4.  **Verificar la Configuración**

      * Abre el archivo `includes/config.php`.
      * Asegúrate de que las credenciales de la base de datos coincidan con tu configuración de XAMPP (por defecto suelen ser correctas):
        ```php
        define('DB_HOST', 'localhost');
        define('DB_NAME', 'gym_project');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        ```

5.  **¡Acceder al Proyecto\!**

      * Abre tu navegador y navega a: `http://localhost/gym-project/`
      * ¡Listo\! El sitio web debería estar funcionando.

-----

## 🔑 Uso del Sistema

### Acceso de Administrador

Para acceder al panel de administración, navega a `http://localhost/gym-project/login.php` y utiliza las credenciales del usuario con `role = 'admin'` en tu base de datos.

  * **Email:** `admin@gym.com`
  * **Contraseña:** (La contraseña que hayas definido para el admin en tu archivo SQL)

Desde el panel, podrás gestionar todos los aspectos del gimnasio.
