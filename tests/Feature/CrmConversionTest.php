<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\TypeDocument;
use App\Models\TypePerson;
use App\Models\TypePersonTransition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_flow_converts_prospect_and_keeps_history(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        $cp = TypePerson::create(['name' => 'Cliente potencial', 'code' => 'CP', 'status' => true]);
        $co = TypePerson::create(['name' => 'Contacto', 'code' => 'CO', 'status' => true]);
        $document = TypeDocument::create(['name' => 'RUC', 'max_length' => 11, 'status' => true]);
        TypePersonTransition::create(['from_type_person_id' => $cp->id, 'to_type_person_id' => $co->id, 'name' => 'Conversión', 'active' => true]);
        $prospect = Person::create(['full_name' => 'Empresa interesada', 'type_person_id' => $cp->id, 'status' => true]);

        $this->actingAs($owner)->postJson(route('people.convert', $prospect), [
            'type_document_id' => $document->id,
            'document_number' => '20123456789',
            'email' => 'ventas@example.com',
            'phone_number' => '999888777',
            'reason' => 'Contrató el servicio',
            'marketing_consent' => true,
        ])->assertOk();

        $prospect->refresh();
        $this->assertSame($co->id, $prospect->type_person_id);
        $this->assertTrue($prospect->marketing_consent);
        $this->assertNotNull($prospect->converted_at);
        $this->assertDatabaseHas('person_type_histories', [
            'person_id' => $prospect->id,
            'from_type_person_id' => $cp->id,
            'to_type_person_id' => $co->id,
            'changed_by' => $owner->id,
        ]);
    }
}
