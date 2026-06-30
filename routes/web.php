<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/t/{team:login_slug}', [TeamController::class, 'home'])->name('team.home');
Route::post('/t/{team:login_slug}/login', [TeamController::class, 'login'])->name('team.login');
Route::middleware('team.session')->prefix('/t/{team:login_slug}')->name('team.')->group(function () {
    Route::post('/logout', [TeamController::class, 'logout'])->name('logout');
    Route::get('/records/create', [TeamController::class, 'create'])->name('records.create');
    Route::post('/records', [TeamController::class, 'store'])->name('records.store');
    Route::get('/today', [TeamController::class, 'today'])->name('today');
    Route::get('/floors', [TeamController::class, 'floors'])->name('floors');
    Route::get('/records/{record}/edit', [TeamController::class, 'edit'])->name('records.edit');
    Route::put('/records/{record}', [TeamController::class, 'update'])->name('records.update');
});

Route::get('/admin', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');
Route::middleware('admin.session')->prefix('/admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/floors', [AdminController::class, 'floors'])->name('floors');
    Route::get('/floor-config', [AdminController::class, 'floorConfig'])->name('floor-config');
    Route::put('/floor-config', [AdminController::class, 'updateFloorConfig'])->name('floor-config.update');
    Route::get('/teams', [AdminController::class, 'teams'])->name('teams.index');
    Route::get('/teams/create', [AdminController::class, 'createTeam'])->name('teams.create');
    Route::post('/teams', [AdminController::class, 'storeTeam'])->name('teams.store');
    Route::get('/teams/{team}/edit', [AdminController::class, 'editTeam'])->name('teams.edit');
    Route::put('/teams/{team}', [AdminController::class, 'updateTeam'])->name('teams.update');
    Route::get('/records', [AdminController::class, 'records'])->name('records.index');
    Route::get('/records/{record}', [AdminController::class, 'show'])->name('records.show');
    Route::get('/records/{record}/edit', [AdminController::class, 'edit'])->name('records.edit');
    Route::put('/records/{record}', [AdminController::class, 'update'])->name('records.update');
    Route::delete('/records/{record}', [AdminController::class, 'destroy'])->name('records.destroy');
    Route::get('/export-csv', [AdminController::class, 'export'])->name('export');
});
