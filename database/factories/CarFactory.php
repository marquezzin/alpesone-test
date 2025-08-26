<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        return [
            'type'         => $this->faker->randomElement(['Hatch', 'Sedan', 'SUV', 'Pickup']),
            'brand'        => $this->faker->company(),
            'model'        => $this->faker->word(),
            'version'      => $this->faker->word(),
            'year_model'   => $this->faker->numberBetween(1995, now()->year + 1),
            'year_build'   => $this->faker->numberBetween(1990, now()->year),
            'doors'        => $this->faker->randomElement([2, 3, 4, 5]),
            'board'        => strtoupper($this->faker->bothify('???-####')), // placa do carro
            'chassi'       => strtoupper($this->faker->regexify('[A-Z0-9]{17}')),
            'transmission' => $this->faker->randomElement(['Manual', 'Automática', 'CVT']),
            'km'           => $this->faker->numberBetween(0, 300000),
            'description'  => $this->faker->sentence(10),
            'created'      => now(),
            'updated'      => now(),
            'sold'         => $this->faker->boolean(),
            'category'     => $this->faker->randomElement(['Novo', 'Usado', 'Seminovo']),
            'url_car'      => $this->faker->url(),
            'price'        => $this->faker->randomFloat(2, 20000, 250000),
            'old_price'    => $this->faker->randomFloat(2, 20000, 250000),
            'color'        => $this->faker->safeColorName(),
            'fuel'         => $this->faker->randomElement(['Gasolina', 'Álcool', 'Flex', 'Diesel', 'Elétrico']),
        ];
    }
}
