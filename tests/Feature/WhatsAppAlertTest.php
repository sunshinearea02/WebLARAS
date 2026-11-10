<?php

namespace Tests\Feature;

use App\Models\AlertVerification;
use App\Models\SensorLog;
use App\Models\SensorNode;
use App\Models\User;
use App\Models\WargaContact;
use App\Services\WhatsAppAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Alur peringatan WhatsApp dengan verifikasi kader per desa:
 * status berubah -> WA ke kader -> disetujui / ditolak / otomatis ke warga
 * (BAHAYA 5 menit, WASPADA 10 menit) -> kabar kembali aman.
 */
class WhatsAppAlertTest extends TestCase
{
    use RefreshDatabase;

    private const KADER = '6281211112222';
    private const WARGA_KEMIRI = '6281333334444';
    private const WARGA_PACET = '6281355556666';

    private bool $gowaOk = true;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['services.gowa' => [
            'enabled' => true,
            'url' => 'http://gowa.test:3000', 'user' => 'laras', 'pass' => 'rahasia', 'device_id' => '',
            'targets' => '', 'kader_targets' => '',
            'auto_send_minutes' => 5, 'waspada_auto_send_minutes' => 10, 'fresh_minutes' => 30,
        ]]);
        Http::fake([
            'gowa.test:3000/*' => fn () => $this->gowaOk
                ? Http::response(['code' => 'SUCCESS', 'message' => 'Success', 'results' => ['message_id' => 'ABC']])
                : Http::response(['code' => 'INTERNAL_SERVER_ERROR', 'message' => 'not logged in'], 500),
            '*' => Http::response([], 500),
        ]);
    }

    private function wa(): WhatsAppAlertService
    {
        return app(WhatsAppAlertService::class);
    }

    private function kader(): User
    {
        return User::factory()->create(['name' => 'Kader Sri', 'phone' => '0812-1111-2222']);
    }

    private function reading(string $desa, string $code, string $status): SensorNode
    {
        $node = SensorNode::firstOrCreate(['node_code' => $code], ['node_name' => $code, 'status' => 'ACTIVE', 'desa' => $desa, 'latitude' => -7.674, 'longitude' => 112.516]);
        SensorLog::create(['node_id' => $node->id, 'timestamp' => now(), 'status' => $status, 'kelembapan_dangkal' => 80]);

        return $node;
    }

    /** @return string[] isi pesan yang terkirim ke nomor ini */
    private function messagesTo(string $phone): array
    {
        return Http::recorded(fn (Request $r) => str_contains($r->url(), '/send/message') && $r['phone'] === $phone)
            ->map(fn ($pair) => $pair[0]['message'])->values()->all();
    }

    private function setUpKemiriBahaya(): User
    {
        $kader = $this->kader();
        WargaContact::create(['desa' => 'Kemiri', 'name' => 'Warga Kemiri', 'phone' => '0813 3333 4444', 'active' => true]);
        $this->reading('Kemiri', 'NODE-01', 'BAHAYA');

        return $kader;
    }

    public function test_parses_targets(): void
    {
        $this->assertSame(
            ['6281234567890', '6281300000000', '120363000000000000@g.us'],
            WhatsAppAlertService::parseTargets('0812-3456-7890; +62813 0000 0000, 120363000000000000@g.us')
        );
    }

    public function test_send_uses_gowa_endpoint_with_basic_auth(): void
    {
        $this->assertTrue($this->wa()->sendTo(self::WARGA_KEMIRI, 'Uji LARAS')['ok']);

        Http::assertSent(fn (Request $r) => $r->url() === 'http://gowa.test:3000/send/message'
            && $r['phone'] === self::WARGA_KEMIRI
            && $r['message'] === 'Uji LARAS'
            && $r->hasHeader('Authorization', 'Basic ' . base64_encode('laras:rahasia')));
    }

    public function test_status_change_asks_kader_before_warga(): void
    {
        $this->setUpKemiriBahaya();

        $this->wa()->evaluate();

        $pending = $this->wa()->pending('Kemiri');
        $this->assertNotNull($pending);
        $this->assertSame('BAHAYA', $pending->level);
        $this->assertCount(1, $this->messagesTo(self::KADER));
        $this->assertSame([], $this->messagesTo(self::WARGA_KEMIRI));
    }

    public function test_kader_approval_sends_warning_to_warga(): void
    {
        $kader = $this->setUpKemiriBahaya();
        $this->wa()->evaluate();

        $result = $this->wa()->approve($this->wa()->pending('Kemiri'), $kader);

        $this->assertTrue($result['ok']);
        $this->assertSame(AlertVerification::APPROVED, AlertVerification::sole()->state);
        $warga = $this->messagesTo(self::WARGA_KEMIRI);
        $this->assertCount(1, $warga);
        $this->assertStringContainsString('Desa Kemiri', $warga[0]);
        $this->assertStringContainsString('Kader Sri', $warga[0]);
    }

    public function test_kader_rejection_keeps_warga_silent_and_mutes_same_level(): void
    {
        $kader = $this->setUpKemiriBahaya();
        $this->wa()->evaluate();

        $this->assertTrue($this->wa()->reject($this->wa()->pending('Kemiri'), $kader, 'Sensor tersenggol')['ok']);
        $this->wa()->evaluate();

        $this->assertSame(AlertVerification::REJECTED, AlertVerification::sole()->state);
        $this->assertSame([], $this->messagesTo(self::WARGA_KEMIRI));
    }

    public function test_auto_send_after_5_minutes_for_bahaya_and_10_minutes_for_waspada(): void
    {
        $this->kader();
        WargaContact::create(['desa' => 'Kemiri', 'name' => 'Warga Kemiri', 'phone' => '0813 3333 4444', 'active' => true]);
        WargaContact::create(['desa' => 'Pacet', 'name' => 'Warga Pacet', 'phone' => '0813 5555 6666', 'active' => true]);
        $this->reading('Kemiri', 'NODE-01', 'BAHAYA');
        $this->reading('Pacet', 'NODE-02', 'WASPADA');
        $this->wa()->evaluate();

        $this->travel(6)->minutes();
        $this->wa()->tick();
        $this->assertSame(AlertVerification::AUTO_SENT, AlertVerification::where('desa', 'Kemiri')->sole()->state);
        $this->assertSame(AlertVerification::PENDING, AlertVerification::where('desa', 'Pacet')->sole()->state);
        $this->assertStringContainsString('dalam 5 menit', $this->messagesTo(self::WARGA_KEMIRI)[0]);
        $this->assertSame([], $this->messagesTo(self::WARGA_PACET));

        $this->travel(5)->minutes();
        $this->wa()->tick();
        $this->assertSame(AlertVerification::AUTO_SENT, AlertVerification::where('desa', 'Pacet')->sole()->state);
        $this->assertStringContainsString('dalam 10 menit', $this->messagesTo(self::WARGA_PACET)[0]);
    }

    public function test_gowa_down_keeps_verification_pending(): void
    {
        $kader = $this->setUpKemiriBahaya();
        $this->wa()->evaluate();
        $this->gowaOk = false;

        $result = $this->wa()->approve($this->wa()->pending('Kemiri'), $kader);

        $this->assertFalse($result['ok']);
        $this->assertSame(AlertVerification::PENDING, AlertVerification::sole()->state);
    }

    public function test_back_to_aman_sends_all_clear_once(): void
    {
        $kader = $this->setUpKemiriBahaya();
        $this->wa()->evaluate();
        $this->wa()->approve($this->wa()->pending('Kemiri'), $kader);

        $this->travel(1)->minutes();
        $this->reading('Kemiri', 'NODE-01', 'AMAN');
        $this->wa()->evaluate();
        $this->wa()->evaluate();

        $this->assertCount(2, $this->messagesTo(self::WARGA_KEMIRI));  // peringatan + kabar aman, sekali saja
    }
}
