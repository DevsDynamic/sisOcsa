<?php

namespace Tests\Feature;

use App\Models\IntegrationLog;
use App\Models\Osinergmin;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class IntegrationMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_monitor_and_purge_only_demo_data(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        $typeDocument = DB::table('type_documents')->insertGetId(['name' => 'RUC', 'max_length' => 11]);
        $typePerson = DB::table('type_people')->insertGetId(['code' => 'CO', 'name' => 'Contacto']);
        $demo = Person::create(['type_document_id' => $typeDocument, 'type_person_id' => $typePerson, 'document_number' => '20000000001', 'full_name' => 'Demo', 'is_demo' => true]);
        IntegrationLog::create(['person_id' => $demo->id, 'environment' => 'development', 'stage' => 'RUN', 'status' => 'ERROR', 'message' => 'Demo error']);
        Osinergmin::create(['environment' => 'development', 'plate' => 'DEMO']);
        Osinergmin::create(['environment' => 'production', 'plate' => 'PROD']);

        $this->actingAs($owner)->get(route('integration-monitor.index'))->assertOk()->assertSee('Demo error');
        $this->actingAs($owner)->delete(route('integration-monitor.purge-demo'), ['confirmation' => 'ELIMINAR DEMO'])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('people', ['id' => $demo->id]);
        $this->assertDatabaseMissing('osinergmins', ['plate' => 'DEMO']);
        $this->assertDatabaseHas('osinergmins', ['plate' => 'PROD']);
    }

    public function test_public_status_requires_a_valid_signature(): void
    {
        $this->get('/estado-integracion')->assertForbidden();
        $this->get(URL::temporarySignedRoute('integration-status.public', now()->addMinute()))->assertOk();
    }

    public function test_monitor_access_and_sensitive_actions_are_independent_permissions(): void
    {
        $administrator = User::factory()->create();
        $administrator->givePermissionTo(Permission::firstOrCreate([
            'name' => 'integration.monitor.view', 'guard_name' => 'web',
        ]));

        $this->actingAs($administrator)->get(route('integration-monitor.index'))
            ->assertOk()->assertDontSee('Ejecutar ahora')->assertDontSee('Limpiar datos demo');
        $this->actingAs($administrator)->post(route('integration-monitor.send-now'), ['environment' => 'production'])
            ->assertForbidden();
    }
}
