<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile.account'))
            ->assertOk()
            ->assertSee($user->username);
    }

    public function test_profile_access_email_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.account.update'), [
            'username' => 'nuevo@example.com',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('profile_status');

        $this->assertSame('nuevo@example.com', $user->fresh()->username);
    }
}
