# FAMASY

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/Livewire-4B56D2?style=for-the-badge&logo=livewire&logoColor=white)](https://laravel-livewire.com/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)

FAMASY es una aplicación web moderna construida con Laravel, Livewire y Tailwind CSS, diseñada para ofrecer una experiencia de usuario fluida y reactiva.

## 📁 Estructura del Proyecto

```
FAMASY/
├── app/                    # Código fuente de la aplicación
│   ├── Http/              # Controladores y middleware
│   ├── Livewire/          # Componentes Livewire
│   ├── Models/            # Modelos de Eloquent
│   └── Providers/         # Service Providers
├── bootstrap/             # Archivos de arranque
├── config/                # Archivos de configuración
├── database/              # Migraciones, seeders y factories
│   ├── factories/         # Factories para testing
│   ├── migrations/        # Migraciones de base de datos
│   └── seeders/           # Seeders para datos iniciales
├── public/                # Punto de entrada de la aplicación
├── resources/             
│   ├── css/               # Estilos CSS
│   ├── js/                # JavaScript de la aplicación
│   └── views/             # Vistas Blade y componentes
│       ├── components/    # Componentes reutilizables
│       └── livewire/      # Vistas de componentes Livewire
├── routes/                # Definición de rutas
│   ├── web.php           # Rutas web
│   └── auth.php          # Rutas de autenticación
├── storage/               # Almacenamiento de archivos
├── tests/                 # Pruebas automatizadas
│   ├── Feature/          # Pruebas de características
│   └── Unit/             # Pruebas unitarias
├── .env.example          # Variables de entorno de ejemplo
├── artisan              # CLI de Laravel
├── composer.json        # Dependencias de PHP
└── package.json         # Dependencias de Node.js
```

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
