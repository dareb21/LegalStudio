<?php

namespace Database\Seeders;

use App\Models\User;

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
    'name' => 'Juan José Martinez Espinal',
    'email' => 'jj_martinezespinal@estudiolegalmc.com',
    'phone' => '99556342',
    'birthday' => '1985-03-15',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Raul Edgardo Chinchilla Aguilar',
    'email' => 'Raul.ed.chinchilla@estudiolegalmc.com',
    'phone' => '98908152',
    'birthday' => '1990-06-22',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Carol Yolanda Martinez Mejia',
    'email' => 'Martinezcastellon@estudiolegalmc.com',
    'phone' => '32378650',
    'birthday' => '1990-06-22',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Fany Griselda Castellón Vallecillo',
    'email' => 'Fanycastellon@estudiolegalmc.com',
    'phone' => '99264747',
    'birthday' => '1990-06-22',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'José Carlos Martínez Castellón',
    'email' => 'Jcmartinez@estudiolegalmc.com',
    'phone' => '95277216',
    'birthday' => '1990-06-22',
    'role' => 'Abogado',
]);

User::create([
    'name' => 'Carlos Rene Antunez Lazo',
    'email' => 'Carlosantunez07@gmail.com',
    'phone' => '33899012',
    'birthday' => '1990-06-22',
    'role' => 'Asistente',
]);

User::create([
    'name' => 'Carlos David Carranza Martinez',
    'email' => 'Abgcarloscarranza@gmail.com',
    'phone' => '33955834',
    'birthday' => '1990-06-22',
    'role' => 'Asistente',
]);

User::create([
    'name' => 'Verónica Zamora',
    'email' => 'Vzamora1978@gmail.com',
    'phone' => '99043091',
    'birthday' => '1990-06-22',
    'role' => 'Asistente',
]);

User::create([
    "email"=> 'palma.chn15@gmail.com',
    "name"=>'Carlos Admin',
    "phone"=>"21212121",
    "birthday"=>"2002-12-20",
    "role"=>"Admin"
]);

User::create([
    "email"=> '2210088@usap.edu',
    "name"=>'Carlos Abogado',
    "phone"=>"45677623",
    "birthday"=>"2002-12-20",
    "role"=>"Abogado"
]);

User::create([
    "email"=> 'practicas25.carlospalma@usap.edu',
    "name"=>'Carlos Asistente',
    "phone"=>"22786501",
    "birthday"=>"2002-12-20",
    "role"=>"Asistente"
]);


User::create([
    "email"=> 'kindleluis17@gmail.com',
    "name"=>'Luis ADMIN',
    "phone"=>"90909090",
    "birthday"=>"2002-12-20",
    "role"=>"Admin"
]);

User::create([
    "email"=> 'fernandossrc@gmail.com',
    "name"=>'Luis Abogado',
    "phone"=>"32310952",
    "birthday"=>"1834-03-12",
    "role"=>"Abogado"
]);
User::create([
    "email"=>'3190019@usap.edu',
    'name'=>'Luis Asistente',
    'phone'=>'32323232',
    'birthday'=>'2002-04-12',
    'role'=>'Asistente'
]);


    }
}
