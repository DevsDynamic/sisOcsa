<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;
class EmailVerificationTest extends TestCase {
    use RefreshDatabase;
    public function test_unused_email_verification_surface_is_not_exposed(): void {
        $this->assertFalse(Features::enabled(Features::emailVerification()));
        $this->get('/email/verify')->assertNotFound();
    }
}
