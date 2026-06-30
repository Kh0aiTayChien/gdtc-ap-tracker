<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveApRecordRequest;
use App\Models\ApRecord;
use App\Models\Team;
use App\Services\ApRecordManager;
use App\Services\FloorProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function home(Request $request, Team $team): View|RedirectResponse
    {
        if ((int) $request->session()->get('team_id') === $team->id) {
            return redirect()->route('team.records.create', $team);
        }

        return view('auth.team', compact('team'));
    }

    public function login(Request $request, Team $team): RedirectResponse
    {
        $request->validate(['access_code' => ['required', 'string']]);

        if (! hash_equals($team->access_code, (string) $request->input('access_code'))) {
            return back()->withErrors(['access_code' => 'Mã truy cập không đúng.']);
        }

        $request->session()->regenerate();
        $request->session()->put('team_id', $team->id);

        return redirect()->route('team.records.create', $team);
    }

    public function logout(Request $request, Team $team): RedirectResponse
    {
        $request->session()->forget('team_id');
        $request->session()->regenerateToken();

        return redirect()->route('team.home', $team);
    }

    public function create(Team $team, FloorProgressService $progress): View
    {
        return view('team.form', ['team' => $team, 'record' => new ApRecord, 'floorOptions' => $progress->floorOptions()]);
    }

    public function store(SaveApRecordRequest $request, Team $team, ApRecordManager $manager): RedirectResponse
    {
        $record = $manager->save(null, $request->validated(), $team->id);

        return redirect()->route('team.today', $team)->with('success', "Đã lưu {$record->ap_name}");
    }

    public function today(Team $team): View
    {
        $records = $team->records()->whereDate('created_at', today())->latest()->get();

        return view('team.today', compact('team', 'records'));
    }

    public function floors(Team $team, FloorProgressService $progress): View
    {
        return view('shared.floors', [
            'title' => 'Tổng hợp theo tầng',
            'backUrl' => route('team.today', $team),
            'backLabel' => 'Hôm nay',
            ...$progress->summary(),
        ]);
    }

    public function edit(Team $team, ApRecord $record, FloorProgressService $progress): View
    {
        $this->assertOwnership($team, $record);

        return view('team.form', ['team' => $team, 'record' => $record, 'floorOptions' => $progress->floorOptions()]);
    }

    public function update(SaveApRecordRequest $request, Team $team, ApRecord $record, ApRecordManager $manager): RedirectResponse
    {
        $this->assertOwnership($team, $record);
        $record = $manager->save($record, $request->validated());

        return redirect()->route('team.today', $team)->with('success', "Đã lưu {$record->ap_name}");
    }

    private function assertOwnership(Team $team, ApRecord $record): void
    {
        abort_unless((int) $record->team_id === $team->id, 404);
    }

}
