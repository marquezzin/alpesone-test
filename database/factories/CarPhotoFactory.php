<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarPhotoFactory extends Factory
{
    protected $model = CarPhoto::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(), // relaciona com um carro novo por padrão
            'url'    => $this->faker->imageUrl(800, 600, 'car', true),
        ];
    }
}
