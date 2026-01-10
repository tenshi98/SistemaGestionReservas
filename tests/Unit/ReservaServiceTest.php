<?php
/*
* Tests para ReservaService
*
* Tests de lógica de negocio para reservas
*/

//Seteo del Namespace
namespace Tests\Unit;

//Se instancias otras clases
use PHPUnit\Framework\TestCase;
use App\Services\ReservaService;
use App\Repositories\ReservaRepository;
use App\Repositories\SucursalRepository;
use App\Validators\DataValidator;
use App\Core\Database;
use App\Core\Logger;

//Se crea la clase
class ReservaServiceTest extends TestCase {
    /*
    *===========================================================================
    * @vars
    */
    private $service;
    private $reservaRepository;
    private $sucursalRepository;
    private $validator;
    private $db;
    private $logger;

    /*
    *===========================================================================
    * setUp
    */
    protected function setUp(): void {
        // Crear mocks
        $this->reservaRepository  = $this->createMock(ReservaRepository::class);
        $this->sucursalRepository = $this->createMock(SucursalRepository::class);
        $this->validator          = new DataValidator(); // Usar real para tests de validación
        $this->db                 = $this->createMock(Database::class);
        $this->logger             = $this->createMock(Logger::class);

        $this->service = new ReservaService(
            $this->reservaRepository,
            $this->sucursalRepository,
            $this->validator,
            $this->db,
            $this->logger
        );
    }

    /*
    *===========================================================================
    * Test: Service se inicializa correctamente
    */
    public function testServiceInitialization() {
        $this->assertInstanceOf(ReservaService::class, $this->service);
    }

    /*
    *===========================================================================
    * Test: processReservaData valida datos requeridos
    */
    public function testProcessReservaDataValidatesRequiredFields() {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/inválidos/');

        // Datos incompletos (falta Email)
        $data = [
            'NombreCompleto' => 'Juan Pérez',
            'Sucursal'       => 'Sucursal Centro',
            'Etapa'          => 'Confirmada'
        ];

        $this->service->processReservaData($data, 1);
    }

    /*
    *===========================================================================
    * Test: processReservaData valida formato de email
    */
    public function testProcessReservaDataValidatesEmailFormat() {
        $this->expectException(\Exception::class);

        $data = [
            'NombreCompleto' => 'Juan Pérez',
            'Email'          => 'email_invalido',
            'Sucursal'       => 'Sucursal Centro',
            'Etapa'          => 'Confirmada'
        ];

        $this->service->processReservaData($data, 1);
    }

    /*
    *===========================================================================
    * Test: processReservaData crea nueva reserva cuando no existe
    */
    public function testProcessReservaDataCreatesNewReservation() {
        // Configurar mocks
        $this->sucursalRepository
            ->method('getOrCreate')
            ->with('Sucursal Centro')
            ->willReturn(1);

        $this->reservaRepository
            ->method('findByEmailAndFecha')
            ->willReturn(null); // No existe

        $this->reservaRepository
            ->expects($this->once())
            ->method('create')
            ->willReturn(123);

        $this->db
            ->expects($this->once())
            ->method('beginTransaction');

        $this->db
            ->expects($this->once())
            ->method('commit');

        // Datos válidos
        $data = [
            'NombreCompleto' => 'Juan Pérez',
            'Email'          => 'juan@example.com',
            'Sucursal'       => 'Sucursal Centro',
            'Etapa'          => 'Confirmada'
        ];

        $result = $this->service->processReservaData($data, 1);

        $this->assertEquals('created', $result['action']);
        $this->assertEquals(123, $result['idReservas']);
    }

    /*
    *===========================================================================
    * Test: processReservaData actualiza reserva existente
    */
    public function testProcessReservaDataUpdatesExistingReservation() {
        // Configurar mocks
        $this->sucursalRepository
            ->method('getOrCreate')
            ->willReturn(1);

        $existingReserva = [
            'idReservas' => 456,
            'Email'      => 'juan@example.com',
            'Fecha'      => date('Y-m-d')
        ];

        $this->reservaRepository
            ->method('findByEmailAndFecha')
            ->willReturn($existingReserva); // Existe

        $this->reservaRepository
            ->expects($this->once())
            ->method('update')
            ->with(456, $this->anything());

        $this->db
            ->expects($this->once())
            ->method('beginTransaction');

        $this->db
            ->expects($this->once())
            ->method('commit');

        $data = [
            'NombreCompleto' => 'Juan Pérez',
            'Email'          => 'juan@example.com',
            'Sucursal'       => 'Sucursal Centro',
            'Etapa'          => 'Confirmada'
        ];

        $result = $this->service->processReservaData($data, 1);

        $this->assertEquals('updated', $result['action']);
        $this->assertEquals(456, $result['idReservas']);
    }

    /*
    *===========================================================================
    * Test: processReservaData hace rollback en caso de error
    */
    public function testProcessReservaDataRollbacksOnError() {
        $this->sucursalRepository
            ->method('getOrCreate')
            ->willThrowException(new \Exception('Error de prueba'));

        $this->db
            ->expects($this->once())
            ->method('beginTransaction');

        $this->db
            ->expects($this->once())
            ->method('rollback');

        $this->expectException(\Exception::class);

        $data = [
            'NombreCompleto' => 'Juan Pérez',
            'Email'          => 'juan@example.com',
            'Sucursal'       => 'Sucursal Centro',
            'Etapa'          => 'Confirmada'
        ];

        $this->service->processReservaData($data, 1);
    }

    /*
    *===========================================================================
    * Test: filterReservas sanitiza criterios
    */
    public function testFilterReservasSanitizesCriteria() {
        $this->reservaRepository
            ->method('filter')
            ->willReturn([]);

        $criteria = [
            'Etapa'      => '<script>alert("xss")</script>',
            'idSucursal' => '1'
        ];

        $result = $this->service->filterReservas($criteria, 1);

        $this->assertIsArray($result);
    }

    /*
    *===========================================================================
    * Test: filterReservas agrega idToken automáticamente
    */
    public function testFilterReservasAddsIdTokenAutomatically() {
        $this->reservaRepository
            ->expects($this->once())
            ->method('filter')
            ->with($this->callback(function ($criteria) {
                return isset($criteria['idToken']) && $criteria['idToken'] === 1;
            }))
            ->willReturn([]);

        $criteria = ['Etapa' => 'Confirmada'];

        $this->service->filterReservas($criteria, 1);
    }
}
