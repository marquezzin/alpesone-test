<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarPhoto extends Model
{
    use HasFactory;

    protected $table = 'cars_photos';
    
    protected $fillable = [
        'car_id',
        'url',
    ];

    // Relacionamento com o carro
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
