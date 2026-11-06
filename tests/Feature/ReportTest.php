<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Guest cannot access reports dashboard.
     */
    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }

    /**
     * Guru cannot access reports dashboard (forbidden).
     */
    public function test_guru_cannot_access_reports(): void
    {
        $guru = User::where('role_id', Role::GURU)->first();

        $response = $this->actingAs($guru)->get('/reports');

        $response->assertStatus(403);
    }

    /**
     * PIC cannot access reports dashboard (forbidden).
     */
    public function test_pic_cannot_access_reports(): void
    {
        $pic = User::where('role_id', Role::PIC)->first();

        $response = $this->actingAs($pic)->get('/reports');

        $response->assertStatus(403);
    }

    /**
     * Admin can view the daily reports dashboard.
     */
    public function test_admin_can_view_daily_report(): void
    {
        $admin = User::where('role_id', Role::ADMIN)->first();

        $response = $this->actingAs($admin)->get('/reports?period=daily');

        $response->assertStatus(200);
        $response->assertSee('Laporan &amp; Rekapitulasi Sarpras', false);
        $response->assertSee('Khusus Waka &amp; Admin', false);
        $response->assertSee('Harian');
    }

    /**
     * Waka can view weekly and monthly reports.
     */
    public function test_waka_can_view_weekly_and_monthly_report(): void
    {
        $waka = User::where('role_id', Role::WAKA)->first();

        $resWeekly = $this->actingAs($waka)->get('/reports?period=weekly');
        $resWeekly->assertStatus(200);
        $resWeekly->assertSee('Mingguan');

        $resMonthly = $this->actingAs($waka)->get('/reports?period=monthly');
        $resMonthly->assertStatus(200);
        $resMonthly->assertSee('Bulanan');
    }

    /**
     * Admin and Waka can export report to CSV.
     */
    public function test_admin_and_waka_can_export_csv(): void
    {
        $admin = User::where('role_id', Role::ADMIN)->first();

        $response = $this->actingAs($admin)->get('/reports/export?period=daily');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type') ?? '');
    }

    /**
     * Admin and Waka can view the printable official report.
     */
    public function test_waka_can_view_print_report(): void
    {
        $waka = User::where('role_id', Role::WAKA)->first();

        $response = $this->actingAs($waka)->get('/reports/print?period=daily');

        $response->assertStatus(200);
        $response->assertSee('LAPORAN REKAPITULASI SARANA DAN PRASARANA');
        $response->assertSee('Wakil Kepala Sekolah Bidang Sarpras');
    }

    /**
     * Reports can be filtered by room.
     */
    public function test_reports_can_filter_by_room(): void
    {
        $admin = User::where('role_id', Role::ADMIN)->first();
        $room = Room::first();

        $response = $this->actingAs($admin)->get('/reports?period=daily&room_id='.$room->id);

        $response->assertStatus(200);
        $response->assertSee($room->room_name);
    }

    /**
     * Monthly report with specific month and year parameters.
     */
    public function test_monthly_report_with_custom_month_and_year(): void
    {
        $admin = User::where('role_id', Role::ADMIN)->first();

        $response = $this->actingAs($admin)->get('/reports?period=monthly&month=10&year=2026');

        $response->assertStatus(200);
        $response->assertSee('Oktober 2026');
    }
}
