<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SavingGoalController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function() {
    Route::get('/budgets/{year}/{month}', [BudgetController::class, 'show'])->name('budgets.show');
    Route::post('/budgets/{year}/{month}', [BudgetController::class, 'storeOrUpdate'])->name('budgets.store');
    Route::post('/budgets/{year}/{month}/allocations', [BudgetController::class, 'saveAllocations'])
        ->name('budgets.allocations.save');
});

Route::middleware(['auth'])->group(function(){
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
});

Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function(){
    Route::get('/saving-goals', [SavingGoalController::class, 'index'])
        ->name('saving.goals');
    Route::post('/saving-goals', [SavingGoalController::class, 'store'])
        ->name('saving.goals.store');
    Route::post('/saving-goals/{goal}', [SavingGoalController::class, 'update'])
        ->name('saving.goals.update');
});

Route::middleware('auth')->group(function(){
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
});

require __DIR__.'/auth.php';
