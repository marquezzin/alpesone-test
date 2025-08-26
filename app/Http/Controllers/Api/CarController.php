<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // per_page com fallback e limites
        $perPage = (int) $request->query('per_page', 15);
        if ($perPage <= 0) {
            $perPage = 15;
        }
        $perPage = min($perPage, 100); // evita per_page absurdos

        $cars = Car::with('photos')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($cars);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate($this->rules());
        $car = Car::create($validated);

        // Se houver fotos, salva elas
        if (!empty($validated['photos'])) {
            $photos = collect($validated['photos'])->map(fn($url) => ['url' => $url]);
            $car->photos()->createMany($photos->toArray());
        }

        return response()->json([
            'data' => $car->load('photos'),
            'message' => 'Carro registrado com sucesso.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $car = Car::with('photos')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Carro não encontrado'], 404);
        }

        return $car;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $car = Car::with('photos')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Carro não encontrado'], 404);
        }

        $validated = $request->validate($this->rules($id));

        $car->update($validated);

        // Se houver fotos, salva elas
        if (!empty($validated['photos'])) {
             $car->photos()->delete(); // remove fotos antigas
            $photos = collect($validated['photos'])->map(fn($url) => ['url' => $url]);
            $car->photos()->createMany($photos->toArray());
        }

        return response()->json([
            'data' => $car->load('photos'),
            'message' => 'Carro atualizado com sucesso.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Car::with('photos')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Carro não encontrado'], 404);
        }

        $car->delete();

        return response()->json([
            'message' => 'Carro deletado com sucesso.',
        ]);
    }


    //Regras de validação
    private function rules($id = null)
    {
        return [
            'type'         => 'required|string',
            'brand'        => 'required|string',
            'model'        => 'required|string',
            'version'      => 'required|string',
            'year_model'   => 'required|string',
            'year_build'   => 'required|string',
            'doors'        => 'nullable|integer',
            'board'        => 'required|string',
            'chassi'       => 'nullable|string',
            'transmission' => 'required|string',
            'km'           => 'nullable|string',
            'description'  => 'nullable|string',
            'created'      => 'nullable|date',
            'updated'      => 'nullable|date',
            'sold'         => 'boolean',
            'category'     => 'nullable|string',
            'url_car'      => [
                'required',
                'string',
                Rule::unique('cars', 'url_car')->ignore($id),
            ],
            'price'        => 'required|numeric',
            'old_price'    => 'nullable|numeric',
            'color'        => 'nullable|string',
            'fuel'         => 'nullable|string',
            'photos'       => 'nullable|array',
            'photos.*'     => 'url',
        ];
    }

}
