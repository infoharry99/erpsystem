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

        // Multiple Email Accounts Management
        Route::post('/accounts/test-connection', [EmailAccountController::class, 'testConnection'])->name('accounts.test-connection');
        Route::get('/accounts', [EmailAccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [EmailAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [EmailAccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{id}/edit', [EmailAccountController::class, 'edit'])->name('accounts.edit')->whereNumber('id');
        Route::put('/accounts/{id}', [EmailAccountController::class, 'update'])->name('accounts.update')->whereNumber('id');
        Route::delete('/accounts/{id}', [EmailAccountController::class, 'destroy'])->name('accounts.destroy')->whereNumber('id');

        // Leads Management
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show')->whereNumber('id');
        Route::patch('/leads/{id}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status')->whereNumber('id');
        Route::patch('/leads/{id}/assign', [LeadController::class, 'assign'])->name('leads.assign')->whereNumber('id');
        Route::post('/leads/{id}/notes', [LeadController::class, 'addNote'])->name('leads.add-note')->whereNumber('id');
        Route::patch('/leads/{id}/extracted', [LeadController::class, 'updateExtracted'])->name('leads.update-extracted')->whereNumber('id');

        // Email Synchronization & Logs
        Route::post('/sync', [EmailSyncController::class, 'sync'])->name('sync');
        Route::get('/sync-logs', [EmailSyncController::class, 'history'])->name('sync-logs.index');

        // Team User Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('users.edit')->whereNumber('id');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('users.update')->whereNumber('id');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('users.destroy')->whereNumber('id');

        // Profile & Change Password
        Route::get('/profile/change-password', [AuthController::class, 'showChangePassword'])->name('profile.change-password');
        Route::post('/profile/change-password', [AuthController::class, 'updatePassword']);

        // Cache clear helper inside admin group
        Route::get('/clear-cache', function () {
            return redirect('/clear-cache');
        })->name('clear-cache');
    });
});

// Emergency Cache Clear & Diagnostic Route
Route::get('/clear-cache', function () {
    $results = [];

    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $results[] = 'Artisan view:clear: ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $results[] = 'Artisan view:clear error: ' . $e->getMessage();
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $results[] = 'Artisan cache:clear: ' . trim(\Illuminate\Support\Facades\Artisan::output());
    } catch (\Throwable $e) {
        $results[] = 'Artisan cache:clear error: ' . $e->getMessage();
    }

    // Force delete all cached blade templates
    $viewPath = storage_path('framework/views');
    $deletedFiles = 0;
    if (is_dir($viewPath)) {
        foreach (glob($viewPath . '/*.php') as $file) {
            if (is_file($file)) {
                @unlink($file);
                $deletedFiles++;
            }
        }
    }
    $results[] = "Manually deleted {$deletedFiles} compiled view files from storage/framework/views.";

    // Reset OPcache if active
    if (function_exists('opcache_reset')) {
        $op = @opcache_reset();
        $results[] = 'OPcache reset: ' . ($op ? 'success' : 'failed/disabled');
    }

    // Check actual content of index.blade.php
    $indexPath = resource_path('views/shipment_leads/leads/index.blade.php');
    $indexContentSnippet = 'File not found';
    if (file_exists($indexPath)) {
        $lines = file($indexPath);
        $headerLine = isset($lines[72]) ? trim($lines[72]) : 'Line 73 not found';
        $indexContentSnippet = htmlspecialchars($headerLine);
    }

    return response('<html><body style="font-family:sans-serif;padding:30px;line-height:1.6;">'
        . '<h2>Cache Cleared Successfully!</h2>'
        . '<ul>' . implode('', array_map(fn($r) => "<li>{$r}</li>", $results)) . '</ul>'
        . '<h3>Current File Header Check:</h3>'
        . '<pre style="background:#f4f4f4;padding:12px;border:1px solid #ccc;">' . $indexContentSnippet . '</pre>'
        . '<p><a href="/shipment-leads/leads" style="display:inline-block;padding:10px 20px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:5px;">Go back to Shipment Leads</a></p>'
        . '</body></html>');
});

