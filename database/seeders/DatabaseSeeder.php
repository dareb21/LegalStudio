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
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Ana María Torres',
    'email' => 'ana.torres@example.com',
    'phone' => '50490000002',
    'birthday' => '1990-06-22',
    'role' => 'Asistente',
]);

User::create([
    'name' => 'Carlos Alberto Díaz',
    'email' => 'carlos.diaz@example.com',
    'phone' => '50490000003',
    'birthday' => '1982-11-08',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Lucía Fernández',
    'email' => 'lucia.fernandez@example.com',
    'phone' => '50490000004',
    'birthday' => '1995-04-30',
    'role' => 'Asistente',
]);

User::create([
    'name' => 'Pedro José Martínez',
    'email' => 'pedro.martinez@example.com',
    'phone' => '50490000005',
    'birthday' => '1979-09-18',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Gabriela Ramírez',
    'email' => 'gabriela.ramirez@example.com',
    'phone' => '50490000006',
    'birthday' => '1993-12-12',
    'role' => 'Asistente',
]);

User::create([
    'name' => 'Andrés Felipe Ríos',
    'email' => 'andres.rios@example.com',
    'phone' => '50490000007',
    'birthday' => '1988-02-05',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Sofía Navarro',
    'email' => 'sofia.navarro@example.com',
    'phone' => '50490000008',
    'birthday' => '1996-08-09',
    'role' => 'Asistente',
]);

User::create([
    'name' => 'Diego Armando Morales',
    'email' => 'diego.morales@example.com',
    'phone' => '50490000009',
    'birthday' => '1987-01-27',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Mariana López',
    'email' => 'mariana.lopez@example.com',
    'phone' => '50490000010',
    'birthday' => '1991-07-19',
    'role' => 'Asistente',
]);
Laywer::create([
"id" =>1,
]);

Laywer::create([
"id" =>3,
]);

Laywer::create([
"id" =>5,
]);

Laywer::create([
"id" =>7,
]);

Laywer::create([
"id" =>9,
]);
Assistant::create([
"id" =>2,
"laywer_id" =>1,
]);
Assistant::create([
"id" =>4,
"laywer_id" =>3,
]);
Assistant::create([
"id" =>6,   
"laywer_id" =>5,
]);
Assistant::create([
"id" =>8,
"laywer_id" =>7,
]);
Assistant::create([
 "id" =>10,   
"laywer_id" =>9,
]);

Record::create([
    "name"=>"caso 1",
    "description"=>"Linganguliguliwachan",
]);
   
Record::create([
    "name"=>"caso 2",
    "description"=>"blablabla",
]);

Record::create([
    "name"=>"caso 3",
    "description"=>"blebleble",
]);
Record::create([
    "name"=>"caso 4",
    "description"=>"blublublu",
]);
    }
}
