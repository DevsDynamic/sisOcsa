<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;
class DeleteApiTokenTest extends TestCase {
    use RefreshDatabase;
    public function test_personal_api_token_deletion_endpoint_is_not_exposed(): void {
        $this->assertFalse(Features::hasApiFeatures());
        $this->delete('/user/api-tokens/1')->assertNotFound();
    }
}
