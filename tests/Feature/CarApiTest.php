<?php

// tests/Feature/CarApiTest.php
namespace Tests\Feature;

use App\Models\Car;
use App\Models\CarPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_protected_routes()
    {
        $this->getJson('/api/cars')->assertStatus(401);
        $this->postJson('/api/cars', [])->assertStatus(401);
        $this->getJson('/api/cars/1')->assertStatus(401);
        $this->putJson('/api/cars/1', [])->assertStatus(401);
        $this->deleteJson('/api/cars/1')->assertStatus(401);
    }


        public function index_is_paginated_and_returns_expected_structure()
    {
        Sanctum::actingAs(User::factory()->create());

        // Gera 1 carro com fotos para validar inclusão do relacionamento
        Car::factory()->has(CarPhoto::factory()->count(2), 'photos')->create();

        // +24 carros (total 25) para testar paginação
        Car::factory()->count(24)->create();

        $resp = $this->getJson('/api/cars?per_page=10')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id','brand','model','year_model','price',
                        'photos' => [
                            '*' => ['id','url','car_id']
                        ]
                    ]
                ],
                'current_page','per_page','total','last_page',
                'from','to','path','first_page_url','last_page_url','next_page_url','prev_page_url'
            ]);

        // Deve trazer exatamente 10 itens na página 1
        $this->assertCount(10, $resp->json('data'));
        $this->assertSame(1, $resp->json('current_page'));
        $this->assertSame(10, (int) $resp->json('per_page'));
        $this->assertSame(25, (int) $resp->json('total')); // 25 criados
    }

    #[Test]
    public function index_second_page_metadata_is_correct()
    {
        Sanctum::actingAs(User::factory()->create());

        Car::factory()->count(25)->create();

        $resp = $this->getJson('/api/cars?page=2&per_page=10')
            ->assertOk()
            ->assertJsonStructure(['data','current_page','per_page','total','last_page'])
            ->assertJsonFragment(['current_page' => 2]);

        // Página 2 com per_page=10 também deve ter 10 itens (dos 25)
        $this->assertCount(10, $resp->json('data'));
        $this->assertSame(10, (int) $resp->json('per_page'));
        $this->assertSame(25, (int) $resp->json('total'));
    }

    #[Test]
    public function per_page_is_limited_when_too_large()
    {
        Sanctum::actingAs(User::factory()->create());

        // cria 120 para testar clamp de per_page
        Car::factory()->count(120)->create();

        $resp = $this->getJson('/api/cars?per_page=999') // pedindo 999
            ->assertOk()
            ->assertJsonStructure(['data','current_page','per_page','total','last_page']);

        // Controller limita per_page a 100
        $this->assertSame(100, (int) $resp->json('per_page'));
        $this->assertCount(100, $resp->json('data'));
        $this->assertSame(120, (int) $resp->json('total'));
    }

    #[Test]
    public function per_page_invalid_or_zero_falls_back_to_default_15()
    {
        Sanctum::actingAs(User::factory()->create());

        Car::factory()->count(40)->create();

        $resp = $this->getJson('/api/cars?per_page=0')
            ->assertOk();

        // fallback configurado no controller: 15
        $this->assertSame(15, (int) $resp->json('per_page'));
        $this->assertCount(15, $resp->json('data'));
        $this->assertSame(40, (int) $resp->json('total'));
    }

    #[Test]
    public function authenticated_user_can_show_a_single_car_with_photos()
    {
        Sanctum::actingAs(User::factory()->create());

        $car = Car::factory()
            ->has(CarPhoto::factory()->count(3), 'photos')
            ->create();

        $this->getJson("/api/cars/{$car->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $car->id])
            ->assertJsonCount(3, 'photos');
    }

    #[Test]
    public function store_validates_payload_and_creates_car()
    {
        Sanctum::actingAs(User::factory()->create());

        // inválido: valida os campos REAIS exigidos no Controller
        $this->postJson('/api/cars', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'type','brand','model','version','year_model',
                'year_build','board','transmission','url_car','price'
            ]);

        // válido
        $payload = [
            'type'         => 'Hatch',
            'brand'        => 'Ford',
            'model'        => 'Ka',
            'version'      => 'SE 1.0',
            'year_model'   => '2019',              
            'year_build'   => '2018',              
            'board'        => 'ABC-1234',
            'transmission' => 'Manual',
            'url_car'      => 'https://example.com/cars/ka',
            'price'        => 45000.55,
            // opcional: fotos como array de URLs
            'photos'       => [
                'https://picsum.photos/seed/car1/800/600',
                'https://picsum.photos/seed/car2/800/600',
            ],
        ];

        $res = $this->postJson('/api/cars', $payload)
            ->assertOk()
            ->assertJsonStructure(['data' => ['id','brand','model','year_model','price','photos']])
            ->assertJsonFragment([
                'brand' => 'Ford',
                'model' => 'Ka',
                'year_model' => '2019',
            ]);

        // Confirma fotos salvas
        $res->assertJsonCount(2, 'data.photos');

        // Confirma que persistiu no banco
        $this->assertDatabaseHas('cars', [
            'brand' => 'Ford',
            'model' => 'Ka',
            'year_model' => '2019',
            'price' => 45000.55,
            'url_car' => 'https://example.com/cars/ka',
        ]);
    }

    #[Test]
    public function update_validates_and_persists_changes()
    {
        Sanctum::actingAs(User::factory()->create());

        // cria um carro inicial com campos mínimos obrigatórios
        $car = Car::factory()->create([
            'type'         => 'Hatch',
            'brand'        => 'VW',
            'model'        => 'Gol',
            'version'      => '1.6',
            'year_model'   => '2020',
            'year_build'   => '2019',
            'board'        => 'DEF-5678',
            'transmission' => 'Manual',
            'url_car'      => 'https://example.com/cars/gol',
            'price'        => 50000,
        ]);


        // válido: atualiza price e fotos
            $this->putJson("/api/cars/{$car->id}", [
                'type'         => 'Hatch',
                'brand'        => 'VW',
                'model'        => 'Gol',
                'version'      => '1.6',
                'year_model'   => '2020',
                'year_build'   => '2019',
                'board'        => 'DEF-5678',
                'transmission' => 'Manual',
                'url_car'      => 'https://example.com/cars/gol',
                'price'        => 52000,
                'photos'       => ['https://picsum.photos/seed/updated1/800/600'],
            ])
            ->assertOk()
            ->assertJsonFragment(['price' => 52000])
            ->assertJsonCount(1, 'data.photos');

        $this->assertDatabaseHas('cars', ['id' => $car->id, 'price' => 52000]);
    }

    #[Test]
    public function destroy_deletes_the_car()
    {
        Sanctum::actingAs(User::factory()->create());

        $car = Car::factory()->create([
            'type'         => 'Hatch',
            'brand'        => 'Fiat',
            'model'        => 'Argo',
            'version'      => 'Drive',
            'year_model'   => '2021',
            'year_build'   => '2020',
            'board'        => 'GHI-9012',
            'transmission' => 'Manual',
            'url_car'      => 'https://example.com/cars/argo',
            'price'        => 62000,
        ]);

        $this->deleteJson("/api/cars/{$car->id}")
            ->assertOk() // controller retorna 200 com message
            ->assertJsonFragment(['message' => 'Carro deletado com sucesso.']);

        $this->assertDatabaseMissing('cars', ['id' => $car->id]);
    }


}
