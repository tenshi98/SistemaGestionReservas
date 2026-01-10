<?php
/*
* Clase IndexController
*
* Controlador para la página de inicio que muestra la documentación de la API
* Genera una interfaz HTML con Bulma CSS estilo Swagger
*
* @package App\Controllers
*/

//Seteo del Namespace
namespace App\Controllers;

//Se crea la clase
class IndexController {
    /*
    *===========================================================================
    * Muestra la documentación de la API
    *
    * @param \stdClass $request Objeto request
    * @return void
    */
    public function index($request) {
        $html = $this->generateDocumentation();

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }

    /*
    *===========================================================================
    * Genera el HTML de la documentación
    *
    * @return string HTML de la documentación
    */
    private function generateDocumentation() {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API RESTful - Documentación</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .endpoint { margin-bottom: 2rem; }
        .method-badge { font-weight: bold; padding: 0.25rem 0.75rem; border-radius: 4px; }
        .method-get { background-color: #3298dc; color: white; }
        .method-post { background-color: #48c774; color: white; }
        .code-block { background-color: #f5f5f5; padding: 1rem; border-radius: 4px; overflow-x: auto; }
        pre { margin: 0; }
    </style>
</head>
<body>
    <section class="hero is-primary">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    <i class="fas fa-code"></i> API RESTful - Documentación
                </h1>
                <p class="subtitle">
                    Sistema de gestión de reservas con autenticación Bearer Token
                </p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">

            <!-- Información General -->
            <div class="box">
                <h2 class="title is-4"><i class="fas fa-info-circle"></i> Información General</h2>
                <div class="content">
                    <p><strong>Base URL:</strong> <code>{$_SERVER['HTTP_HOST']}</code></p>
                    <p><strong>Versión:</strong> 1.0</p>
                    <p><strong>Autenticación:</strong> Bearer Token (excepto para rutas públicas)</p>
                    <p><strong>Formato de respuesta:</strong> JSON</p>
                </div>
            </div>

            <!-- Autenticación -->
            <div class="box">
                <h2 class="title is-4"><i class="fas fa-lock"></i> Autenticación</h2>
                <div class="content">
                    <p>La mayoría de los endpoints requieren autenticación mediante Bearer Token en el header:</p>
                    <div class="code-block">
                        <pre>Authorization: Bearer {tu_token_aqui}</pre>
                    </div>
                </div>
            </div>

            <!-- Endpoints -->
            <div class="box">
                <h2 class="title is-4"><i class="fas fa-route"></i> Endpoints Disponibles</h2>

                <!-- Endpoint: Index -->
                <div class="endpoint">
                    <h3 class="title is-5">
                        <span class="method-badge method-get">GET</span> /
                    </h3>
                    <p><strong>Descripción:</strong> Muestra esta documentación</p>
                    <p><strong>Autenticación:</strong> No requerida</p>
                    <p><strong>Respuesta:</strong> HTML</p>
                </div>

                <hr>

                <!-- Endpoint: Cron -->
                <div class="endpoint">
                    <h3 class="title is-5">
                        <span class="method-badge method-get">GET</span> /Cron/cron1/{token}
                    </h3>
                    <p><strong>Descripción:</strong> Endpoint de prueba para cron jobs</p>
                    <p><strong>Autenticación:</strong> Token vía parámetro GET</p>
                    <p><strong>Parámetros:</strong></p>
                    <ul>
                        <li><code>token</code> (string, requerido): Token de autenticación</li>
                    </ul>
                    <p><strong>Ejemplo de uso:</strong></p>
                    <div class="code-block">
                        <pre>curl -X GET "http://{$_SERVER['HTTP_HOST']}/Cron/cron1/tu_token_aqui"</pre>
                    </div>
                    <p><strong>Respuesta exitosa (200):</strong></p>
                    <div class="code-block">
                        <pre>{
  "success": true,
  "message": "Cron ejecutado correctamente",
  "data": {
    "cron": "cron1",
    "response": "Hola mundo"
  }
}</pre>
                    </div>
                </div>

                <hr>

                <!-- Endpoint: Post Data -->
                <div class="endpoint">
                    <h3 class="title is-5">
                        <span class="method-badge method-post">POST</span> /API/v1/postData
                    </h3>
                    <p><strong>Descripción:</strong> Recibe y procesa datos de reservas</p>
                    <p><strong>Autenticación:</strong> Bearer Token requerido</p>
                    <p><strong>Content-Type:</strong> application/json</p>
                    <p><strong>Parámetros del body:</strong></p>
                    <ul>
                        <li><code>NombreCompleto</code> (string, requerido): Nombre completo del cliente</li>
                        <li><code>Email</code> (string, requerido): Email del cliente</li>
                        <li><code>Sucursal</code> (string, requerido): Nombre de la sucursal</li>
                        <li><code>Etapa</code> (string, requerido): Etapa de la reserva</li>
                        <li><code>Nombre</code> (string, opcional): Nombre</li>
                        <li><code>ApellidoPat</code> (string, opcional): Apellido paterno</li>
                        <li><code>ApellidoMat</code> (string, opcional): Apellido materno</li>
                    </ul>
                    <p><strong>Ejemplo de uso:</strong></p>
                    <div class="code-block">
                        <pre>curl -X POST "http://{$_SERVER['HTTP_HOST']}/API/v1/postData" \\
  -H "Authorization: Bearer tu_token_aqui" \\
  -H "Content-Type: application/json" \\
  -d '{
    "NombreCompleto": "Juan Pérez González",
    "Email": "juan.perez@example.com",
    "Sucursal": "Sucursal Centro",
    "Etapa": "Confirmada",
    "Nombre": "Juan",
    "ApellidoPat": "Pérez",
    "ApellidoMat": "González"
  }'</pre>
                    </div>
                    <p><strong>Respuesta exitosa - Nueva reserva (200):</strong></p>
                    <div class="code-block">
                        <pre>{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "action": "created",
    "idReservas": 123,
    "message": "Reserva creada correctamente"
  }
}</pre>
                    </div>
                    <p><strong>Respuesta exitosa - Reserva actualizada (200):</strong></p>
                    <div class="code-block">
                        <pre>{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "action": "updated",
    "idReservas": 123,
    "message": "Reserva actualizada correctamente"
  }
}</pre>
                    </div>
                </div>

                <hr>

                <!-- Endpoint: Filter -->
                <div class="endpoint">
                    <h3 class="title is-5">
                        <span class="method-badge method-post">POST</span> /API/v1/filter
                    </h3>
                    <p><strong>Descripción:</strong> Filtra reservas según criterios</p>
                    <p><strong>Autenticación:</strong> Bearer Token requerido</p>
                    <p><strong>Content-Type:</strong> application/json</p>
                    <p><strong>Parámetros del body (todos opcionales):</strong></p>
                    <ul>
                        <li><code>idSucursal</code> (int o array, opcional): ID de sucursal(es) a filtrar</li>
                        <li><code>Etapa</code> (string, opcional): Etapa de la reserva</li>
                        <li><code>Fecha</code> (string, opcional): Fecha específica (Y-m-d)</li>
                        <li><code>FechaInicio</code> (string, opcional): Fecha inicio del rango (Y-m-d)</li>
                        <li><code>FechaFin</code> (string, opcional): Fecha fin del rango (Y-m-d)</li>
                        <li><code>idSendMail</code> (int, opcional): Estado de envío de email</li>
                    </ul>
                    <p><strong>Ejemplo de uso - Filtro simple:</strong></p>
                    <div class="code-block">
                        <pre>curl -X POST "http://{$_SERVER['HTTP_HOST']}/API/v1/filter" \\
  -H "Authorization: Bearer tu_token_aqui" \\
  -H "Content-Type: application/json" \\
  -d '{
    "Etapa": "Confirmada",
    "idSendMail": 0
  }'</pre>
                    </div>
                    <p><strong>Ejemplo de uso - Filtro con múltiples sucursales y rango de fechas:</strong></p>
                    <div class="code-block">
                        <pre>curl -X POST "http://{$_SERVER['HTTP_HOST']}/API/v1/filter" \\
  -H "Authorization: Bearer tu_token_aqui" \\
  -H "Content-Type: application/json" \\
  -d '{
    "idSucursal": [1, 2, 3],
    "FechaInicio": "2026-01-01",
    "FechaFin": "2026-01-31"
  }'</pre>
                    </div>
                    <p><strong>Respuesta exitosa (200):</strong></p>
                    <div class="code-block">
                        <pre>{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "total": 2,
    "reservas": [
      {
        "idReservas": 123,
        "NombreCompleto": "Juan Pérez",
        "Email": "juan@example.com",
        "NombreSucursal": "Sucursal Centro",
        "Etapa": "Confirmada",
        "Fecha": "2026-01-10",
        "FechaAcceso": "2026-01-10 12:00:00",
        "idSendMail": 0
      }
    ]
  }
}</pre>
                    </div>
                </div>

            </div>

            <!-- Códigos de Error -->
            <div class="box">
                <h2 class="title is-4"><i class="fas fa-exclamation-triangle"></i> Códigos de Respuesta</h2>
                <div class="content">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>200</code></td>
                                <td>Operación exitosa</td>
                            </tr>
                            <tr>
                                <td><code>400</code></td>
                                <td>Solicitud inválida (datos incorrectos)</td>
                            </tr>
                            <tr>
                                <td><code>401</code></td>
                                <td>No autorizado (token inválido o no proporcionado)</td>
                            </tr>
                            <tr>
                                <td><code>404</code></td>
                                <td>Ruta no encontrada</td>
                            </tr>
                            <tr>
                                <td><code>429</code></td>
                                <td>Demasiadas solicitudes (rate limit excedido)</td>
                            </tr>
                            <tr>
                                <td><code>500</code></td>
                                <td>Error interno del servidor</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>

    <footer class="footer">
        <div class="content has-text-centered">
            <p>
                <strong>API RESTful</strong> - Sistema de gestión de reservas
            </p>
        </div>
    </footer>
</body>
</html>
HTML;
    }
}
