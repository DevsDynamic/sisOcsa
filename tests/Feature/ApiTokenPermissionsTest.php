<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Features;
use Tests\TestCase;
class ApiTokenPermissionsTest extends TestCase {
    use RefreshDatabase;
    public function test_personal_api_token_permissions_endpoint_is_not_exposed(): void {
        $this->assertFalse(Features::hasApiFeatures());
        $this->put('/user/api-tokens/1', ['permissions' => ['*']])->assertNotFound();
    }
}
