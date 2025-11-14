<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Pokemon extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pokemons';
    
    protected $fillable = [
        'pokeapi_id',
        'name',
        'types',
        'abilities',
        'stats',
        'sprite',
        'height',
        'weight',
        'is_favorite'
    ];
    
    protected $casts = [
        'types' => 'array',
        'abilities' => 'array',
        'stats' => 'array',
        'is_favorite' => 'boolean',
        'height' => 'integer',
        'weight' => 'integer',
        'pokeapi_id' => 'integer'
    ];

    // Timestamps otomatis di MongoDB
    public $timestamps = true;
}