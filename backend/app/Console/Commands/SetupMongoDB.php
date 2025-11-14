<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupMongoDB extends Command
{
    protected $signature = 'mongodb:setup';
    protected $description = 'Setup MongoDB collections and indexes';

    public function handle()
    {
        $this->info('Setting up MongoDB...');

        try {
            // Test connection first
            DB::connection('mongodb')->getMongoClient()->listDatabases();
            $this->info('✅ Connection successful');

            // Get database
            $database = DB::connection('mongodb')->getMongoClient()->selectDatabase(env('DB_DATABASE', 'pokemon_db'));
            $this->info("Using database: {$database->getDatabaseName()}");

            // Create collection if not exists
            $collectionName = 'pokemons';
            $collection = $database->selectCollection($collectionName);
            
            $this->info("Collection '{$collectionName}' ready");

            // Create indexes
            $this->info('Creating indexes...');
            
            $collection->createIndex(['name' => 1]);
            $this->info('✅ Name index created');
            
            $collection->createIndex(['is_favorite' => 1]);
            $this->info('✅ Favorite index created');
            
            $collection->createIndex(['created_at' => 1]);
            $this->info('✅ Created_at index created');
            
            // Create unique index for pokeapi_id
            $collection->createIndex(['pokeapi_id' => 1], ['unique' => true]);
            $this->info('✅ Unique pokeapi_id index created');

            $this->info('✅ All indexes created successfully');

            // Show collection count using countDocuments
            $count = $collection->countDocuments();
            $this->info("Collection count: " . $count);

        } catch (\Exception $e) {
            $this->error('❌ Setup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}