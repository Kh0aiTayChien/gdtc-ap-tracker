<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveApRecordRequest;
use App\Models\ApRecord;
use App\Models\FloorConfig;
use App\Models\Team;
use App\Services\ApRecordManager;
use App\Services\FloorProgressService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function loginForm(Request $request): View|RedirectResponse
    {
        return $request->session()->get('is_admin') === true
            ? redirect()->route('admin.dashboard')
            : view('auth.admin');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate(['access_code' => ['required', 'string']]);

        if (! hash_equals((string) config('app.admin_code', 'vnptadmin'), (string) $request->input('access_code'))) {
            return back()->withErrors(['access_code' => 'Mã quản trị không đúng.']);
        }

        $request->session()->regenerate();
        $request->session()->put('is_admin', true);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('is_admin');
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function dashboard(FloorProgressService $progress): View
    {
        $summary = ApRecord::query()->selectRaw("COUNT(*) total, SUM(CASE WHEN status = 'installed' THEN 1 ELSE 0 END) installed, SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) blocked")
            ->first();
        $floorProgress = $progress->summary()['floors'];
        $configuredApTotal = $floorProgress->sum('total');
        $overallPercent = $configuredApTotal > 0 ? round(((int) ($summary->installed ?? 0) / $configuredApTotal) * 100, 1) : 0;
        $byWorkDate = ApRecord::query()
            ->whereNotNull('work_date')
            ->selectRaw("work_date, SUM(CASE WHEN status = 'installed' THEN 1 ELSE 0 END) installed, SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) blocked, COUNT(*) total")
            ->groupBy('work_date')->orderByDesc('work_date')->limit(14)->get();
        $weeklyProgress = ApRecord::query()
            ->whereNotNull('work_date')
            ->oldest('work_date')
            ->get()
            ->groupBy(fn (ApRecord $record) => $record->work_date->startOfWeek()->format('Y-m-d'))
            ->map(function ($items, string $weekStart) use ($configuredApTotal) {
                $installed = $items->where('status', 'installed')->count();

                return (object) [
                    'week_start' => $weekStart,
                    'week_end' => $items->first()->work_date->startOfWeek()->addDays(6)->format('Y-m-d'),
                    'installed' => $installed,
                    'blocked' => $items->where('status', 'blocked')->count(),
                    'total' => $items->count(),
                    'percent' => $configuredApTotal > 0 ? round(($installed / $configuredApTotal) * 100, 1) : 0,
                ];
            })
            ->reverse()
            ->take(8)
            ->values();
        $byTeam = Team::query()->withCount([
            'records as installed' => fn (Builder $q) => $q->where('status', 'installed'),
            'records as blocked' => fn (Builder $q) => $q->where('status', 'blocked'),
        ])->get();
        $latest = ApRecord::query()->with('team')->latest()->limit(15)->get();

        return view('admin.dashboard', compact('summary', 'floorProgress', 'configuredApTotal', 'overallPercent', 'byWorkDate', 'weeklyProgress', 'byTeam', 'latest'));
    }

    public function floors(FloorProgressService $progress): View
    {
        return view('shared.floors', [
            'title' => 'Tổng hợp theo tầng',
            'backUrl' => route('admin.dashboard'),
            'backLabel' => 'Tổng quan',
            ...$progress->summary(),
        ]);
    }

    public function floorConfig(FloorProgressService $progress): View
    {
        return view('admin.floor-config', [
            'floors' => $progress->summary()['floors'],
            'configs' => FloorConfig::query()->orderBy('sort_order')->orderBy('floor')->get(),
        ]);
    }

    public function updateFloorConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'floors' => ['array'],
            'floors.*.floor' => ['required', 'string', 'max:20'],
            'floors.*.target_ap_count' => ['required', 'integer', 'min:0', 'max:9999'],
            'new_floor' => ['nullable', 'string', 'max:20'],
            'new_target_ap_count' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        foreach ($validated['floors'] ?? [] as $row) {
            $floor = strtoupper(trim($row['floor']));
            FloorConfig::query()->updateOrCreate(
                ['floor' => $floor],
                [
                    'target_ap_count' => (int) $row['target_ap_count'],
                    'sort_order' => FloorConfig::sortValue($floor),
                ]
            );
        }

        if (filled($validated['new_floor'] ?? null)) {
            $floor = strtoupper(trim($validated['new_floor']));
            FloorConfig::query()->updateOrCreate(
                ['floor' => $floor],
                [
                    'target_ap_count' => (int) ($validated['new_target_ap_count'] ?? 0),
                    'sort_order' => FloorConfig::sortValue($floor),
                ]
            );
        }

        return redirect()->route('admin.floor-config')->with('success', 'Đã cập nhật cấu hình tầng.');
    }

    public function teams(): View
    {
        $teams = Team::query()
            ->withCount([
                'records as installed' => fn (Builder $q) => $q->where('status', 'installed'),
                'records as blocked' => fn (Builder $q) => $q->where('status', 'blocked'),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.teams.index', compact('teams'));
    }

    public function createTeam(): View
    {
        return view('admin.teams.form', ['team' => new Team]);
    }

    public function storeTeam(Request $request): RedirectResponse
    {
        $validated = $this->validateTeam($request);
        $validated['login_slug'] = $this->uniqueTeamSlug($validated['login_slug'] ?? $validated['name']);

        $team = Team::create($validated);

        return redirect()->route('admin.teams.index')->with('success', "Đã tạo {$team->name}");
    }

    public function editTeam(Team $team): View
    {
        return view('admin.teams.form', compact('team'));
    }

    public function updateTeam(Request $request, Team $team): RedirectResponse
    {
        $validated = $this->validateTeam($request, $team);
        $validated['login_slug'] = $this->uniqueTeamSlug($validated['login_slug'] ?? $validated['name'], $team);

        $team->update($validated);

        return redirect()->route('admin.teams.index')->with('success', "Đã cập nhật {$team->name}");
    }

    public function records(Request $request): View
    {
        $records = $this->filtered($request)->with('team')->latest()->paginate(30)->withQueryString();
        $teams = Team::query()->orderBy('name')->get();

        return view('admin.records', compact('records', 'teams'));
    }

    public function show(ApRecord $record): View
    {
        $record->load('team');

        return view('admin.show', compact('record'));
    }

    public function edit(ApRecord $record, FloorProgressService $progress): View
    {
        return view('team.form', ['record' => $record, 'team' => $record->team, 'admin' => true, 'floorOptions' => $progress->floorOptions()]);
    }

    public function update(SaveApRecordRequest $request, ApRecord $record, ApRecordManager $manager): RedirectResponse
    {
        $record = $manager->save($record, $request->validated());

        return redirect()->route('admin.records.show', $record)->with('success', "Đã lưu {$record->ap_name}");
    }

    public function destroy(ApRecord $record, ApRecordManager $manager): RedirectResponse
    {
        $manager->delete($record);

        return redirect()->route('admin.records.index')->with('success', 'Đã xóa bản ghi.');
    }

    public function export(Request $request): StreamedResponse
    {
        $records = $this->filtered($request)->with('team')->oldest()->cursor();

        return response()->streamDownload(function () use ($records): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['id', 'team', 'floor', 'ap_no', 'ap_name', 'status', 'work_date', 'issue_reason', 'issue_note', 'location_photo', 'mac_photo', 'cable_photo', 'issue_photo', 'created_at', 'updated_at']);
            foreach ($records as $record) {
                fputcsv($out, [$record->id, $record->team?->name, $record->floor, $record->ap_no, $record->ap_name, $record->status, $record->work_date?->format('Y-m-d'), $record->issue_reason, $record->issue_note, $record->location_photo, $record->mac_photo, $record->cable_photo, $record->issue_photo, $record->created_at, $record->updated_at]);
            }
            fclose($out);
        }, 'ap-records-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        return ApRecord::query()
            ->when($request->date, fn (Builder $q, $date) => $q->whereDate('work_date', $date))
            ->when($request->floor, fn (Builder $q, $floor) => $q->where('floor', $floor))
            ->when($request->team, fn (Builder $q, $team) => $q->where('team_id', $team))
            ->when($request->status, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($request->ap_name, fn (Builder $q, $name) => $q->where('ap_name', 'like', '%'.$name.'%'));
    }

    private function validateTeam(Request $request, ?Team $team = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'login_slug' => ['nullable', 'string', 'max:255', Rule::unique('teams', 'login_slug')->ignore($team?->id)],
            'access_code' => ['required', 'string', 'max:255'],
        ]);
    }

    private function uniqueTeamSlug(string $source, ?Team $team = null): string
    {
        $base = Str::slug($source) ?: 'team';
        $slug = $base;
        $suffix = 2;

        while (Team::query()
            ->where('login_slug', $slug)
            ->when($team, fn (Builder $q) => $q->whereKeyNot($team->id))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
