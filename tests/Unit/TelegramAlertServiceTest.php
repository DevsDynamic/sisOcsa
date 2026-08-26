<?php

namespace Tests\Unit;

use App\Services\TelegramAlertService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramAlertServiceTest extends TestCase
{
    public function test_it_sends_a_message_to_every_configured_chat(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

        TelegramAlertService::send('Prueba', '123:secret', ['111', '-222']);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request['text'] === 'Prueba' && in_array($request['chat_id'], ['111', '-222'], true));
    }

    public function test_chat_ids_are_normalized_and_deduplicated(): void
    {
        $this->assertSame(['111', '-222'], TelegramAlertService::chatIds("111, -222\n111"));
    }
}
