<?php

namespace Tests\Feature;

use App\Events\ApRecordSaved;
use App\Models\ApRecord;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
        Event::fake([ApRecordSaved::class]);
    }

    public function test_team_logs_in_once_and_reaches_create_form(): void
    {
        $team = Team::where('login_slug', 'team-1')->firstOrFail();

        $this->get('/t/team-1')->assertOk()->assertSee('Tổ 1');
        $this->post('/t/team-1/login', ['access_code' => 'wrong'])->assertSessionHasErrors('access_code');
        $this->post('/t/team-1/login', ['access_code' => 'vnpt1'])
            ->assertRedirect(route('team.records.create', $team))
            ->assertSessionHas('team_id', $team->id);

        $this->withSession(['team_id' => $team->id])->get('/t/team-1')->assertRedirect(route('team.records.create', $team));
    }

    public function test_team_creates_installed_ap_without_required_photos_and_custom_time(): void
    {
        $team = Team::where('login_slug', 'team-1')->firstOrFail();
        $payload = [
            'floor' => 'T11', 'ap_no' => 2, 'status' => 'installed',
            'record_time' => '2026-06-20T09:45',
        ];

        $this->withSession(['team_id' => $team->id])->post('/t/team-1/records', $payload)
            ->assertRedirect(route('team.today', $team))
            ->assertSessionHas('success', 'Đã lưu T11-AP2');

        $record = ApRecord::firstOrFail();
        $this->assertSame('T11-AP2', $record->ap_name);
        $this->assertSame($team->id, $record->team_id);
        $this->assertSame('2026-06-20 09:45', $record->created_at->format('Y-m-d H:i'));
        $this->assertNull($record->location_photo);
        $this->assertNull($record->mac_photo);
        $this->assertNull($record->cable_photo);
        Event::assertDispatched(ApRecordSaved::class);
    }

    public function test_blocked_ap_requires_reason_but_not_issue_photo(): void
    {
        $team = Team::where('login_slug', 'team-2')->firstOrFail();

        $this->withSession(['team_id' => $team->id])->post('/t/team-2/records', [
            'floor' => 'G', 'ap_no' => 5, 'status' => 'blocked',
        ])->assertSessionHasErrors(['issue_reason']);

        $this->withSession(['team_id' => $team->id])->post('/t/team-2/records', [
            'floor' => 'G', 'ap_no' => 5, 'status' => 'blocked',
            'issue_reason' => 'Chưa tìm thấy dây',
        ])->assertRedirect(route('team.today', $team));

        $this->assertDatabaseHas('ap_records', ['ap_name' => 'G-AP5', 'status' => 'blocked', 'issue_photo' => null]);
    }

    public function test_team_cannot_edit_another_teams_record(): void
    {
        $team1 = Team::where('login_slug', 'team-1')->firstOrFail();
        $team2 = Team::where('login_slug', 'team-2')->firstOrFail();
        $record = ApRecord::create(['team_id' => $team2->id, 'floor' => 'T1', 'ap_no' => 1, 'status' => 'blocked', 'issue_reason' => 'Khác']);

        $this->withSession(['team_id' => $team1->id])->get(route('team.records.edit', [$team1, $record]))->assertNotFound();
    }

    public function test_admin_login_dashboard_and_csv_export_are_protected(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/admin');
        $this->post('/admin/login', ['access_code' => 'vnptadmin'])
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('is_admin', true);

        $this->withSession(['is_admin' => true])->get('/admin/dashboard')->assertOk()->assertSee('Tiến độ thi công');
        $this->withSession(['is_admin' => true])->get('/admin/export-csv')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
