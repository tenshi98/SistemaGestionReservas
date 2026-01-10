<?php
/*
* Tests para DataValidator
*/

//Seteo del Namespace
namespace Tests\Unit;

//Se instancias otras clases
use PHPUnit\Framework\TestCase;
use App\Validators\DataValidator;

//Se crea la clase
class DataValidatorTest extends TestCase {
    /*
    *===========================================================================
    * @vars
    */
    private $validator;

    /*
    *===========================================================================
    * setUp
    */
    protected function setUp(): void {
        $this->validator = new DataValidator();
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testRequiredValidation() {
        $this->assertTrue($this->validator->required('valor', 'campo'));
        $this->assertFalse($this->validator->required('', 'campo'));
        $this->assertFalse($this->validator->required(null, 'campo'));
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testEmailValidation() {
        $this->assertTrue($this->validator->email('test@example.com', 'email'));
        $this->assertFalse($this->validator->email('invalid-email', 'email'));
        $this->assertFalse($this->validator->email('test@', 'email'));
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testDateValidation() {
        $this->assertTrue($this->validator->date('2026-01-10', 'fecha'));
        $this->assertFalse($this->validator->date('2026-13-01', 'fecha'));
        $this->assertFalse($this->validator->date('invalid-date', 'fecha'));
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testSanitizeString() {
        $input = '<script>alert("xss")</script>Test';
        $expected = '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;Test';
        $this->assertEquals($expected, $this->validator->sanitizeString($input));
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testSanitizeEmail() {
        $input = 'test@example.com ';
        $expected = 'test@example.com';
        $this->assertEquals($expected, $this->validator->sanitizeEmail($input));
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testValidateMultipleFields() {
        $data = [
            'nombre' => 'Juan',
            'email'  => 'juan@example.com',
            'edad'   => 25
        ];

        $rules = [
            'nombre' => ['required', 'minLength:2'],
            'email'  => ['required', 'email'],
            'edad'   => ['required', 'integer']
        ];

        $this->assertTrue($this->validator->validate($data, $rules));
        $this->assertEmpty($this->validator->getErrors());
    }

    /*
    *===========================================================================
    * setUp
    */
    public function testValidateMultipleFieldsWithErrors() {
        $data = [
            'nombre' => '',
            'email'  => 'invalid',
            'edad'   => 'abc'
        ];

        $rules = [
            'nombre' => ['required'],
            'email'  => ['required', 'email'],
            'edad'   => ['required', 'integer']
        ];

        $this->assertFalse($this->validator->validate($data, $rules));
        $errors = $this->validator->getErrors();
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('nombre', $errors);
        $this->assertArrayHasKey('email', $errors);
    }
}
