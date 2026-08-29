<?php

namespace Database\Seeders;

use App\Models\TypePerson;
use App\Models\TypePersonTransition;
use Illuminate\Database\Seeder;

class CrmFlowSeeder extends Seeder
{
    public function run(): void
    {
        $cp = TypePerson::whereRaw('LOWER(code) = ?', ['cp'])->first();
        $co = TypePerson::whereRaw('LOWER(code) = ?', ['co'])->first();
        if (! $cp || ! $co) return;

        TypePersonTransition::updateOrCreate(
            ['from_type_person_id' => $cp->id, 'to_type_person_id' => $co->id],
            ['name' => 'Convertir prospecto en contacto', 'required_fields' => ['type_document_id', 'document_number', 'email'], 'active' => true]
        );
    }
}
