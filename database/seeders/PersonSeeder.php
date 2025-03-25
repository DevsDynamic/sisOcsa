<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contacto creado en el seeder de User
        // Person::create([
        //     'type_document_id' => '1',
        //     'document_number' => '71183099',
        //     'full_name' => 'Contacto Valverde Ruiz',
        //     'email' => 'contacto@gmail.com',
        //     'type_person_id' => '1',
        // ]);

        Person::create([
            'type_document_id' => '1',
            'document_number' => '71183000',
            'full_name' => 'Cliente portencial Ruiz',
            'email' => 'cliente_potencial@gmail.com',
            'type_person_id' => '2',
        ]);
    }
}
