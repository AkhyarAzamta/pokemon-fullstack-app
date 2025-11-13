<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CoinController;
use App\Http\Controllers\Api\PokemonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('/products', ProductController::class);

Route::prefix('pokemon')->group(function () {
    Route::get('/', [PokemonController::class, 'index']);
    Route::get('/{id}', [PokemonController::class, 'show']);
    Route::post('/{id}/favorite', [PokemonController::class, 'toggleFavorite']);
});

Route::prefix('favorites')->group(function () {
    Route::get('/', [PokemonController::class, 'favorites']);
    Route::get('/search', [PokemonController::class, 'searchFavorites']);
    Route::get('/abilities', [PokemonController::class, 'favoriteAbilities']);
    Route::get('/ability/{ability}', [PokemonController::class, 'byAbility']);
});

Route::get('/test', function () {
    return response()->json(['message' => 'Pokemon API is working!']);
});

Route::post('/coins/count', [CoinController::class, 'countCoins']);