<?php

namespace Tests\Feature;

use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SendOsinergminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_reads_laravel_json_response_as_an_array(): void
    {
        $controller = Mockery::mock(TaskController::class);
        $controller->shouldReceive('sendDataOsinergmin')->once()->andReturn(
            response()->json(['resu' => [[
                'status' => 'SUCCESS',
                'error_message' => '',
            ]]])
        );
        $this->app->instance(TaskController::class, $controller);

        $this->artisan('osinergmin:send')
            ->expectsOutputToContain('1 tramas exitosas')
            ->assertSuccessful();
    }

    public function test_command_fails_for_non_conclusive_results(): void
    {
        $controller = Mockery::mock(TaskController::class);
        $controller->shouldReceive('sendDataOsinergmin')->once()->andReturn(
            response()->json(['resu' => [[
                'status' => 'UNKNOWN',
                'error_message' => 'PMGO no confirmo la trama.',
            ]]])
        );
        $this->app->instance(TaskController::class, $controller);

        $this->artisan('osinergmin:send')
            ->expectsOutputToContain('PMGO no confirmo la trama.')
            ->assertFailed();
    }
}
