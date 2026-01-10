<?php
/*
* Tests para AuthMiddleware
*
* Tests de autenticación Bearer Token
*/

//Seteo del Namespace
namespace Tests\Unit;

//Se instancias otras clases
use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
use App\Repositories\TokenRepository;
use App\Core\Logger;

//Se crea la clase
class AuthMiddlewareTest extends TestCase {
    /*
    *===========================================================================
    * @vars
    */
    private $middleware;
    private $tokenRepository;
    private $logger;

    /*
    *===========================================================================
    * setUp
    */
    protected function setUp(): void {
        // Crear mocks
        $this->tokenRepository = $this->createMock(TokenRepository::class);
        $this->logger          = $this->createMock(Logger::class);
        $this->middleware      = new AuthMiddleware($this->tokenRepository, $this->logger);
    }

    /*
    *===========================================================================
    * Test: Middleware se inicializa correctamente
    */
    public function testMiddlewareInitialization() {
        $this->assertInstanceOf(AuthMiddleware::class, $this->middleware);
    }

    /*
    *===========================================================================
    * Test: handle() debería retornar false cuando no hay token
    */
    public function testHandleReturnsFalseWhenNoToken() {
        // Crear request sin token
        $request          = new \stdClass();
        $request->headers = [];
        $request->uri     = '/test';

        // El método handle() llama a ResponseHelper::unauthorized() que hace exit
        // Por lo tanto, este test verificaría el comportamiento pero causaría exit
        // En un entorno real, refactorizarías para inyectar ResponseHelper

        $this->markTestIncomplete(
            'Este test requiere refactorización de ResponseHelper para evitar exit()'
        );
    }

    /*
    *===========================================================================
    * Test: handle() debería retornar false cuando token es inválido
    */
    public function testHandleReturnsFalseWhenTokenIsInvalid() {
        // Configurar mock para retornar null (token no encontrado)
        $this->tokenRepository
            ->method('findByToken')
            ->willReturn(null);

        // Crear request con token inválido
        $request          = new \stdClass();
        $request->headers = ['AUTHORIZATION' => 'Bearer token_invalido'];
        $request->uri     = '/test';

        $this->markTestIncomplete(
            'Este test requiere refactorización de ResponseHelper para evitar exit()'
        );
    }

    /*
    *===========================================================================
    * Test: handle() debería inyectar idToken cuando token es válido
    */
    public function testHandleInjectsIdTokenWhenTokenIsValid() {
        // Configurar mock para retornar datos de token válido
        $tokenData = [
            'idToken' => 1,
            'Token'   => 'ejemplo_token_123456789',
            'Nombre'  => 'Token de Prueba'
        ];

        $this->tokenRepository
            ->method('findByToken')
            ->with('ejemplo_token_123456789')
            ->willReturn($tokenData);

        // Crear request con token válido
        $request          = new \stdClass();
        $request->headers = ['AUTHORIZATION' => 'Bearer ejemplo_token_123456789'];
        $request->uri     = '/test';

        // Ejecutar
        $result = $this->middleware->handle($request);

        // Verificar que se inyectó el idToken
        $this->assertIsObject($result);
        $this->assertEquals(1, $result->idToken);
        $this->assertEquals('Token de Prueba', $result->tokenName);
    }

    /*
    *===========================================================================
    * Test: Extracción de token del header Authorization
    */
    public function testTokenExtractionFromAuthorizationHeader() {
        $tokenData = [
            'idToken' => 2,
            'Token'   => 'otro_token_xyz',
            'Nombre'  => 'Otro Token'
        ];

        $this->tokenRepository
            ->method('findByToken')
            ->with('otro_token_xyz')
            ->willReturn($tokenData);

        // Probar diferentes formatos de header
        $request          = new \stdClass();
        $request->headers = ['AUTHORIZATION' => 'Bearer otro_token_xyz'];
        $request->uri     = '/test';

        $result = $this->middleware->handle($request);

        $this->assertIsObject($result);
        $this->assertEquals(2, $result->idToken);
    }

    /*
    *===========================================================================
    * Test: Token con espacios extra debería funcionar
    */
    public function testTokenWithExtraSpaces() {
        $tokenData = [
            'idToken' => 3,
            'Token'   => 'token_con_espacios',
            'Nombre'  => 'Token Espacios'
        ];

        $this->tokenRepository
            ->method('findByToken')
            ->with('token_con_espacios')
            ->willReturn($tokenData);

        $request          = new \stdClass();
        $request->headers = ['AUTHORIZATION' => 'Bearer   token_con_espacios  '];
        $request->uri     = '/test';

        $result = $this->middleware->handle($request);

        $this->assertIsObject($result);
        $this->assertEquals(3, $result->idToken);
    }
}
