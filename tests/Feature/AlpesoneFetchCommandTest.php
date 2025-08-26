<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

//Este teste finge uma resposta HTTP da API e garante que o comando salva o JSON corretamente.
class AlpesoneFetchCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetch_command_stores_latest_json()
    {
        Http::fake([
            '*' => Http::response(json_encode([
                [
                    'url_car' => 'teste-carro',
                    'type' => 'carro',
                    'brand' => 'Hyundai',
                    'model' => 'HB20',
                    'version' => '1.0 Sense',
                    'year' => ['model' => '2025', 'build' => '2025'],
                    'doors' => 4,
                    'board' => 'ABC1234',
                    'chassi' => 'XYZ987',
                    'transmission' => 'Automática',
                    'km' => '10000',
                    'description' => 'Carro novo, revisado',
                    'created' => now()->toDateTimeString(),
                    'updated' => now()->toDateTimeString(),
                    'sold' => false,
                    'category' => 'Hatch',
                    'price' => 80000,
                    'old_price' => 85000,
                    'color' => 'Branco',
                    'fuel' => 'Flex',
                    'fotos' => [
                        'https://exemplo.com/foto1.jpg',
                        'https://exemplo.com/foto2.jpg',
                    ],
                ]
            ]), 200, [
                'ETag' => '123abc',
                'Last-Modified' => now()->toRfc7231String(),
            ]),
        ]);

        Storage::fake('local');

        Artisan::call('alpesone:fetch');

        Storage::assertExists('imports/alpesone/latest.json');

        $json = Storage::get('imports/alpesone/latest.json');
        $this->assertStringContainsString('teste-carro', $json);
    }
}

