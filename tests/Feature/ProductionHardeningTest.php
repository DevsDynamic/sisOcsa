<?php

namespace Tests\Feature;

use App\Models\Osinergmin;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_ocsa_token_is_never_returned_by_people_endpoints(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        $person = Person::create([
            'full_name' => 'Cliente protegido',
            'token' => 'token-ocsa-super-secreto',
            'status' => 1,
        ]);

        $response = $this->actingAs($owner)->getJson(route('people.edit', $person));

        $response->assertOk()
            ->assertJsonMissing(['token' => 'token-ocsa-super-secreto'])
            ->assertJsonPath('html.token_configured', true);
        $this->assertStringNotContainsString('token-ocsa-super-secreto', $response->getContent());
    }

    public function test_customer_report_only_contains_own_active_environment(): void
    {
        $user = User::factory()->create();
        $ownPerson = Person::create(['user_id' => $user->id, 'full_name' => 'Propio', 'status' => 1]);
        $otherPerson = Person::create(['full_name' => 'Ajeno', 'status' => 1]);

        Osinergmin::create([
            'person_id' => $ownPerson->id,
            'environment' => 'development',
            'uuid' => 'OWN-UNIT',
            'plate' => 'OWN-001',
            'response_status' => 'SUCCESS',
        ]);
        Osinergmin::create([
            'person_id' => $otherPerson->id,
            'environment' => 'development',
            'uuid' => 'OTHER-UNIT',
            'plate' => 'OTHER-001',
            'response_status' => 'SUCCESS',
        ]);
        Osinergmin::create([
            'person_id' => $ownPerson->id,
            'environment' => 'production',
            'uuid' => 'OWN-PROD',
            'plate' => 'OWN-PRD',
            'response_status' => 'SUCCESS',
        ]);

        $response = $this->actingAs($user)->getJson(route('reports.view-osinergmin', [
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
            'draw' => 1,
            'start' => 0,
            'length' => 25,
        ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 1)
            ->assertJsonPath('data.0.uuid', 'OWN-UNIT');
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/login')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }
}
