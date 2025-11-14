<?php

namespace Database\Seeders;

use App\Models\Pokemon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PokemonSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data using MongoDB method
        DB::connection('mongodb')->collection('pokemons')->delete();

        // Create sample Pokémon data
        $pokemons = [
            [
                'pokeapi_id' => 1,
                'name' => 'bulbasaur',
                'types' => ['grass', 'poison'],
                'abilities' => [
                    ['ability' => ['name' => 'overgrow'], 'is_hidden' => false],
                    ['ability' => ['name' => 'chlorophyll'], 'is_hidden' => true]
                ],
                'stats' => [
                    ['stat' => ['name' => 'hp'], 'base_stat' => 45],
                    ['stat' => ['name' => 'attack'], 'base_stat' => 49],
                    ['stat' => ['name' => 'defense'], 'base_stat' => 49],
                    ['stat' => ['name' => 'special-attack'], 'base_stat' => 65],
                    ['stat' => ['name' => 'special-defense'], 'base_stat' => 65],
                    ['stat' => ['name' => 'speed'], 'base_stat' => 45]
                ],
                'sprite' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/1.png',
                'height' => 7,
                'weight' => 69,
                'is_favorite' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pokeapi_id' => 4,
                'name' => 'charmander',
                'types' => ['fire'],
                'abilities' => [
                    ['ability' => ['name' => 'blaze'], 'is_hidden' => false],
                    ['ability' => ['name' => 'solar-power'], 'is_hidden' => true]
                ],
                'stats' => [
                    ['stat' => ['name' => 'hp'], 'base_stat' => 39],
                    ['stat' => ['name' => 'attack'], 'base_stat' => 52],
                    ['stat' => ['name' => 'defense'], 'base_stat' => 43],
                    ['stat' => ['name' => 'special-attack'], 'base_stat' => 60],
                    ['stat' => ['name' => 'special-defense'], 'base_stat' => 50],
                    ['stat' => ['name' => 'speed'], 'base_stat' => 65]
                ],
                'sprite' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/4.png',
                'height' => 6,
                'weight' => 85,
                'is_favorite' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pokeapi_id' => 7,
                'name' => 'squirtle',
                'types' => ['water'],
                'abilities' => [
                    ['ability' => ['name' => 'torrent'], 'is_hidden' => false],
                    ['ability' => ['name' => 'rain-dish'], 'is_hidden' => true]
                ],
                'stats' => [
                    ['stat' => ['name' => 'hp'], 'base_stat' => 44],
                    ['stat' => ['name' => 'attack'], 'base_stat' => 48],
                    ['stat' => ['name' => 'defense'], 'base_stat' => 65],
                    ['stat' => ['name' => 'special-attack'], 'base_stat' => 50],
                    ['stat' => ['name' => 'special-defense'], 'base_stat' => 64],
                    ['stat' => ['name' => 'speed'], 'base_stat' => 43]
                ],
                'sprite' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/7.png',
                'height' => 5,
                'weight' => 90,
                'is_favorite' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($pokemons as $pokemonData) {
            Pokemon::create($pokemonData);
        }

        $this->command->info('Sample Pokémon data seeded successfully!');
        $this->command->info('Total Pokémon: ' . Pokemon::count());
    }
}