# 🗿 Tienda3D & Apparel - API Backend

Backend robusto desarrollado con Laravel 11 para la gestión integral de un e-commerce especializado en figuras 3D y merchandising (camisetas). Este proyecto ha sido diseñado siguiendo estándares profesionales, priorizando la seguridad y la consistencia en el manejo de productos técnicos y personalizados.

## 🚀 Características Técnicas & "Seniority"

A diferencia de un proyecto básico, esta API implementa:

- Autenticación JWT: Seguridad stateless mediante JSON Web Tokens para una integración fluida con SPAs (Vue 3).
- Gestión Manual de Errores (API Consistency): Control total de excepciones 404 y 422 en los controladores. Esto asegura que el frontend siempre reciba mensajes claros y predecibles, evitando errores genéricos del sistema.
- TDD (Test Driven Development): Cobertura de 28 tests de integración (Pest) que validan lógica de negocio, seguridad de roles y persistencia de datos.
- Arquitectura de Eventos: Notificaciones automáticas de stock bajo (crucial para productos bajo demanda) mediante Events & Listeners.
- Observers: Automatización de procesos como la generación de slugs únicos para cada figura o modelo de camiseta.

## 🛠️ Tech Stack

- Framework: Laravel 11 (PHP 8.2+)
- Seguridad: JWT (tymon/jwt-auth)
- Testing: Pest PHP
- DB: MariaDB / MySQL
- Frontend Ready: Configuración CORS optimizada para Vite/Vue 3 (Puerto 5173).

## 📦 Instalación

Clonar y acceder:

```bash
git clone https://github.com/tu-usuario/tienda-3d-laravel.git
cd tienda-3d-laravel
```

Dependencias y Entorno:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configuración de Seguridad (JWT):

```bash
php artisan jwt:secret
```

Base de Datos y Seeders:
Configura tu DB en el .env y ejecuta:

```bash
php artisan migrate --seed
```

Assets (Vite):

```bash
npm install
npm run dev
```

## 🧪 Testing

Puedes verificar la integridad de la API ejecutando:

```bash
php artisan test
```

## 🔗 Endpoints Principales

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | /api/login | Obtención de Token JWT. |
| GET | /api/products | Catálogo de figuras y camisetas con filtros. |
| GET | /api/products/{id} | Detalle con gestión manual de error 404. |
| POST | /api/orders | Compra de figuras/camisetas (Auth requerido). |
| PUT | /api/admin/orders/{id}/status | Gestión de estados de pedido (Solo Admin). |

## 👤 Sobre el Autor

Dorki - Graduado en Desarrollo de Aplicaciones Web (DAW).Residente en el País Vasco