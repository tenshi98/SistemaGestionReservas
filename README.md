# API RESTful - Sistema de Gestión de Reservas

API RESTful completa desarrollada en PHP con MySQL, autenticación Bearer Token, arquitectura modular limpia y buenas prácticas de desarrollo.

## 📋 Tabla de Contenidos

- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
  - [1. Clonar o Descargar el Proyecto](#1-clonar-o-descargar-el-proyecto)
  - [2. Configurar Permisos](#1-configurar-permisos)
  - [3. Configuración de Base de Datos](#3-configuración-de-base-de-datos)
  - [4. Configuración de Archivos](#4-configuración-de-archivos)
  - [5. Instalación en cPanel](#5-instalación-en-cpanel)
- [Configuración](#-configuración)
- [Cómo Ejecutar el Proyecto](#-cómo-ejecutar-el-proyecto)
- [Ejemplos de Uso](#-ejemplos-de-uso)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Explicación de Módulos](#-explicación-de-módulos)
- [Migración a Otras Bases de Datos](#-migración-a-otras-bases-de-datos)
- [Solución de Problemas](#-solución-de-problemas)
- [Notas Adicionales](#-notas-adicionales)

---

## 📡 Tecnologías Utilizadas

- **PHP** 7.0+ (compatible con PHP 8.x)
- **MySQL** 5.7+ / MariaDB 10.2+
- **PDO** para conexión a base de datos
- **Arquitectura Modular** con separación de responsabilidades
- **PSR-4** Autoloading
- **Bulma CSS** para documentación interactiva
- **PHPUnit** para tests unitarios

---

## ✨ Características

✅ **API RESTful** siguiendo buenas prácticas
✅ **Autenticación Bearer Token** robusta
✅ **Rate Limiting** para prevenir abuso
✅ **Routing Engine** personalizado con soporte para parámetros dinámicos
✅ **Arquitectura Modular** (Core, Middleware, Controllers, Services, Repositories)
✅ **Validación y Sanitización** de datos
✅ **Logging** completo (info, warning, error)
✅ **Manejo de Errores** robusto con excepciones
✅ **Transacciones** de base de datos
✅ **CORS** configurable
✅ **Documentación Interactiva** estilo Swagger
✅ **Tests Unitarios** con PHPUnit

---

## 📦 Requisitos

### Requisitos del Servidor

- PHP >= 7.0 (recomendado PHP 7.4 o superior)
- MySQL >= 5.7 o MariaDB >= 10.2
- Apache con mod_rewrite habilitado
- Extensiones PHP requeridas:
  - PDO
  - pdo_mysql
  - json
  - mbstring

### Requisitos Opcionales

- Composer (para gestión de dependencias y tests)
- Git (para control de versiones)

---

## 🚀 Instalación

### 1. Clonar o Descargar el Proyecto

```bash
git clone https://github.com/tenshi98/SistemaGestionReservas.git
cd SistemaGestionReservas
```

### 2. Configurar Permisos

```bash
chmod -R 755 .
chmod -R 777 logs/  # Crear directorio si no existe
```

### 3. Configuración de Base de Datos

#### Opción A: Usando el script SQL

```bash
# Conectar a MySQL
mysql -u root -p

# Ejecutar schema
mysql -u root -p < database/schema.sql
```

#### Opción B: Manualmente

1. Accede a phpMyAdmin o tu cliente MySQL
2. Crea una nueva base de datos llamada `SistemaGestionReservas`
3. Ejecuta el contenido del archivo `database/schema.sql`

El script creará las siguientes tablas:
- `tokens_listado`: Tokens autorizados
- `sucursal_listado`: Sucursales disponibles
- `reservas_listado`: Reservas realizadas

También insertará datos de ejemplo para pruebas.

### 4. Configuración de Archivos

1. **Copiar archivo de configuración:**
```bash
cp .env.example .env
```

2. **Editar `.env` con tus credenciales:**
```env
DB_HOST=localhost
DB_USER=tu_usuario
DB_PASS=tu_contraseña
DB_NAME=SistemaGestionReservas
DB_CHARSET=utf8mb4

APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Santiago

RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=60

LOG_LEVEL=info
LOG_PATH=logs/

CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization
```

### 5. Instalación en cPanel

#### Paso 1: Subir Archivos

1. Accede a tu cPanel
2. Ve a **Administrador de Archivos**
3. Navega a `public_html` (o el directorio de tu dominio)

**Estructura final en cPanel:**
```
public_html/
└── SistemaGestionReservas/
    ├── App/     # Código de la aplicación
    ├── Public/  # Punto de entrada público
    ├── logs/    # Archivos de log
    └── .env     # Configuración (no versionado)
```

#### Paso 2: Configurar Dominio/Subdominio

1. En cPanel, ve a **Dominios** o **Subdominios**
2. Crea un nuevo subdominio (ej: `api.tudominio.com`)
3. Configura la **Raíz del Documento** apuntando a la carpeta `Public` del proyecto (donde está `index.php`)
4. Guarda los cambios

#### Paso 3: Configurar Permisos

```bash
# Establece permisos de sólo lectura para el archivo de configuración (.env debería ser seguro)
chmod 644 .env

# Permite que el propietario acceda y ejecute dentro del directorio App (lector/ejecutor para grupo y otros)
chmod 755 App

# Permite que el propietario lea/escriba/ejecute en logs; grupo/otros pueden listar (ajustar a 775/777 si el servidor web necesita escribir)
chmod 755 logs

# Archivos públicos: lectura por todos, sin permisos de ejecución
chmod 644 Public/index.php
chmod 644 Public/.htaccess
```

#### Paso 4: Verificar mod_rewrite

En cPanel, ve a **Seleccionar versión de PHP** y asegúrate de que:
- La versión de PHP sea >= 7.0
- Las extensiones `pdo`, `pdo_mysql`, `json`, `mbstring` estén habilitadas

---

## ⚙️ Configuración

### Variables de Entorno (.env)

| Variable | Descripción | Valor por Defecto |
|----------|-------------|-------------------|
| `DB_HOST` | Host de la base de datos | `localhost` |
| `DB_USER` | Usuario de MySQL | `root` |
| `DB_PASS` | Contraseña de MySQL | `` |
| `DB_NAME` | Nombre de la base de datos | `SistemaGestionReservas` |
| `DB_CHARSET` | Charset de la conexión | `utf8mb4` |
| `APP_ENV` | Entorno (development/production) | `development` |
| `APP_DEBUG` | Mostrar errores detallados | `true` |
| `RATE_LIMIT_REQUESTS` | Máximo de requests por ventana | `100` |
| `RATE_LIMIT_WINDOW` | Ventana de tiempo en segundos | `60` |
| `LOG_LEVEL` | Nivel mínimo de log | `info` |
| `CORS_ALLOWED_ORIGINS` | Orígenes permitidos para CORS | `*` |

---

## 🔌 Cómo Ejecutar el Proyecto

### Desarrollo Local

1. **Con PHP integrado:**
```bash
cd Public
php -S localhost:8000
```

2. **Con Apache:**
   - Configura un VirtualHost apuntando a la carpeta `Public`
   - Accede a `http://localhost/SistemaGestionReservas`

3. **Con Docker (LAMP):**
```bash
# El proyecto ya está en /lamp/www/SistemaGestionReservas
# Accede a: http://localhost/SistemaGestionReservas
```

### Producción

Sigue los pasos de [Instalación en cPanel](#5-instalación-en-cpanel) y accede a tu dominio configurado.

---

## 🏃 Ejemplos de Uso

### 1. Ver Documentación

```bash
curl http://localhost/SistemaGestionReservas/
```

Abre en el navegador para ver la documentación interactiva con Bulma CSS.

### 2. Ejecutar Cron

```bash
curl -X GET "http://localhost/SistemaGestionReservas/Cron/cron1/ejemplo_token_123456789"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Cron ejecutado correctamente",
  "data": {
    "cron": "cron1",
    "response": "Hola mundo",
    "executed_at": "2026-01-10 12:00:00",
    "token_name": "Token de Prueba"
  }
}
```

### 3. Crear/Actualizar Reserva

```bash
curl -X POST "http://localhost/SistemaGestionReservas/API/v1/postData" \
  -H "Authorization: Bearer ejemplo_token_123456789" \
  -H "Content-Type: application/json" \
  -d '{
    "NombreCompleto": "Juan Pérez González",
    "Email": "juan.perez@example.com",
    "Sucursal": "Sucursal Centro",
    "Etapa": "Confirmada",
    "Nombre": "Juan",
    "ApellidoPat": "Pérez",
    "ApellidoMat": "González"
  }'
```

**Respuesta (Nueva):**
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "action": "created",
    "idReservas": 1,
    "message": "Reserva creada correctamente"
  }
}
```

**Respuesta (Actualización):**
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "action": "updated",
    "idReservas": 1,
    "message": "Reserva actualizada correctamente"
  }
}
```

### 4. Filtrar Reservas

```bash
# Filtro simple
curl -X POST "http://localhost/SistemaGestionReservas/API/v1/filter" \
  -H "Authorization: Bearer ejemplo_token_123456789" \
  -H "Content-Type: application/json" \
  -d '{
    "Etapa": "Confirmada",
    "idSendMail": 0
  }'

# Filtro con múltiples sucursales y rango de fechas
curl -X POST "http://localhost/SistemaGestionReservas/API/v1/filter" \
  -H "Authorization: Bearer ejemplo_token_123456789" \
  -H "Content-Type: application/json" \
  -d '{
    "idSucursal": [1, 2, 3],
    "FechaInicio": "2026-01-01",
    "FechaFin": "2026-01-31"
  }'
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Reservas filtradas correctamente",
  "data": {
    "total": 1,
    "reservas": [
      {
        "idReservas": 1,
        "NombreCompleto": "Juan Pérez González",
        "Email": "juan.perez@example.com",
        "NombreSucursal": "Sucursal Centro",
        "Etapa": "Confirmada",
        "Fecha": "2026-01-10",
        "FechaAcceso": "2026-01-10 12:00:00",
        "idSendMail": 0
      }
    ]
  }
}
```

---

## 📁 Estructura del Proyecto

```
SistemaGestionReservas/
├── App/                             # Código de la aplicación
│   ├── Core/                        # Componentes fundamentales
│   │   ├── Config.php               # Gestión de configuración
│   │   ├── Database.php             # Conexión PDO a MySQL
│   │   ├── Logger.php               # Sistema de logging
│   │   └── Router.php               # Motor de enrutamiento
│   ├── Middleware/                  # Middleware de la aplicación
│   │   ├── AuthMiddleware.php       # Autenticación Bearer Token
│   │   ├── CorsMiddleware.php       # Headers CORS
│   │   └── RateLimitMiddleware.php  # Control de rate limiting
│   ├── Controllers/                 # Controladores
│   │   ├── ApiController.php        # Endpoints de API v1
│   │   ├── CronController.php       # Endpoints de cron
│   │   └── IndexController.php      # Documentación
│   ├── Models/                      # Modelos de datos
│   │   ├── Reserva.php
│   │   ├── Sucursal.php
│   │   └── Token.php
│   ├── Repositories/                # Acceso a datos
│   │   ├── ReservaRepository.php
│   │   ├── SucursalRepository.php
│   │   └── TokenRepository.php
│   ├── Services/                    # Lógica de negocio
│   │   ├── ReservaService.php
│   │   └── SucursalService.php
│   ├── Validators/                  # Validación de datos
│   │   └── DataValidator.php
│   └── Helpers/                     # Utilidades
│       └── ResponseHelper.php
├── Public/                          # Punto de entrada público
│   ├── index.php                    # Bootstrap de la aplicación
│   ├── .htaccess                    # Configuración Apache
│   └── assets/                      # Archivos estáticos
├── logs/                            # Archivos de log
├── tests/                           # Tests unitarios
│   └── Unit/
│       └── DataValidatorTest.php
├── database/                        # Punto de entrada público
│   └── schema.sql                   # Script de base de datos
├── .env                             # Configuración (no versionado)
├── .env.example                     # Plantilla de configuración
├── .gitignore                       # Archivos ignorados por Git
├── composer.json                    # Dependencias PHP
├── phpunit.xml                      # Configuración de tests
├── database.sql                     # Script de base de datos
└── README.md                        # Este archivo
```

---

## 🔧 Explicación de Módulos

### Core

- **Config**: Carga y gestiona variables de entorno desde `.env`
- **Database**: Conexión PDO con Singleton, reconexión automática y transacciones
- **Logger**: Registro de eventos con niveles (info, warning, error) y rotación por fecha
- **Router**: Sistema de enrutamiento con soporte para parámetros dinámicos y middleware

### Middleware

- **AuthMiddleware**: Valida Bearer Token contra la base de datos
- **CorsMiddleware**: Configura headers CORS para permitir consumo desde otros orígenes
- **RateLimitMiddleware**: Limita requests por IP para prevenir abuso

### Controllers

- **IndexController**: Genera documentación HTML interactiva con Bulma CSS
- **CronController**: Maneja endpoints de cron jobs con autenticación vía GET
- **ApiController**: Endpoints principales (postData, filter)

### Services

- **ReservaService**: Lógica de negocio para reservas (insert/update, filtrado)
- **SucursalService**: Gestión de sucursales con validación

### Repositories

- **ReservaRepository**: Operaciones CRUD y filtrado complejo con JOIN
- **SucursalRepository**: Gestión de sucursales con validación de unicidad
- **TokenRepository**: Validación y gestión de tokens

### Validators

- **DataValidator**: Validación y sanitización de datos con reglas flexibles

### Helpers

- **ResponseHelper**: Respuestas JSON estandarizadas con códigos HTTP apropiados

---

## 🔄 Migración a Otras Bases de Datos

El proyecto está diseñado para facilitar la migración a otros motores de base de datos.

### Pasos para Migrar

1. **Crear nuevo adaptador de base de datos:**
   - Copia `App/Core/Database.php` a `App/Core/DatabasePostgreSQL.php` (ejemplo)
   - Modifica el DSN y opciones específicas del motor

2. **Actualizar configuración:**
   - Agrega `DB_DRIVER=pgsql` en `.env`
   - Modifica `Database.php` para usar el driver configurado

3. **Adaptar queries:**
   - Los Repositories usan PDO preparado, compatible con la mayoría de motores
   - Ajusta sintaxis específica si es necesario (ej: `AUTO_INCREMENT` vs `SERIAL`)

### Ejemplo para PostgreSQL

```php
// En Database.php, modificar el DSN:
$dsn = sprintf(
    'pgsql:host=%s;dbname=%s',
    $this->config['host'],
    $this->config['name']
);
```

---

## 🐛 Solución de Problemas

### Error: "Archivo .env no encontrado"

**Solución:** Copia `.env.example` a `.env` y configura las variables.

```bash
cp .env.example .env
```

### Error: "No se pudo establecer conexión con la base de datos"

**Causas posibles:**
- Credenciales incorrectas en `.env`
- MySQL no está corriendo
- Firewall bloqueando conexión

**Solución:**
1. Verifica credenciales en `.env`
2. Comprueba que MySQL esté activo: `sudo service mysql status`
3. Prueba conexión: `mysql -u usuario -p`

### Error 404 en todas las rutas

**Causa:** mod_rewrite no está habilitado o `.htaccess` no se está leyendo.

**Solución:**
1. Habilita mod_rewrite: `sudo a2enmod rewrite`
2. Verifica `AllowOverride All` en configuración de Apache
3. Reinicia Apache: `sudo service apache2 restart`

### Rate Limit se activa incorrectamente

**Causa:** Archivos temporales acumulados.

**Solución:**
```bash
# Limpiar archivos de rate limit
rm -rf /tmp/rate_limit/*
```

### Logs no se generan

**Causa:** Permisos incorrectos en directorio `logs/`.

**Solución:**
```bash
chmod 755 logs/
chmod 644 logs/*.log
```

### Error: "Class not found"

**Causa:** Autoloader no encuentra las clases.

**Solución:**
1. Verifica que la estructura de carpetas coincida con los namespaces
2. Si usas Composer: `composer dump-autoload`

---

## 📝 Notas Adicionales

### Seguridad

- **Producción:** Cambia `APP_DEBUG=false` en `.env`
- **Tokens:** Genera tokens seguros (mínimo 32 caracteres aleatorios)
- **HTTPS:** Usa siempre HTTPS en producción para proteger tokens
- **Rate Limiting:** Ajusta según tus necesidades en `.env`

### Performance

- **Caché:** Considera implementar Redis para rate limiting en alta carga
- **Índices:** Las tablas ya tienen índices optimizados
- **Logs:** Implementa rotación automática de logs en producción

### Mantenimiento

- **Logs:** Revisa regularmente `logs/` y elimina archivos antiguos
- **Rate Limit:** Ejecuta limpieza periódica de archivos temporales
- **Backups:** Realiza backups regulares de la base de datos

### Tests

```bash
# Instalar dependencias
composer install

# Ejecutar tests
./vendor/bin/phpunit

# Con cobertura
./vendor/bin/phpunit --coverage-html coverage/
```

