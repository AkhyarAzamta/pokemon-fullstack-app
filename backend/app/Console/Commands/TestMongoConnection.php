<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class TestMongoConnection extends Command
{
    protected $signature = 'mongo:test';
    protected $description = 'Test MongoDB Atlas connection';

    public function handle()
    {
        try {
            // Test koneksi dasar
            $this->info('Testing MongoDB Atlas connection...');
            
            // Method 1: Coba akses database
            DB::connection('mongodb')->getMongoClient()->listDatabases();
            $this->info('✅ MongoDB Atlas connection successful!');
            
            // Method 2: Coba operasi sederhana
            $database = DB::connection('mongodb')->getMongoClient()->selectDatabase(env('DB_DATABASE', 'pokemon_db'));
            $this->info('✅ Database access successful!');
            
            // List collections
            $collections = $database->listCollections();
            $collectionNames = [];
            foreach ($collections as $collection) {
                $collectionNames[] = $collection->getName();
            }
            
            $this->info('Available collections: ' . implode(', ', $collectionNames));
            
        } catch (Exception $e) {
            $this->error('❌ MongoDB Atlas connection failed: ' . $e->getMessage());
            $this->error('Error details: ' . get_class($e));
            return 1;
        }

        return 0;
    }
}