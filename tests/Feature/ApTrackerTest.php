<?php

namespace Tests\Feature;

use App\Events\ApRecordSaved;
use App\Models\ApRecord;
use App\Models\FloorConfig;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $this->withSession(['team_id' => $team->id])->get(route('team.records.create', $team))
            ->assertOk()
            ->assertSee('Theo tầng')
            ->assertSee('Thoát');
        $this->withSession(['team_id' => $team->id])->post(route('team.logout', $team))
            ->assertRedirect(route('team.home', $team))
            ->assertSessionMissing('team_id');
    }

    public function test_team_creates_installed_ap_without_required_photos_and_custom_time(): void
    {
        $team = Team::where('login_slug', 'team-1')->firstOrFail();
        $payload = [
            'floor' => 'T11', 'ap_no' => 2, 'status' => 'installed',
            'work_date' => '2026-06-19',
            'record_time' => '2026-06-20T09:45',
        ];

        $this->withSession(['team_id' => $team->id])->post('/t/team-1/records', $payload)
            ->assertRedirect(route('team.today', $team))
            ->assertSessionHas('success', 'Đã lưu T11-AP2');

        $record = ApRecord::firstOrFail();
        $this->assertSame('T11-AP2', $record->ap_name);
        $this->assertSame($team->id, $record->team_id);
        $this->assertSame('2026-06-19', $record->work_date->format('Y-m-d'));
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
            'work_date' => '2026-06-20',
        ])->assertSessionHasErrors(['issue_reason']);

        $this->withSession(['team_id' => $team->id])->post('/t/team-2/records', [
            'floor' => 'G', 'ap_no' => 5, 'status' => 'blocked',
            'work_date' => '2026-06-20',
            'issue_reason' => 'Chưa tìm thấy dây',
        ])->assertRedirect(route('team.today', $team));

        $this->assertDatabaseHas('ap_records', ['ap_name' => 'G-AP5', 'status' => 'blocked', 'issue_photo' => null]);
    }

    public function test_uploaded_photo_keeps_real_file_extension(): void
    {
        $team = Team::where('login_slug', 'team-1')->firstOrFail();

        $this->withSession(['team_id' => $team->id])->post('/t/team-1/records', [
            'floor' => 'T3',
            'ap_no' => 9,
            'status' => 'installed',
            'work_date' => '2026-06-20',
            'location_photo' => UploadedFile::fake()->image('location.png'),
        ])->assertRedirect(route('team.today', $team));

        $record = ApRecord::where('ap_name', 'T3-AP9')->firstOrFail();

        $this->assertSame('ap-records/T3-AP9-location.png', $record->location_photo);
        Storage::disk('public')->assertExists($record->location_photo);
    }

    public function test_work_date_is_required(): void
    {
        $team = Team::where('login_slug', 'team-1')->firstOrFail();

        $this->withSession(['team_id' => $team->id])->post('/t/team-1/records', [
            'floor' => 'T8', 'ap_no' => 2, 'status' => 'installed',
        ])->assertSessionHasErrors(['work_date']);
    }

    public function test_team_cannot_edit_another_teams_record(): void
    {
        $team1 = Team::where('login_slug', 'team-1')->firstOrFail();
        $team2 = Team::where('login_slug', 'team-2')->firstOrFail();
        $record = ApRecord::create(['team_id' => $team2->id, 'floor' => 'T1', 'ap_no' => 1, 'status' => 'blocked', 'work_date' => '2026-06-20', 'issue_reason' => 'Khác']);

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
        $this->withSession(['is_admin' => true])->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionMissing('is_admin');
    }

    public function test_admin_can_create_more_teams(): void
    {
        $this->withSession(['is_admin' => true])->post('/admin/teams', [
            'name' => 'Tổ 4',
            'login_slug' => '',
            'access_code' => 'vnpt4',
        ])->assertRedirect(route('admin.teams.index'));

        $team = Team::where('access_code', 'vnpt4')->firstOrFail();

        $this->assertSame('Tổ 4', $team->name);
        $this->assertSame('to-4', $team->login_slug);
        $this->withSession(['is_admin' => true])->get('/admin/teams')->assertOk()->assertSee('vnpt4');
    }

    public function test_admin_and_team_can_view_floor_summary(): void
    {
        $team1 = Team::where('login_slug', 'team-1')->firstOrFail();
        $team2 = Team::where('login_slug', 'team-2')->firstOrFail();

        ApRecord::create(['team_id' => $team1->id, 'floor' => 'G', 'ap_no' => 1, 'status' => 'installed', 'work_date' => '2026-06-20']);
        ApRecord::create(['team_id' => $team2->id, 'floor' => 'T2', 'ap_no' => 3, 'status' => 'blocked', 'work_date' => '2026-06-20', 'issue_reason' => 'Khác']);

        $this->withSession(['is_admin' => true])->get('/admin/floors')
            ->assertOk()
            ->assertSee('G-AP1')
            ->assertSee('T2-AP3')
            ->assertSee('Xem');

        $this->withSession(['team_id' => $team1->id])->get(route('team.floors', $team1))
            ->assertOk()
            ->assertSee('Tầng mục tiêu')
            ->assertSee('G-AP1')
            ->assertSee('T2-AP3')
            ->assertSee('Sửa');
    }

    public function test_admin_can_configure_floor_targets_and_add_new_floor(): void
    {
        FloorConfig::query()->updateOrCreate(['floor' => 'T2'], ['target_ap_count' => 10, 'sort_order' => FloorConfig::sortValue('T2')]);

        $this->withSession(['is_admin' => true])->put(route('admin.floor-config.update'), [
            'floors' => [
                ['floor' => 'T2', 'target_ap_count' => 12],
            ],
            'new_floor' => 'T25',
            'new_target_ap_count' => 4,
        ])->assertRedirect(route('admin.floor-config'));

        $this->assertDatabaseHas('floor_configs', ['floor' => 'T2', 'target_ap_count' => 12]);
        $this->assertDatabaseHas('floor_configs', ['floor' => 'T25', 'target_ap_count' => 4]);

        $this->withSession(['is_admin' => true])->get(route('admin.floors'))
            ->assertOk()
            ->assertSee('T25')
            ->assertSee('0/4 AP');
    }

    public function test_new_configured_floor_can_be_used_in_ap_form(): void
    {
        $team = Team::where('login_slug', 'team-1')->firstOrFail();
        FloorConfig::query()->create(['floor' => 'T25', 'target_ap_count' => 2, 'sort_order' => FloorConfig::sortValue('T25')]);

        $this->withSession(['team_id' => $team->id])->get(route('team.records.create', $team))
            ->assertOk()
            ->assertSee('T25');

        $this->withSession(['team_id' => $team->id])->post(route('team.records.store', $team), [
            'floor' => 'T25',
            'ap_no' => 1,
            'status' => 'installed',
            'work_date' => '2026-06-30',
        ])->assertRedirect(route('team.today', $team));

        $this->assertDatabaseHas('ap_records', ['ap_name' => 'T25-AP1']);
    }
}
