<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;
class CreateApiTokenTest extends TestCase {
    use RefreshDatabase;
    public function test_personal_api_token_console_is_not_exposed(): void {
        $this->assertFalse(Features::hasApiFeatures());
        $this->get('/user/api-tokens')->assertNotFound();
    }
}
