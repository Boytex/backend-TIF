<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Backend — Sistema de Gestión de Incidencias Técnicas

Una institución educativa necesita registrar y realizar el seguimiento de los problemas técnicos informados por sus usuarios. Actualmente estos problemas se comunican verbalmente, por correo electrónico o mediante mensajería, lo que dificulta conocer cuáles continúan pendientes, quién los informó, qué técnico se encuentra trabajando sobre cada caso y cuáles ya fueron solucionados.


## Requisitos

- PHP 8.2 o superior.
- Composer 2.
- MySQL o MariaDB (por ejemplo, el servicio MySQL de XAMPP).
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo` y `curl`.

## Instalación en Windows

1. Iniciar MySQL desde XAMPP y crear una base de datos vacía llamada `tif_incidencias` con cotejamiento `utf8mb4_unicode_ci`.
2. Abrir CMD o la terminal de VS Code dentro de la carpeta `tif-incidencias-backend`.
3. Instalar dependencias:

   ```bash
   composer install
   ```

4. Crear el archivo de entorno:

   ```bat
   copy .env.example .env
   ```

5. Editar `.env` y comprobar `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` según la instalación local de MySQL.
6. Generar la clave y crear las tablas/datos de prueba:

   ```bash
   C:\php84\php.exe artisan key:generate
   C:\php84\php.exe artisan migrate --seed
   ```

   Para reconstruir desde cero una base de desarrollo, usar `C:\php84\php.exe artisan migrate:fresh --seed`. **Este comando borra los datos existentes.**

7. Levantar la API:

   ```bash
   C:\php84\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```

La API queda disponible en `http://127.0.0.1:8000/api`.

## Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Usuario | `usuario@tif.com` | `password123` |
| Técnico | `tecnico@tif.com` | `password123` |

Cambiar estas contraseñas si se publica el sistema fuera del entorno local.

## Endpoints principales

Todas las rutas, salvo `POST /login`, requieren el encabezado `Authorization: Bearer TOKEN`.

| Método | Endpoint | Función |
|---|---|---|
| POST | `/api/login` | Autenticar y recibir token Sanctum |
| POST | `/api/logout` | Revocar el token actual |
| GET | `/api/user` | Consultar usuario autenticado |
| GET | `/api/categories` | Listar categorías precargadas |
| GET | `/api/incidencias` | Listar según rol; filtros `status` y `category_id` |
| POST | `/api/incidencias` | Crear incidencia (rol usuario) |
| GET | `/api/incidencias/{id}` | Ver detalle y comentarios |
| PUT | `/api/incidencias/{id}/tomar` | Tomar incidencia (técnico) |
| PUT | `/api/incidencias/{id}/resolver` | Resolver (técnico asignado) |
| GET | `/api/incidencias/{id}/comentarios` | Listar comentarios cronológicamente |
| POST | `/api/incidencias/{id}/comentarios` | Agregar comentario |

El backend también conserva alias en inglés `/api/incidents` y `/comments` para compatibilidad con el proyecto inicial.

### Ejemplos de JSON

Crear incidencia:

```json
{
  "title": "La impresora no responde",
  "description": "La impresora de secretaría no imprime los documentos.",
  "category_id": 4
}
```

Agregar comentario:

```json
{
  "comment": "Revisé la conexión y continúa sin responder."
}
```

Resolver incidencia:

```json
{
  "resolution": "Se reinstaló el controlador y se verificó una impresión de prueba."
}
```

## Reglas aplicadas en el servidor

- Un usuario solo consulta sus propias incidencias; el técnico consulta todas.
- Las incidencias nuevas se crean con estado `ABIERTA`.
- Solo un técnico puede tomar una incidencia `ABIERTA`; al tomarla pasa a `EN PROCESO` y queda asignada.
- Solo el técnico asignado puede resolver una incidencia `EN PROCESO`.
- Para pasar a `RESUELTA` es obligatoria una descripción de resolución.
- Los permisos no dependen de ocultar botones en el frontend: se verifican en Laravel.
- Los comentarios quedan asociados a su autor y se devuelven en orden cronológico.

## Integración con frontend

El frontend se configura en `tif-incidencias-frontend/assets/js/config.js`. El valor inicial apunta a `http://127.0.0.1:8000/api`. La configuración CORS permite las solicitudes HTTP locales entre los dos proyectos. Antes de publicar el backend, reemplazar el origen abierto de `config/cors.php` por el dominio real del frontend.

## Pruebas

```bash
C:\php84\php.exe artisan test
```
