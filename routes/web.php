<?php

use App\Http\Controllers\CarteraController;
use App\Http\Controllers\EmpresaMandanteController;
use App\Http\Controllers\RoleController;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'permission:dashboard.view'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('blank', 'blank')->middleware(['auth', 'permission:users.view'])->name('blank');
Route::view('forms', 'forms')->middleware(['auth', 'permission:forms.view'])->name('forms');

Route::get('cartera', [CarteraController::class, 'index'])
    ->middleware(['auth', 'permission:cartera.view'])
    ->name('cartera.index');

Route::post('cartera/importar', [CarteraController::class, 'importar'])
    ->middleware(['auth', 'permission:cartera.import'])
    ->name('cartera.importar');

Route::get('empresas-mandantes', [EmpresaMandanteController::class, 'index'])
    ->middleware(['auth', 'permission:empresas.view'])
    ->name('empresas-mandantes.index');

Route::post('empresas-mandantes', [EmpresaMandanteController::class, 'store'])
    ->middleware(['auth', 'permission:empresas.create'])
    ->name('empresas-mandantes.store');

Route::put('empresas-mandantes/{empresaMandante}', [EmpresaMandanteController::class, 'update'])
    ->middleware(['auth', 'permission:empresas.update'])
    ->name('empresas-mandantes.update');

Route::post('empresa-mandante/seleccionar', [EmpresaMandanteController::class, 'seleccionar'])
    ->middleware(['auth', 'permission:empresas.view'])
    ->name('empresa-mandante.seleccionar');

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

require __DIR__.'/auth.php';
