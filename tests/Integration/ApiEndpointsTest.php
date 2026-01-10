<?php
/*
* Tests de Integración para Endpoints de la API
*
* Estos tests verifican el flujo completo de request/response
* Requieren que el servidor esté corriendo y la base de datos configurada
*/

//Seteo del Namespace
namespace Tests\Integration;

//Se instancias otras clases
use PHPUnit\Framework\TestCase;

//Se crea la clase
class ApiEndpointsTest extends TestCase {
    /*
    *===========================================================================
    * @vars
    */
    private $baseUrl;
    private $validToken;

    /*
    *===========================================================================
    * setUp
    */
    protected function setUp(): void {
        // Configurar URL base (ajustar según tu entorno)
        $this->baseUrl    = 'http://localhost/testapi';
        $this->validToken = 'ejemplo_token_123456789';

        // Verificar que el servidor esté disponible
        if (!$this->isServerAvailable()) {
            $this->markTestSkipped('Servidor no disponible en ' . $this->baseUrl);
        }
    }

    /*
    *===========================================================================
    * Verifica si el servidor está disponible
    */
    private function isServerAvailable() {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode > 0;
    }

    /*
    *===========================================================================
    * Realiza una petición HTTP
    */
    private function makeRequest($method, $endpoint, $data = null, $headers = []) {
        $ch = curl_init($this->baseUrl . $endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $headers[] = 'Content-Type: application/json';
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    /*
    *===========================================================================
    * Test: GET / debería retornar documentación HTML
    */
    public function testIndexReturnsDocumentation() {
        $ch = curl_init($this->baseUrl . '/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('<!DOCTYPE html>', $response);
        $this->assertStringContainsString('API RESTful', $response);
    }

    /*
    *===========================================================================
    * Test: GET /Cron/cron1/{token} con token válido
    */
    public function testCronWithValidToken() {
        $response = $this->makeRequest('GET', '/Cron/cron1/' . $this->validToken);

        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['body']['success']);
        $this->assertEquals('Hola mundo', $response['body']['data']['response']);
    }

    /*
    *===========================================================================
    * Test: GET /Cron/cron1/{token} con token inválido
    */
    public function testCronWithInvalidToken() {
        $response = $this->makeRequest('GET', '/Cron/cron1/token_invalido_xyz');

        $this->assertEquals(401, $response['code']);
        $this->assertFalse($response['body']['success']);
    }

    /*
    *===========================================================================
    * Test: POST /API/v1/postData sin autenticación
    */
    public function testPostDataWithoutAuthentication() {
        $data = [
            'NombreCompleto' => 'Test Usuario',
            'Email'          => 'test@example.com',
            'Sucursal'       => 'Test Sucursal',
            'Etapa'          => 'Test'
        ];

        $response = $this->makeRequest('POST', '/API/v1/postData', $data);

        $this->assertEquals(401, $response['code']);
        $this->assertFalse($response['body']['success']);
    }

    /*
    *===========================================================================
    * Test: POST /API/v1/postData con autenticación válida
    */
    public function testPostDataWithValidAuthentication() {
        $data = [
            'NombreCompleto' => 'Test Usuario',
            'Email'          => 'test@example.com',
            'Sucursal'       => 'Test Sucursal',
            'Etapa'          => 'Test'
        ];

        $headers  = ['Authorization: Bearer ' . $this->validToken];
        $response = $this->makeRequest('POST', '/API/v1/postData', $data, $headers);

        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['body']['success']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertContains($response['body']['data']['action'], ['created', 'updated']);
    }

    /*
    *===========================================================================
    * Test: POST /API/v1/postData con datos inválidos
    */
    public function testPostDataWithInvalidData() {
        $data = [
            'NombreCompleto' => 'Test',
            'Email'          => 'email_invalido', // Email inválido
            'Sucursal'       => 'Test',
            'Etapa'          => 'Test'
        ];

        $headers = ['Authorization: Bearer ' . $this->validToken];
        $response = $this->makeRequest('POST', '/API/v1/postData', $data, $headers);

        $this->assertEquals(400, $response['code']);
        $this->assertFalse($response['body']['success']);
    }

    /*
    *===========================================================================
    * Test: POST /API/v1/filter sin autenticación
    */
    public function testFilterWithoutAuthentication() {
        $response = $this->makeRequest('POST', '/API/v1/filter', []);

        $this->assertEquals(401, $response['code']);
        $this->assertFalse($response['body']['success']);
    }

    /*
    *===========================================================================
    * Test: POST /API/v1/filter con autenticación válida
    */
    public function testFilterWithValidAuthentication() {
        $criteria = ['Etapa' => 'Test'];

        $headers  = ['Authorization: Bearer ' . $this->validToken];
        $response = $this->makeRequest('POST', '/API/v1/filter', $criteria, $headers);

        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['body']['success']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('total', $response['body']['data']);
        $this->assertArrayHasKey('reservas', $response['body']['data']);
    }

    /*
    *===========================================================================
    * Test: Ruta inexistente debería retornar 404
    */
    public function testNonExistentRouteReturns404() {
        $response = $this->makeRequest('GET', '/ruta/inexistente');

        $this->assertEquals(404, $response['code']);
        $this->assertFalse($response['body']['success']);
    }

    /*
    *===========================================================================
    * Test: Rate limiting (requiere múltiples requests)
    */
    public function testRateLimiting() {
        $this->markTestIncomplete(
            'Test de rate limiting requiere configuración específica y múltiples requests'
        );

        // Código de ejemplo:
        // for ($i = 0; $i < 101; $i++) {
        //     $response = $this->makeRequest('GET', '/');
        // }
        // 
        // // La request 101 debería ser bloqueada
        // $this->assertEquals(429, $response['code']);
    }
}
