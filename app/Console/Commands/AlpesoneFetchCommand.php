<?php

namespace App\Console\Commands;

use App\Models\Car;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class AlpesoneFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alpesone:fetch';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baixa e salva o JSON da URL pública do Alpesone';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = config('services.alpesone.export_url');
        if (!$url) {
            $this->error('ALPESONE_EXPORT_URL não configurado no .env');
            return self::FAILURE;
        }

        //Evita rebaixar quando nada mudou no arquivo
        $etag = cache('alpesone:etag');
        $lastModified = cache('alpesone:last_modified');

        //Baixa o arquivo
        $response = Http::timeout(25)
            ->retry(3, 500)                 // 3 tentativas com backoff de 500ms
            ->acceptJson()
            ->withHeaders(array_filter([
                'If-None-Match'     => $etag, // So devolve se o etag mudou
                'If-Modified-Since' => $lastModified, // So devolve se o modified mudou
                'User-Agent'        => 'alpesone-test-importer/1.0',
            ]))
            ->get($url);

        if ($response->status() === 304) {
            $this->info('304 Not Modified: JSON não mudou desde a última execução.');
            return self::SUCCESS;
        }

        if (!$response->successful()) {
            $this->error("Falha HTTP {$response->status()}: ".$response->body());
            return self::FAILURE;
        }

        $raw = $response->body();
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Resposta não é JSON válido: '.json_last_error_msg());
            return self::FAILURE;
        }

        // Salva arquivo
        $dir = 'imports/alpesone';
        $filename = now()->format('Ymd_His').'.json';
        Storage::disk('local')->put("$dir/$filename", $raw);
        Storage::disk('local')->put("$dir/latest.json", $raw); // atalho para o último

        // Guarda ETag/Last-Modified para a próxima chamada condicional
        if ($response->header('ETag'))         cache()->forever('alpesone:etag', $response->header('ETag'));
        if ($response->header('Last-Modified')) cache()->forever('alpesone:last_modified', $response->header('Last-Modified'));

        // Contagem básica de registros (se a API vier como array ou em data[])
        $count = 0;
        if (is_array($data)) {
            $count = isset($data['data']) && is_array($data['data'])
                ? count($data['data'])
                : (array_is_list($data) ? count($data) : 1);
        }

        $this->info("OK! Salvo em storage/app/$dir/$filename. Registros estimados: $count");

        $this->info('Processando registros no banco...');

        foreach ($data as $item) {
            $car = Car::updateOrCreate(
                ['url_car' => $item['url_car']], //OBS: Esse vai ser meu parametro de unicidade, já que o id é externo e no meu banco é auto-increment
                [
                    'type'         => $item['type'] ?? null,
                    'brand'        => $item['brand'] ?? null,
                    'model'        => $item['model'] ?? null,
                    'version'      => $item['version'] ?? null,
                    'year_model'   => $item['year']['model'] ?? null,
                    'year_build'   => $item['year']['build'] ?? null,
                    'doors'        => $item['doors'] ?? null,
                    'board'        => $item['board'] ?? null,
                    'chassi'       => $item['chassi'] ?? null,
                    'transmission' => $item['transmission'] ?? null,
                    'km'           => $item['km'] ?? null,
                    'description'  => $item['description'] ?? null,
                    'created'      => $item['created'] ?? null,
                    'updated'      => $item['updated'] ?? null,
                    'sold'         => $item['sold'] ?? false,
                    'category'     => $item['category'] ?? null,
                    'price'        => $item['price'] ?? null,
                    'old_price'    => $item['old_price'] ?? null,
                    'color'        => $item['color'] ?? null,
                    'fuel'         => $item['fuel'] ?? null,
                ]
            );

            // Atualiza as fotos
            if (isset($item['fotos']) && is_array($item['fotos'])) {
                $car->photos()->delete(); // remove fotos antigas
                $photos = collect($item['fotos'])->map(fn($url) => ['url' => $url]);
                $car->photos()->createMany($photos->toArray()); // insere as novas
            }
        }

        $this->info("Registros salvos/atualizados com sucesso!");

        return self::SUCCESS;
    }

    
}
