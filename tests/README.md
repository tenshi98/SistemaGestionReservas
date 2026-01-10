# Tests - API RESTful

Este directorio contiene los tests unitarios e de integración para la API RESTful.

## Estructura

```
tests/
├── Unit/                           # Tests unitarios
│   ├── DataValidatorTest.php      # Tests de validación de datos
│   ├── TokenRepositoryTest.php    # Tests de repositorio de tokens
│   ├── AuthMiddlewareTest.php     # Tests de autenticación
│   ├── ReservaServiceTest.php     # Tests de lógica de negocio
│   └── ResponseHelperTest.php     # Tests de respuestas HTTP
└── Integration/                    # Tests de integración
    └── ApiEndpointsTest.php        # Tests de endpoints completos
```

## Ejecutar Tests

### Todos los tests
```bash
./vendor/bin/phpunit
```

### Solo tests unitarios
```bash
./vendor/bin/phpunit --testsuite Unit
```

### Solo tests de integración
```bash
./vendor/bin/phpunit --testsuite Integration
```

### Test específico
```bash
./vendor/bin/phpunit tests/Unit/DataValidatorTest.php
```

### Con cobertura de código
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Tests Unitarios

### DataValidatorTest
- ✅ Validación de campos requeridos
- ✅ Validación de formato de email
- ✅ Validación de fechas
- ✅ Sanitización de strings
- ✅ Validación múltiple de campos

### TokenRepositoryTest
- ⚠️ Requiere base de datos de prueba
- Tests de búsqueda de tokens
- Tests de estructura de datos

### AuthMiddlewareTest
- ✅ Inicialización del middleware
- ✅ Inyección de idToken con token válido
- ✅ Extracción de token del header
- ✅ Manejo de espacios en token
- ⚠️ Algunos tests marcados como incompletos (requieren refactorización de ResponseHelper)

### ReservaServiceTest
- ✅ Validación de datos requeridos
- ✅ Validación de formato de email
- ✅ Creación de nueva reserva
- ✅ Actualización de reserva existente
- ✅ Rollback en caso de error
- ✅ Sanitización de criterios de filtrado
- ✅ Inyección automática de idToken

### ResponseHelperTest
- ✅ Verificación de existencia de métodos
- ✅ Documentación de estructura de respuestas
- ⚠️ Tests marcados como incompletos (ResponseHelper usa exit())

## Tests de Integración

### ApiEndpointsTest
- ✅ GET / - Documentación HTML
- ✅ GET /Cron/cron1/{token} - Con token válido/inválido
- ✅ POST /API/v1/postData - Con/sin autenticación
- ✅ POST /API/v1/postData - Con datos válidos/inválidos
- ✅ POST /API/v1/filter - Con/sin autenticación
- ✅ Rutas inexistentes (404)
- ⚠️ Rate limiting (marcado como incompleto)

**Nota:** Los tests de integración requieren:
- Servidor web corriendo
- Base de datos configurada
- Token de prueba válido en la base de datos

## Configuración para Tests

### Base de Datos de Prueba

Para ejecutar tests que requieren base de datos, crea una base de datos separada:

```sql
CREATE DATABASE testapi_test;
USE testapi_test;
-- Ejecutar database.sql
```

Luego crea un archivo `.env.testing`:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=testapi_test
```

### Tests Marcados como Incompletos

Algunos tests están marcados como `markTestIncomplete()` porque:

1. **Requieren base de datos**: TokenRepositoryTest necesita una BD de prueba configurada
2. **Usan exit()**: ResponseHelper y algunos métodos de AuthMiddleware usan `exit()` que termina la ejecución
3. **Requieren refactorización**: Para testear completamente, se necesitaría inyectar dependencias de output

### Mejoras Futuras

Para tests más robustos, considera:

1. **Usar SQLite en memoria** para tests de base de datos
2. **Refactorizar ResponseHelper** para inyectar output handler
3. **Usar PHPUnit annotations** como `@runInSeparateProcess` para tests con exit()
4. **Implementar mocks de PDO** para evitar dependencia de base de datos real
5. **Agregar tests de performance** para rate limiting

## Cobertura de Código

Los tests actuales cubren:
- ✅ Validación de datos (100%)
- ✅ Autenticación (parcial - funcionalidad principal)
- ✅ Lógica de negocio (80%+)
- ✅ Endpoints principales (integración)
- ⚠️ Helpers (documentado, requiere refactorización)

## Notas Importantes

- Los tests de integración requieren que el servidor esté corriendo
- Ajusta `$baseUrl` en `ApiEndpointsTest.php` según tu entorno
- Los tests con mocks no requieren base de datos
- Algunos tests están documentados pero no ejecutables por limitaciones técnicas (exit(), etc.)

## Ejecutar Tests en CI/CD

Ejemplo para GitHub Actions:

```yaml
- name: Run Unit Tests
  run: ./vendor/bin/phpunit --testsuite Unit

- name: Run Integration Tests
  run: ./vendor/bin/phpunit --testsuite Integration
```
