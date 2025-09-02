# FAMASY

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-4B56D2?style=for-the-badge&logo=livewire&logoColor=white)](https://laravel-livewire.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)

FAMASY es una aplicación web moderna construida con Laravel, Livewire y Tailwind CSS, diseñada para ofrecer una experiencia de usuario fluida y reactiva.

## 📁 Estructura del Proyecto

```
FAMASY/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── VerifyEmailController.php
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   └── CheckActiveSession.php
│   │   └── Livewire/
│   │       ├── Actions/
│   │       │   └── Logout.php
│   ├── Models/
│   │   ├── Animal.php
│   │   ├── Auditoria.php
│   │   ├── Cliente.php
│   │   ├── CompraGasto.php
│   │   ├── Contacto.php
│   │   ├── CuentaPendiente.php
│   │   ├── DatabaseBackup.php
│   │   ├── Direccion.php
│   │   ├── Factura.php
│   │   ├── Herramienta.php
│   │   ├── HistorialMedico.php
│   │   ├── Insumo.php
│   │   ├── Inventario.php
│   │   ├── Mantenimiento.php
│   │   ├── MovimientoContable.php
│   │   ├── Pago.php
│   │   ├── PrestamoHerramienta.php
│   │   ├── ProduccionAnimal.php
│   │   ├── Proveedor.php
│   │   ├── Rol.php
│   │   └── User.php
│   ├── Observers/
│   │   └── ModelAuditObserver.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── AuditServiceProvider.php
│       ├── AuthServiceProvider.php
│       └── VoltServiceProvider.php
├── resources/
│   ├── css/
│   │   ├── components/
│   │   │   ├── nav.css
│   │   │   ├── sidebar.css
│   │   │   └── app.css
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── cards-hover.js
│   │   └── sidebar.js
│   └── views/
│       ├── auth/
│       │   └── home.blade.php
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── auth.blade.php
│       ├── livewire/
│       │   ├── auth/
│       │   ├── contabilidad/
│       │   ├── inventario/
│       │   ├── pecuario/
│       │   ├── proveedores/
│       │   └── settings/
│       └── partials/
│           ├── auth-footer.blade.php
│           ├── auth-nav.blade.php
│           ├── footer.blade.php
│           ├── nav.blade.php
│           └── sidebar.blade.php
└── routes/
    ├── auth.php
    ├── console.php
    └── web.php
```

### Módulos Principales

1. **Autenticación y Usuarios**
   - Gestión de usuarios y roles
   - Control de sesiones
   - Perfiles de usuario

2. **Inventario**
   - Gestión de herramientas
   - Control de insumos
   - Mantenimientos
   - Préstamos de herramientas

3. **Pecuario**
   - Registro de animales
   - Seguimiento de producción
   - Control de salud y peso

4. **Contabilidad**
   - Gestión de facturas
   - Control de pagos
   - Movimientos contables
   - Cuentas pendientes

5. **Configuración**
   - Administración de usuarios
   - Copias de seguridad

### Archivos Clave

- `.env` - Configuración del entorno (no versionado)
- `composer.json` - Dependencias de PHP y scripts
- `package.json` - Dependencias de JavaScript y scripts
- `vite.config.js` - Configuración de Vite
- `phpunit.xml` - Configuración de pruebas PHPUnit
- `.github/workflows/` - Configuración de GitHub Actions para CI/CD

## 🚀 Características

- **Arquitectura moderna** basada en Laravel 12
- **Interfaz reactiva** con Livewire
- **Diseño responsive** con Tailwind CSS
- **Desarrollo ágil** con Vite como bundler
- **Testing** con Pest PHP
- **Integración continua** con GitHub Actions

## 📋 Requisitos del Sistema

- PHP 8.2 o superior
- Composer
- Node.js 18+ y NPM
- Base de datos compatible (MySQL, PostgreSQL, SQLite, etc.)
- Servidor web (Apache/Nginx) o PHP built-in server

## 🛠️ Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/famasy.git
   cd famasy
   ```

2. **Instalar dependencias de PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Node.js**
   ```bash
   npm install
   ```

4. **Configurar entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos**
   - Crear una base de datos
   - Configurar las variables de entorno en `.env`
   - Ejecutar migraciones:
     ```bash
     php artisan migrate
     ```

6. **Construir assets**
   ```bash
   npm run build
   # O para desarrollo:
   npm run dev
   ```

7. **Iniciar servidor**
   ```bash
   php artisan serve
   ```

## 🧪 Testing

El proyecto utiliza Pest PHP para pruebas. Para ejecutar las pruebas:

```bash
composer test
# O para ver la cobertura de pruebas:
composer test -- --coverage-html=coverage
```

### Tipos de Pruebas
- **Unit Tests**: Pruebas de unidades individuales de código
- **Feature Tests**: Pruebas de funcionalidades completas
- **Browser Tests**: Pruebas de interfaz de usuario (opcional)

## 🔒 Seguridad

Si descubre alguna vulnerabilidad de seguridad, por favor envíe un correo electrónico a [famasytechnologies@gmail.com] en lugar de usar el sistema de issues.

### Buenas Prácticas de Seguridad
- No almacenar credenciales directamente en el código
- Usar variables de entorno para información sensible
- Mantener las dependencias actualizadas
- Seguir el principio de mínimo privilegio en la base de datos

## 🚀 Despliegue

### Requisitos del Servidor
- PHP 8.2+
- Composer
- Node.js 18+ y NPM
- Base de datos compatible
- Servidor web (Nginx/Apache)

### Pasos para Despliegue
1. Clonar el repositorio
2. Instalar dependencias de PHP y Node.js
3. Configurar el archivo `.env`
4. Generar clave de aplicación
5. Ejecutar migraciones y seeders
6. Configurar el programador de tareas (scheduler)
7. Configurar el servidor web

## 🛠️ Herramientas de Desarrollo

- **Laravel Tinker** - Para interactuar con la aplicación desde la línea de comandos
- **Laravel Sail** - Entorno de desarrollo Docker (opcional)
- **Laravel Pint** - Para formateo de código
- **Laravel Pail** - Para depuración de logs

## 🤝 Contribución

1. Hacer un fork del proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Hacer commit de tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Hacer push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más información.

## 🌟 Reconocimientos

- [Laravel](https://laravel.com) - El framework PHP para artesanos web
- [Livewire](https://laravel-livewire.com) - Framework full-stack para Laravel
- [Tailwind CSS](https://tailwindcss.com) - Framework CSS utility-first
- [Vite](https://vitejs.dev/) - Herramienta de construcción frontend
- [Pest](https://pestphp.com) - Framework de pruebas elegante para PHP

## ❓ Soporte

Para soporte, por favor abra un [issue](https://github.com/tu-usuario/famasy/issues) en el repositorio o contáctenos en [famasy.soporte@gmail.com].

---

## 👥 Equipo de Desarrollo

- Stip Pama
- Willian Nievas
- Keily Troyano
- Victor Castro
- Pablo Lopez

---

Desarrollado con ❤️ por el equipo de FAMASY Technologies | [MIT License](LICENSE)
