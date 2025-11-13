<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PokeApiService
{
    protected string $baseUrl = 'https://pokeapi.co/api/v2';

    public function getPokemons(int $page = 1, int $limit = 20): ?array
    {
        $offset = ($page - 1) * $limit;
        $cacheKey = "pokemon_list_page_{$page}_{$limit}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::get($this->baseUrl . '/pokemon', [
            'offset' => $offset,
            'limit' => $limit,
        ]);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        $results = collect($data['results'] ?? [])->map(function ($item) {
            preg_match('/\/pokemon\/(\d+)\//', $item['url'], $matches);
            $id = $matches[1] ?? null;

            return [
                'id' => $id,
                'name' => $item['name'],
                'sprite' => $id
                    ? 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/' . $id . '.png'
                    : null,
                'url' => $item['url'],
            ];
        })->values()->all();

        $data['results'] = $results;

        Cache::put($cacheKey, $data, 3600);

        return $data;
    }

    public function getPokemonDetail($id): ?array
    {
        $cacheKey = 'pokemon_detail_' . $id;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $url = $this->baseUrl . '/pokemon/' . $id;
        $response = Http::get($url);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        $data['sprite'] = $data['sprites']['front_default'] ?? null;

        Cache::put($cacheKey, $data, 3600);

        return $data;
    }
}
