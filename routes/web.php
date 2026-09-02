<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Actions\Logout;
use App\Http\Controllers\RoleController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('blank', 'blank')->name('blank');
Route::view('forms', 'forms')->name('forms');

Route::middleware(['auth', 'permission:roles.view'])->group(function () {
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create')->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.update')->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete')->name('roles.destroy');
    Route::post('permissions', [RoleController::class, 'storePermission'])->middleware('permission:roles.create')->name('permissions.store');
    Route::put('permissions/{permission}', [RoleController::class, 'updatePermission'])->middleware('permission:roles.update')->name('permissions.update');
    Route::delete('permissions/{permission}', [RoleController::class, 'destroyPermission'])->middleware('permission:roles.delete')->name('permissions.destroy');
});

Route::post('logout', function (Logout $logout) {
    $logout();

    return redirect('/');
})->middleware('auth')->name('logout');

require __DIR__ . '/auth.php';
