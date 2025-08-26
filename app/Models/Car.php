<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Car extends Model
{
    use HasFactory;

    protected $table = 'cars';

    protected $fillable = [
        'type',
        'brand',
        'model',
        'version',
        'year_model',
        'year_build',
        'doors',
        'board',
        'chassi',
        'transmission',
        'km',
        'description',
        'created',
        'updated',
        'sold',
        'category',
        'url_car',
        'price',
        'old_price',
        'color',
        'fuel',
    ];

    // Relacionamento com fotos
    public function photos()
    {
        return $this->hasMany(CarPhoto::class);
    }
}

