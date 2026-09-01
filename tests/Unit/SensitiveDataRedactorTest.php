<?php

namespace Tests\Unit;

use App\Services\SensitiveDataRedactor;
use PHPUnit\Framework\TestCase;

class SensitiveDataRedactorTest extends TestCase
{
    public function test_it_redacts_credentials_from_urls_and_nested_context(): void
    {
        $url = 'https://ocsa.test/units?key=secret-value&include=ignition';

        $this->assertSame(
            'https://ocsa.test/units?key=[PROTEGIDO]&include=ignition',
            SensitiveDataRedactor::text($url)
        );
        $this->assertSame([
            'endpoint' => 'https://ocsa.test/units?key=[PROTEGIDO]&include=ignition',
            'token' => '[PROTEGIDO]',
        ], SensitiveDataRedactor::context(['endpoint' => $url, 'token' => 'secret-value']));
    }
}
