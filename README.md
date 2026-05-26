# MercadoHub Deluxe

Plataforma web de intercambio de items entre usuarios. Construida con PHP nativo, MySQL y vanilla JavaScript.

## Requisitos

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Servidor web (Apache / Nginx)

## Instalacion

### 1. Clonar el repositorio

```bash
git clone https://github.com/Gakuus/mercadohub.git
cd mercadohub
```

### 2. Configurar base de datos

```bash
mysql -u root -p < database/schema.sql
```

### 3. Configurar variables de entorno

```bash
cp .env.example .env
```

Editar `.env` con sus credenciales de base de datos:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=mercahub
DB_USER=root
DB_PASS=tu_contraseña
BASE_URL=/proyecto
```

### 4. Servir la aplicacion

Con PHP built-in server:

```bash
php -S localhost:8000
```

O configurar Apache/Nginx para que apunte al directorio del proyecto.

## Estructura del proyecto

```
mercadohub/
├── api/                # Endpoints REST (items, perfil, categorias)
├── auth/               # Autenticacion (login, registro, logout)
├── config/             # Configuracion centralizada (DB, constantes)
├── database/           # Schema SQL
├── Login/              # Pagina de inicio de sesion
├── Register/           # Pagina de registro
├── public/             # Dashboard principal (SPA)
├── .env.example        # Template de configuracion
├── .gitignore
└── README.md
```

## Endpoints API

| Endpoint | Metodo | Auth | Descripcion |
|---|---|---|---|
| `/api/getItems.php` | GET | Si | Listar items (filtro por categoria) |
| `/api/addItem.php` | POST | Si | Agregar un item |
| `/api/updateItem.php` | POST | Si | Actualizar un item (propio) |
| `/api/deleteItem.php` | POST | Si | Eliminar un item (propio) |
| `/api/getCategorias.php` | GET | Si | Listar categorias |
| `/api/getProfile.php` | GET | Si | Obtener perfil del usuario |
| `/api/updateUserProfile.php` | POST | Si | Actualizar foto de perfil |
| `/auth/login.php` | POST | No | Iniciar sesion |
| `/auth/register.php` | POST | No | Registrar usuario |
| `/auth/logout.php` | POST | Si | Cerrar sesion |

## Licencia

MIT
