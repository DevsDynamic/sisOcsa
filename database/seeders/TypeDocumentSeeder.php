<?php

namespace Database\Seeders;

use App\Models\TypeDocument;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeDocument::create([
            'name' => 'DNI',
            'max_length' => 8
        ]);

        TypeDocument::create([
            'name' => 'CE',
            'max_length' => 20
        ]);

        TypeDocument::create([
            'name' => 'RUC',
            'max_length' => 11
        ]);

        TypeDocument::create([
            'name' => 'PASAPORTE',
            'max_length' => 20
        ]);
    }
}
