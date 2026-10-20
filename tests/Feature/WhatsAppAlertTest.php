<?php

namespace Tests\Feature;

use App\Services\WhatsAppAlertService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppAlertTest extends TestCase
{
    private bool $gowaOk = true;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['services.gowa' => [
            'url' => 'http://gowa.test:3000', 'user' => 'laras', 'pass' => 'rahasia',
            'device_id' => '', 'targets' => '0812-3456-7890, 120363000000000000@g.us',
        ]]);
        Http::fake([
            'gowa.test:3000/*'  => fn () => $this->gowaOk
                ? Http::response(['code' => 'SUCCESS', 'message' => 'Success', 'results' => ['message_id' => 'ABC']])
                : Http::response(['code' => 'INTERNAL_SERVER_ERROR', 'message' => 'not logged in'], 500),
            'api.bmkg.go.id/*'  => Http::response([], 500),
        ]);
    }

    public function test_parses_targets(): void
    {
        $this->assertSame(
            ['6281234567890', '6281300000000', '120363000000000000@g.us'],
            WhatsAppAlertService::parseTargets('0812-3456-7890; +62813 0000 0000, 120363000000000000@g.us')
        );
    }

    public function test_bahaya_is_sent_to_all_targets_with_basic_auth(): void
    {
        $sent = app(WhatsAppAlertService::class)->notifyStatus('BAHAYA', ['moisture_percent' => 80, 'rainfall_mm' => 30]);
        $this->assertTrue($sent);

        $sends = Http::recorded(fn (Request $r) => str_contains($r->url(), '/send/message'))->values();
        $this->assertCount(2, $sends);                                   // 2 tujuan
        $this->assertStringContainsString('STATUS BAHAYA', $sends[0][0]['message']);
        $this->assertStringContainsString('112', $sends[0][0]['message']);
    }

    public function test_request_shape(): void
    {
        app(WhatsAppAlertService::class)->notifyStatus('WASPADA', ['moisture_percent' => 60]);

        Http::assertSent(fn (Request $r) => $r->url() === 'http://gowa.test:3000/send/message'
            && $r['phone'] === '6281234567890'
            && str_contains($r['message'], 'STATUS WASPADA')
            && str_contains($r['message'], 'basah (60%)')
            && $r->hasHeader('Authorization', 'Basic ' . base64_encode('laras:rahasia')));
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/send/message') && $r['phone'] === '120363000000000000@g.us');
    }

    public function test_same_status_is_not_repeated_during_cooldown_and_aman_only_after_alert(): void
    {
        $wa = app(WhatsAppAlertService::class);

        $this->assertNull($wa->notifyStatus('AMAN', []));        // belum pernah ada peringatan
        $this->assertTrue($wa->notifyStatus('WASPADA', []));
        $this->assertNull($wa->notifyStatus('WASPADA', []));     // masih cooldown
        $this->assertTrue($wa->notifyStatus('BAHAYA', []));      // naik status -> langsung kirim
        $this->assertTrue($wa->notifyStatus('AMAN', []));        // kabar kembali aman
        $this->assertNull($wa->notifyStatus('AMAN', []));        // hanya sekali
    }

    public function test_returns_false_when_gowa_unreachable(): void
    {
        $this->gowaOk = false;
        $this->assertFalse(app(WhatsAppAlertService::class)->notifyStatus('BAHAYA', []));
        $this->assertFalse(Cache::has('wa_cooldown_BAHAYA'));    // gagal -> boleh dicoba lagi
    }
}