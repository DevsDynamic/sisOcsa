<?php

namespace Tests\Unit;

use App\Services\UnitOperationalStatus;
use Carbon\Carbon;
use Tests\TestCase;

class UnitOperationalStatusTest extends TestCase
{
    public function test_it_only_reports_healthy_when_gps_and_transmission_are_recent_and_accepted(): void
    {
        Carbon::setTestNow('2026-09-01 18:00:00');

        $status = app(UnitOperationalStatus::class)->evaluate(
            '2026-09-01 17:58:00',
            Carbon::parse('2026-09-01 17:59:00'),
            'SUCCESS'
        );

        $this->assertSame('success', $status['tone']);
        $this->assertSame('Transmitiendo correctamente', $status['label']);
        Carbon::setTestNow();
    }

    public function test_it_warns_when_retransmission_is_current_but_ocsa_data_is_stale(): void
    {
        Carbon::setTestNow('2026-09-01 18:00:00');

        $status = app(UnitOperationalStatus::class)->evaluate(
            '2026-03-29 04:23:38',
            Carbon::parse('2026-09-01 17:59:00'),
            'SUCCESS'
        );

        $this->assertSame('warning', $status['tone']);
        $this->assertSame('Dato GPS desactualizado', $status['label']);
        Carbon::setTestNow();
    }

    public function test_it_alerts_when_there_has_not_been_a_recent_transmission(): void
    {
        Carbon::setTestNow('2026-09-01 18:00:00');

        $status = app(UnitOperationalStatus::class)->evaluate(
            '2026-09-01 17:40:00',
            Carbon::parse('2026-09-01 17:40:00'),
            'SUCCESS'
        );

        $this->assertSame('danger', $status['tone']);
        $this->assertSame('Sin transmisión reciente', $status['label']);
        Carbon::setTestNow();
    }
}
