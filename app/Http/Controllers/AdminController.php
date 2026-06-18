<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveApRecordRequest;
use App\Models\ApRecord;
use App\Models\Team;
use App\Services\ApRecordManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function dashboard(): View
    {
        $byFloor = ApRecord::query()->selectRaw("floor, SUM(CASE WHEN status = 'installed' THEN 1 ELSE 0 END) installed, SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) blocked, COUNT(*) total")
            ->groupBy('floor')->orderBy('floor')->get();
        $byTeam = Team::query()->withCount([
            'records as installed' => fn (Builder $q) => $q->where('status', 'installed'),
            'records as blocked' => fn (Builder $q) => $q->where('status', 'blocked'),
        ])->get();
        $latest = ApRecord::query()->with('team')->latest()->limit(15)->get();

        return view('admin.dashboard', compact('byFloor', 'byTeam', 'latest'));
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

    public function edit(ApRecord $record): View
    {
        return view('team.form', ['record' => $record, 'team' => $record->team, 'admin' => true]);
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
            fputcsv($out, ['id', 'team', 'floor', 'ap_no', 'ap_name', 'status', 'issue_reason', 'issue_note', 'location_photo', 'mac_photo', 'cable_photo', 'issue_photo', 'created_at', 'updated_at']);
            foreach ($records as $record) {
                fputcsv($out, [$record->id, $record->team?->name, $record->floor, $record->ap_no, $record->ap_name, $record->status, $record->issue_reason, $record->issue_note, $record->location_photo, $record->mac_photo, $record->cable_photo, $record->issue_photo, $record->created_at, $record->updated_at]);
            }
            fclose($out);
        }, 'ap-records-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request): Builder
    {
        return ApRecord::query()
            ->when($request->date, fn (Builder $q, $date) => $q->whereDate('created_at', $date))
            ->when($request->floor, fn (Builder $q, $floor) => $q->where('floor', $floor))
            ->when($request->team, fn (Builder $q, $team) => $q->where('team_id', $team))
            ->when($request->status, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($request->ap_name, fn (Builder $q, $name) => $q->where('ap_name', 'like', '%'.$name.'%'));
    }
}
