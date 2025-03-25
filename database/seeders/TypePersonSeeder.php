<?php

namespace Database\Seeders;

use App\Models\TypePerson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypePersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypePerson::create([
            'code'=> 'CO',
            'name' => 'Contacto',
            'description' => 'Personas con algún servicio de la empresa'
        ]);

        TypePerson::create([
            'code'=> 'CP',
            'name' => 'Cliente potencial',
            'description' => 'Personas interesadas en nuestros productos'
        ]);
    }
}
