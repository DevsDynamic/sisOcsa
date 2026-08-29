<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;
class RegistrationTest extends TestCase {
    use RefreshDatabase;
    public function test_public_registration_is_explicitly_disabled(): void {
        $this->assertFalse(Features::enabled(Features::registration()));
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }
}
