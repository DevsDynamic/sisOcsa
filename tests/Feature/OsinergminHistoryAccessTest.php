<?php

namespace Tests\Feature;

use App\Models\Osinergmin;
use App\Models\Person;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OsinergminHistoryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_only_sees_own_history_from_active_environment(): void
    {
        $user = User::factory()->create();
        $person = Person::create(['user_id' => $user->id, 'full_name' => 'Cliente propio', 'status' => true]);
        $other = Person::create(['full_name' => 'Otro cliente', 'status' => true]);
        SystemSetting::create(['key' => 'osinergmin_environment', 'value' => 'production']);

        $this->record($person->id, 'production');
        $this->record($other->id, 'production');
        $this->record($person->id, 'development');

        $response = $this->actingAs($user)->getJson(route('osinergmin-retransmission', [
            'id' => 'unit-1', 'draw' => 1, 'start' => 0, 'length' => 25,
        ]));

        $response->assertOk()->assertJsonPath('recordsTotal', 1);
    }

    public function test_owner_sees_all_clients_but_only_the_active_environment(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        $first = Person::create(['full_name' => 'Primero', 'status' => true]);
        $second = Person::create(['full_name' => 'Segundo', 'status' => true]);
        SystemSetting::create(['key' => 'osinergmin_environment', 'value' => 'production']);

        $this->record($first->id, 'production');
        $this->record($second->id, 'production');
        $this->record($first->id, 'development');

        $response = $this->actingAs($owner)->getJson(route('osinergmin-retransmission', [
            'id' => 'unit-1', 'draw' => 1, 'start' => 0, 'length' => 25,
        ]));

        $response->assertOk()->assertJsonPath('recordsTotal', 2);
    }

    private function record(int $personId, string $environment): void
    {
        Osinergmin::create([
            'person_id' => $personId,
            'environment' => $environment,
            'uuid' => 'unit-1',
            'plate' => 'ABC-123',
            'gpsDate' => now(),
            'response_status' => 'UNKNOWN',
        ]);
    }
}
