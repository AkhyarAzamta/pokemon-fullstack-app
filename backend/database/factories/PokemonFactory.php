<?php

namespace Database\Factories;

use App\Models\Pokemon;
use Illuminate\Database\Eloquent\Factories\Factory;

class PokemonFactory extends Factory
{
    protected $model = Pokemon::class;

    public function definition()
    {
        return [
            'pokeapi_id' => $this->faker->unique()->numberBetween(1, 1000),
            'name' => $this->faker->word,
            'types' => ['grass', 'poison'],
            'abilities' => [
                ['ability' => ['name' => 'overgrow'], 'is_hidden' => false],
                ['ability' => ['name' => 'chlorophyll'], 'is_hidden' => true]
            ],
            'stats' => [
                ['stat' => ['name' => 'hp'], 'base_stat' => 45],
                ['stat' => ['name' => 'attack'], 'base_stat' => 49]
            ],
            'sprite' => $this->faker->imageUrl(),
            'height' => $this->faker->numberBetween(1, 100),
            'weight' => $this->faker->numberBetween(1, 1000),
            'is_favorite' => true,
        ];
    }
}