<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Api\CarController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class CarValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_car_data_passes_validation()
    {
        $controller = new CarController();
        $rules = $this->getRules($controller);

        // Data no padrão de salvamento no banco de dados
        // Testes com valores not null + photos
        $data = [
            'type' => 'carro',
            'brand' => 'Hyundai',
            'model' => 'CRETA',
            'version' => '1.6',
            'year_model' => '2025',
            'year_build' => '2025',
            'board' => 'ABC1234',
            'transmission' => 'Automática',
            'price' => 100000,
            'url_car' => 'creta-hyundai-2025',
            'photos' => [
                'http://example.com/photo1.jpg',
                'http://example.com/photo2.jpg',
            ],
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_car_data_fails_validation()
    {
        $controller = new CarController();
        $rules = $this->getRules($controller);

        $data = [
            'type' => null,
            'price' => 'não-numérico',
        ];

        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    private function getRules($controller)
    {
        return (new \ReflectionClass($controller))
            ->getMethod('rules')
            ->invoke($controller);
    }
}
