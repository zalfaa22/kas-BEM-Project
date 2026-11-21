<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, PortalController};

Route::get('/', fn() => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [PortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/my-invoices', [PortalController::class, 'myInvoices'])->name('my-invoices');
    Route::post('/invoices/{invoice}/proof', [PortalController::class, 'uploadProof'])->name('invoices.proof');
    Route::get('/reminders', [PortalController::class, 'reminders'])->name('reminders');
    Route::get('/notices', [PortalController::class, 'notices'])->name('notices');
    Route::post('/notices/{notice}/read', [PortalController::class, 'readNotice'])->name('notices.read');
    Route::middleware('role:bendahara')->group(function () {
        Route::get('/members', [PortalController::class, 'members'])->name('members');
        Route::post('/members', [PortalController::class, 'storeMember'])->name('members.store');
        Route::put('/members/{member}', [PortalController::class, 'updateMember'])->name('members.update');
        Route::get('/dues', [PortalController::class, 'dues'])->name('dues');
        Route::post('/dues', [PortalController::class, 'storeDues'])->name('dues.store');
        Route::post('/dues/{rule}/toggle', [PortalController::class, 'toggleDues'])->name('dues.toggle');
        Route::post('/invoices/generate', [PortalController::class, 'generate'])->name('invoices.generate');
        Route::get('/invoices', [PortalController::class, 'invoices'])->name('invoices.index');
        Route::get('/payments', [PortalController::class, 'payments'])->name('payments');
        Route::post('/payments/{payment}/verify', [PortalController::class, 'verify'])->name('payments.verify');
        Route::get('/transactions', [PortalController::class, 'transactions'])->name('transactions');
        Route::post('/transactions/expense', [PortalController::class, 'storeExpense'])->name('transactions.expense');
        Route::get('/reports', [PortalController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [PortalController::class, 'export'])->name('reports.export');
    });
    Route::middleware('role:ketua')->group(function () {
        Route::get('/monitoring', [PortalController::class, 'monitoring'])->name('monitoring');
    });
});
