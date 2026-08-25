<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OsinergminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_paginates_a_large_result_set_on_the_server(): void
    {
        $user = User::factory()->create();
        $now = now();

        foreach (range(1, 20) as $chunk) {
            $rows = [];
            foreach (range(1, 1000) as $offset) {
                $number = (($chunk - 1) * 1000) + $offset;
                $rows[] = [
                    'uuid' => 'unit-' . ($number % 50),
                    'plate' => 'TEST-' . ($number % 50),
                    'response_status' => 'SUCCESS',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('osinergmins')->insert($rows);
        }

        $response = $this->actingAs($user)->getJson(route('reports.view-osinergmin', [
            'from' => $now->toDateString(),
            'to' => $now->toDateString(),
            'draw' => 1,
            'start' => 0,
            'length' => 25,
            'order' => [['column' => 0, 'dir' => 'desc']],
            'columns' => [['data' => 'id', 'name' => 'id', 'searchable' => 'true', 'orderable' => 'true']],
        ]));

        $response->assertOk()
            ->assertJsonPath('recordsTotal', 20000)
            ->assertJsonCount(25, 'data');

        $this->actingAs($user)->getJson(route('reports.view-osinergmin', [
            'from' => $now->toDateString(), 'to' => $now->toDateString(),
            'status' => 'ERROR', 'draw' => 1, 'start' => 0, 'length' => 25,
        ]))->assertOk()->assertJsonPath('recordsFiltered', 0);
    }

    public function test_report_rejects_ranges_larger_than_retention_window(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('reports.view-osinergmin', [
            'from' => now()->subDays(40)->toDateString(),
            'to' => now()->toDateString(),
        ]))->assertUnprocessable()->assertJsonValidationErrors('to');
    }
}
