<?php

use App\Http\Controllers\CalculationController;
use App\Http\Controllers\JoppdController;
use App\Http\Controllers\LocalGovernmentController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RefereeController;
use App\Http\Controllers\TaxRateController;
use App\Models\Organization;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $organization = Organization::query()
        ->where('is_active', true)
        ->first();
    $hasOrganizations = Organization::exists();

    return view('dashboard', compact('organization', 'hasOrganizations'));
});

Route::get('/organizacija', [OrganizationController::class, 'edit'])
    ->name('organization.edit');

Route::put('/organizacija', [OrganizationController::class, 'update'])
    ->name('organization.update');

Route::get('/organizacije', [OrganizationController::class, 'index'])
    ->name('organizations.index');

Route::get('/organizacije/nova', [OrganizationController::class, 'create'])
    ->name('organizations.create');

Route::post('/organizacije', [OrganizationController::class, 'store'])
    ->name('organizations.store');

Route::post('/organizacije/{organization}/aktiviraj', [OrganizationController::class, 'activate'])
    ->name('organizations.activate');

Route::delete('/organizacije/{organization}', [OrganizationController::class, 'destroy'])
    ->name('organizations.destroy');

Route::resource('primatelji', RefereeController::class)
    ->parameters(['primatelji' => 'referee'])
    ->except(['show']);

Route::get('/obracun/novi', [CalculationController::class, 'create'])
    ->name('calculations.create');

Route::post('/obracun/pregled', [CalculationController::class, 'preview'])
    ->name('calculations.preview');

Route::post('/obracun/spremi', [CalculationController::class, 'store'])
    ->name('calculations.store');

Route::get('/obracuni', [CalculationController::class, 'index'])
    ->name('calculations.index');

Route::get('/obracuni/{calculation}', [CalculationController::class, 'show'])
    ->name('calculations.show');

Route::post('/obracuni/{calculation}/oznaci-kao-placeno', [CalculationController::class, 'markAsPaid'])
    ->name('calculations.mark-as-paid');

Route::delete('/obracuni/{calculation}', [CalculationController::class, 'destroy'])
    ->name('calculations.destroy');

Route::get('/obracuni/{calculation}/nalozi', [CalculationController::class, 'paymentOrders'])
    ->name('calculations.payment-orders');

Route::resource('porezne-stope', TaxRateController::class)
    ->only(['index', 'create', 'store']);

Route::post('/lokalne-jedinice', [LocalGovernmentController::class, 'store'])
    ->name('local-governments.store');
Route::get('/joppd', [JoppdController::class, 'create'])->name('joppd.create');
Route::post('/joppd/pregled', [JoppdController::class, 'preview'])->name('joppd.preview');
Route::post('/joppd/pripreme', [JoppdController::class, 'prepare'])->name('joppd.prepare');
Route::get('/joppd/pripreme/{preparation}', [JoppdController::class, 'show'])->name('joppd.show');
Route::get('/joppd/pripreme/{preparation}/xml', [JoppdController::class, 'download'])->name('joppd.download');
