<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShipmentLead\DashboardController;
use App\Http\Controllers\ShipmentLead\EmailAccountController;
use App\Http\Controllers\ShipmentLead\EmailSyncController;
use App\Http\Controllers\ShipmentLead\LeadController;
use App\Http\Controllers\ShipmentLead\UserManagementController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (auth middleware will handle login redirect if unauthenticated)
Route::get('/', function () {
    return redirect()->route('shipment-leads.dashboard');
});

// Guest Auth Routes
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Admin Routes
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Shipment Lead Management System ──
    Route::prefix('shipment-leads')->name('shipment-leads.')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Multiple Email Accounts Management (Static routes MUST come before {id} wildcard routes)
        Route::post('/accounts/test-connection', [EmailAccountController::class, 'testConnection'])->name('accounts.test-connection');
        Route::get('/accounts', [EmailAccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [EmailAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [EmailAccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{id}/edit', [EmailAccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/accounts/{id}', [EmailAccountController::class, 'update'])->name('accounts.update');
        Route::delete('/accounts/{id}', [EmailAccountController::class, 'destroy'])->name('accounts.destroy');

        // Leads Management
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show');
        Route::patch('/leads/{id}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
        Route::patch('/leads/{id}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::post('/leads/{id}/notes', [LeadController::class, 'addNote'])->name('leads.add-note');
        Route::patch('/leads/{id}/extracted', [LeadController::class, 'updateExtracted'])->name('leads.update-extracted');

        // Email Synchronization & Logs
        Route::post('/sync', [EmailSyncController::class, 'sync'])->name('sync');
        Route::get('/sync-logs', [EmailSyncController::class, 'history'])->name('sync-logs.index');

        // Team User Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        // Profile & Change Password
        Route::get('/profile/change-password', [AuthController::class, 'showChangePassword'])->name('profile.change-password');
        Route::post('/profile/change-password', [AuthController::class, 'updatePassword']);
    });
});
