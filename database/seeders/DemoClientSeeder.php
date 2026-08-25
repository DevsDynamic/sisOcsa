<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        if (config('services.osinergmin.environment') !== 'development') {
            $this->command?->warn('Cliente demo omitido porque el ambiente no es development.');
            return;
        }

        $token = env('OCSA_DEMO_TOKEN');
        if (blank($token)) {
            $this->command?->warn('Cliente demo omitido: falta OCSA_DEMO_TOKEN.');
            return;
        }

        Person::updateOrCreate(
            ['document_number' => '20000000001'],
            [
                'type_document_id' => 2,
                'full_name' => 'Cliente OCSA Demo',
                'email' => 'demo-ocsa@example.test',
                'type_person_id' => 1,
                'token' => $token,
                'status' => true,
                'is_demo' => true,
            ]
        );
    }
}
