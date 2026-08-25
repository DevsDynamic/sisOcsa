<?php

namespace Tests\Unit;

use App\Http\Controllers\TaskController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class OsinergminResponseTest extends TestCase
{
    public function test_batch_items_inherit_success_status_from_the_envelope(): void
    {
        $this->assertSame('SUCCESS', $this->resolveStatus(
            ['plate' => 'ABC-123'],
            ['status' => 'CREATED', 'message' => 'Tramas created successfully'],
            200
        ));
    }

    public function test_explicit_provider_error_is_rejected(): void
    {
        $this->assertSame('ERROR', $this->resolveStatus([], ['status' => 'REJECTED', 'message' => 'Token inválido'], 200));
    }

    public function test_an_ambiguous_http_200_is_not_reported_as_rejected(): void
    {
        $this->assertSame('UNKNOWN', $this->resolveStatus([], [], 200));
    }

    private function resolveStatus(array $item, array $response, int $httpStatus): string
    {
        $method = new ReflectionMethod(TaskController::class, 'providerStatus');

        return $method->invoke(new TaskController, $item, $response, $httpStatus);
    }
}
