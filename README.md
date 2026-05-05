# 📅 Proyecto de Reservas - Laravel 12

Sistema de gestión de reservas desarrollado con Laravel 12, diseñado para administrar salas, objetos y disponibilidad de manera eficiente.

## 🚀 Características
- Gestión de usuarios (registro y autenticación)
- Creación y administración de reservas
- Gestión de servicios o recursos reservables
- Control de disponibilidad (fechas y horarios)
- Panel administrativo
- Validación de datos y seguridad integrada
## 🛠️ Tecnologías utilizadas
- PHP 8+
- Laravel 12
- MySQL / MariaDB
- Blade (templating)
- Bootstrap / Tailwind (según implementación)
- Composer
- Livewire 4

## 📂 Estructura del proyecto
- app/
- bootstrap/
- config/
- database/
- public/
- resources/
- routes/
- storage/
- tests/

## ⚙️ Instalación

Sigue estos pasos para ejecutar el proyecto en local:

1. Clonar el repositorio

git clone https://github.com/AaronFuentesC/proyectoReservasLaravel12.git
cd proyectoReservasLaravel12

2. Instalar dependencias

composer install
npm install && npm run dev

3. Configurar entorno

cp .env.example .env

4. Editar el archivo .env con tus credenciales de base de datos.

5. Generar clave de la aplicación

php artisan key:generate

6. Migrar base de datos

php artisan migrate

7. Ejecutar los seeders

php artisan db:seed

8. Ejecutar el servidor

php artisan serve

Accede en:
👉 http://localhost:8000

## 👤 Roles del sistema
- Administrador: gestiona usuarios, reservas y configuración
- Usuario: realiza y consulta sus reservas
## 📅 Funcionalidades principales
- Crear reservas
- Editar / cancelar reservas
- Gráficos estadísticos de reservas
- Gestión de clientes
- Panel administrativo
## 🔐 Seguridad

### Laravel incluye múltiples mecanismos de seguridad como:

- Protección contra XSS y CSRF
- Validación de formularios
- Autenticación integrada

## 📦 Despliegue

### Puedes desplegar en:

- Servidores Apache/Nginx
- Docker
- Laravel Sail

## 🤝 Contribuciones

Las contribuciones son bienvenidas:

- Fork del repositorio
- Crear rama (feature/nueva-funcionalidad)
- Commit
- Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

## 📬 Contacto

Desarrollado por Aaron Fuentes Casanova
#### GitHub: https://github.com/AaronFuentesC
