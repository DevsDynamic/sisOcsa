<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\Person;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled_and_password_recovery_is_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->get(route('password.request'))->assertOk();
    }

    public function test_remember_me_creates_a_persistent_login_cookie(): void
    {
        $user = User::factory()->create(['username' => 'remember@example.com']);
        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertRedirect('/dashboard');
        $response->assertCookie(Auth::guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
    }

    public function test_only_owner_can_manage_encrypted_settings(): void
    {
        $customer = User::factory()->create(['is_system_owner' => false]);
        $owner = User::factory()->create(['is_system_owner' => true]);

        $this->actingAs($customer)->get(route('system-settings.edit'))->assertForbidden();
        $this->actingAs($owner)->get(route('system-settings.edit'))->assertOk();

        $this->actingAs($owner)->put(route('system-settings.update'), [
            'osinergmin_environment' => 'development',
            'ocsa_base_url' => 'https://gps.example.com',
            'osinergmin_base_url_development' => 'https://demo.example.com',
            'osinergmin_base_url_production' => 'https://production.example.com',
            'osinergmin_token_development' => 'secret-demo-token',
            'osinergmin_token_production' => 'secret-production-token',
        ])->assertSessionHasNoErrors();

        $raw = DB::table('system_settings')->where('key', 'osinergmin_token_development')->value('value');
        $this->assertNotSame('secret-demo-token', $raw);
        $this->assertSame('secret-demo-token', SystemSetting::valueFor('osinergmin_token_development'));
    }

    public function test_owner_can_store_encrypted_smtp_settings_without_exposing_password(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);

        $this->actingAs($owner)->put(route('system-settings.update'), [
            'osinergmin_environment' => 'development',
            'ocsa_base_url' => 'https://gps.example.com',
            'osinergmin_base_url_development' => 'https://demo.example.com',
            'osinergmin_base_url_production' => 'https://production.example.com',
            'mail_host' => 'smtp.example.com',
            'mail_port' => 587,
            'mail_username' => 'alerts@example.com',
            'mail_password' => 'smtp-secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'alerts@example.com',
            'mail_from_name' => 'OCSA GPS',
            'mail_alert_recipients' => 'owner@example.com, soporte@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertNotSame('smtp-secret', DB::table('system_settings')->where('key', 'mail_password')->value('value'));
        $this->assertSame('smtp-secret', SystemSetting::valueFor('mail_password'));
        $this->actingAs($owner)->get(route('system-settings.edit'))
            ->assertOk()
            ->assertDontSee('smtp-secret')
            ->assertSee('Contraseña guardada: sí');
    }

    public function test_successful_mail_test_also_persists_the_smtp_password(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['is_system_owner' => true]);

        $this->actingAs($owner)->post(route('system-settings.test-mail'), [
            'mail_host' => 'smtp.example.com',
            'mail_port' => 465,
            'mail_username' => 'alerts@example.com',
            'mail_password' => 'tested-secret',
            'mail_encryption' => 'ssl',
            'mail_from_address' => 'alerts@example.com',
            'mail_from_name' => 'OCSA GPS',
            'mail_alert_recipients' => 'owner@example.com',
            'mail_test_recipient' => 'test@example.com',
        ])->assertRedirect(route('system-settings.edit'))
            ->assertSessionHas('mail_status');

        $this->assertSame('tested-secret', SystemSetting::valueFor('mail_password'));
        $this->actingAs($owner)->get(route('system-settings.edit'))
            ->assertSee('Contraseña guardada: sí')
            ->assertDontSee('tested-secret');
    }

    public function test_user_can_change_own_password(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('profile.account.password'), [
            'current_password' => 'password',
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NuevaClave123', $user->fresh()->password));
        $this->actingAs($user)->get(route('profile.account'))->assertOk();
        $this->actingAs($user)->get(route('dashboard.index'))->assertOk();
    }

    public function test_password_reset_link_uses_username_as_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['username' => 'recovery@example.com']);

        $this->post(route('password.email'), ['username' => $user->username])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_people_datatable_uses_valid_columns(): void
    {
        $user = User::factory()->create(['is_system_owner' => true]);
        $this->actingAs($user)->getJson(route('people.index-table', [
            'draw' => 1, 'start' => 0, 'length' => 10,
        ]))->assertOk()->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_clients_without_optional_catalogs_are_visible_and_dashboard_counts_match(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        Person::create(['full_name' => 'Cliente activo sin documento', 'status' => true]);
        Person::create(['full_name' => 'Cliente inactivo sin documento', 'status' => false]);

        $this->actingAs($owner)->getJson(route('people.index-table', [
            'draw' => 1, 'start' => 0, 'length' => 10,
        ]))->assertOk()
            ->assertJsonPath('recordsTotal', 2)
            ->assertJsonCount(2, 'data');

        $this->actingAs($owner)->get(route('dashboard.index'))
            ->assertOk()
            ->assertSee('Clientes registrados')
            ->assertSee('1 activos · 1 inactivos');
    }

    public function test_edit_customer_loads_its_type_and_update_does_not_depend_on_hidden_type(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        $documentType = DB::table('type_documents')->insertGetId(['name' => 'RUC', 'max_length' => 11]);
        $personType = DB::table('type_people')->insertGetId(['code' => 'CO', 'name' => 'Contacto', 'status' => true]);
        $customer = Person::create([
            'type_document_id' => $documentType,
            'type_person_id' => $personType,
            'document_number' => '20123456789',
            'full_name' => 'Cliente editable',
            'email' => 'cliente@example.com',
        ]);

        $this->actingAs($owner)->getJson(route('people.edit', $customer))
            ->assertOk()
            ->assertJsonPath('html.type_person_id', $personType)
            ->assertJsonPath('html.type_person_code', 'CO');

        $this->actingAs($owner)->putJson(route('people.update', $customer), [
            'type_document' => $documentType,
            'document_number' => '20123456789',
            'full_name' => 'Cliente actualizado',
            'type_person' => $personType,
            'email' => 'cliente@example.com',
            'phone_number' => '987654321',
        ])->assertOk()->assertJsonPath('success', 'Cliente <strong>Cliente actualizado</strong> actualizado exitosamente.');

        $this->assertDatabaseHas('people', [
            'id' => $customer->id,
            'type_person_id' => $personType,
            'full_name' => 'Cliente actualizado',
        ]);
    }

    public function test_profile_photo_can_be_uploaded(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('profile.account.photo'), [
            'photo' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($user->fresh()->profile_photo_path);
        $this->actingAs($user->fresh())->get(route('profile.account.photo.show'))->assertOk()->assertHeader('content-type', 'image/jpeg');
    }

    public function test_units_endpoint_returns_a_valid_datatable_without_a_token(): void
    {
        $owner = User::factory()->create(['is_system_owner' => true]);
        $this->actingAs($owner)->getJson(route('osinergmins.index-units-data', [
            'draw' => 1, 'start' => 0, 'length' => 10,
        ]))->assertOk()->assertJsonStructure(['draw', 'recordsTotal', 'data', 'notice']);
    }
}
