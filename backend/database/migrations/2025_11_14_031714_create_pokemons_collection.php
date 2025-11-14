<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up()
    {
        // Untuk MongoDB, kita tidak bisa menggunakan Schema seperti MySQL
        // Sebagai gantinya, kita buat collection dan index langsung melalui DB
        $collection = 'pokemons';
        
        // Create indexes
        DB::connection('mongodb')
            ->collection($collection)
            ->createIndex(['name' => 1]);
            
        DB::connection('mongodb')
            ->collection($collection)
            ->createIndex(['is_favorite' => 1]);
            
        DB::connection('mongodb')
            ->collection($collection)
            ->createIndex(['pokeapi_id' => 1], ['unique' => true]);
            
        DB::connection('mongodb')
            ->collection($collection)
            ->createIndex(['created_at' => 1]);
    }

    public function down()
    {
        // Drop the collection
        DB::connection('mongodb')->collection('pokemons')->drop();
    }
};