<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'username' => 'contacto@gmail.com',
            'password' => bcrypt('123456789'),
        ]);

        $user->assignRole('Administrador');

        // Crear la persona y asignarle el user_id
        Person::create([
            'type_document_id' => 1,
            'document_number' => '71183099',
            'full_name' => 'Contacto Valverde Ruiz',
            'email' => 'contacto@gmail.com',
            'type_person_id' => 1,
            'user_id' => $user->id, // Relacionar con el usuario
            'token' => '2bddab5e433a19ac9ebf05b83ad41dca5594a742'
        ]);
    }
}
