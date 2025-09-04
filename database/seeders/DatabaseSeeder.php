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


User::create([
    "email"=> 'fernandossrc@gmail.com',
    "name"=>'Luis Fernando Fuentes Tabora',
    "phone"=>"32310952",
    "birthday"=>"09/12/1834",
    "role"=>"Abogado"
]);
User::create([
    "email"=>'3190019@usap.edu',
    'name'=>'Luis fernando Asistente',
    'phone'=>'32323232',
    'birthday'=>'2002-04-12',
    'role'=>'Asistente'
]);

User::create([
    "email"=> 'palma.chn15@gmail.com',
    "name"=>'Carlos Daniel Palma Antunez',
    "phone"=>"95680555",
    "birthday"=>"29/05/2002",
    "role"=>"Asistente"
]);

    }
}
