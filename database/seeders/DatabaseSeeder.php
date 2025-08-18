<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Assistant;
use App\Models\Document;
use App\Models\Laywer;
use App\Models\Record;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
    'name' => 'Juan Gabriel Garcia',
    'email' => 'juan.gabriel@example.com',
    'phone' => '50490000001',
    'birthday' => '1985-03-15',
    'role' => 'asistente',
]);

User::create([
    'name' => 'Ana María Torres',
    'email' => 'ana.torres@example.com',
    'phone' => '50490000002',
    'birthday' => '1990-06-22',
    'role' => 'Abogado',
]);

    }
}
