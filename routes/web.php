<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShipmentLead\CustomerReportController;
use App\Http\Controllers\ShipmentLead\DashboardController;
use App\Http\Controllers\ShipmentLead\EmailAccountController;
use App\Http\Controllers\ShipmentLead\EmailSyncController;
use App\Http\Controllers\ShipmentLead\ExcludedDomainController;
use App\Http\Controllers\ShipmentLead\ExcludedKeywordController;
use App\Http\Controllers\ShipmentLead\LeadController;
use App\Http\Controllers\ShipmentLead\UserManagementController;
use Illuminate\Support\Facades\Route;

// Public Home Page: shows live inquiry volume and response status without requiring login
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);

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
        Route::match(['post', 'put'], '/accounts/test-connection', [EmailAccountController::class, 'testConnection'])->name('accounts.test-connection');
        Route::get('/accounts', [EmailAccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [EmailAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [EmailAccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{id}/edit', [EmailAccountController::class, 'edit'])->name('accounts.edit')->whereNumber('id');
        Route::put('/accounts/{id}', [EmailAccountController::class, 'update'])->name('accounts.update')->whereNumber('id');
        Route::delete('/accounts/{id}', [EmailAccountController::class, 'destroy'])->name('accounts.destroy')->whereNumber('id');

        // Excluded Domains Management (Lead Blacklist)
        Route::get('/excluded-domains', [ExcludedDomainController::class, 'index'])->name('excluded-domains.index');
        Route::post('/excluded-domains', [ExcludedDomainController::class, 'store'])->name('excluded-domains.store');
        Route::patch('/excluded-domains/{id}/toggle', [ExcludedDomainController::class, 'toggle'])->name('excluded-domains.toggle')->whereNumber('id');
        Route::delete('/excluded-domains/{id}', [ExcludedDomainController::class, 'destroy'])->name('excluded-domains.destroy')->whereNumber('id');
        Route::post('/excluded-domains/prune', [ExcludedDomainController::class, 'pruneLeads'])->name('excluded-domains.prune');

        // Excluded Subject Keywords / Phrases Management
        Route::get('/excluded-keywords', [ExcludedKeywordController::class, 'index'])->name('excluded-keywords.index');
        Route::post('/excluded-keywords', [ExcludedKeywordController::class, 'store'])->name('excluded-keywords.store');
        Route::patch('/excluded-keywords/{id}/toggle', [ExcludedKeywordController::class, 'toggle'])->name('excluded-keywords.toggle')->whereNumber('id');
        Route::delete('/excluded-keywords/{id}', [ExcludedKeywordController::class, 'destroy'])->name('excluded-keywords.destroy')->whereNumber('id');
        Route::post('/excluded-keywords/prune', [ExcludedKeywordController::class, 'pruneLeads'])->name('excluded-keywords.prune');

        // Leads Management
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{id}', [LeadController::class, 'show'])->name('leads.show')->whereNumber('id');
        Route::patch('/leads/{id}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status')->whereNumber('id');
        Route::patch('/leads/{id}/assign', [LeadController::class, 'assign'])->name('leads.assign')->whereNumber('id');
        Route::post('/leads/{id}/notes', [LeadController::class, 'addNote'])->name('leads.add-note')->whereNumber('id');
        Route::patch('/leads/{id}/extracted', [LeadController::class, 'updateExtracted'])->name('leads.update-extracted')->whereNumber('id');
        Route::delete('/leads/{id}/mark-not-lead', [LeadController::class, 'markNotLead'])->name('leads.mark-not-lead')->whereNumber('id');

        // Customer-Wise Lead Reports
        Route::get('/customer-reports', [CustomerReportController::class, 'index'])->name('customer-reports.index');
        Route::get('/customer-reports/show', [CustomerReportController::class, 'show'])->name('customer-reports.show');

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

    // Deduplicate any existing leads with identical email subjects
    try {
        $dedupCount = app(\App\Services\Lead\LeadService::class)->deduplicateExistingLeads();
        $results[] = "Deduplication: Merged and removed {$dedupCount} duplicate lead(s) with identical email subjects.";
    } catch (\Throwable $e) {
        $results[] = 'Deduplication notice: ' . $e->getMessage();
    }

    // Prune false-positive non-inquiry leads (internal, payment, billing, membership, reports)
    try {
        $pruneCount = app(\App\Services\Lead\LeadService::class)->pruneNonLeads();
        $results[] = "Pruning: Removed {$pruneCount} non-inquiry lead(s) (internal emails, payment reminders, membership renewals, reports).";
    } catch (\Throwable $e) {
        $results[] = 'Pruning notice: ' . $e->getMessage();
    }

    // Sync/backfill lead stages based on subject codes (Stage 1: New, Stage 2: QGLT = Quotation Sent, Stage 3: QGLT+GLT = Final Lead, excluding GLTEXO)
    try {
        $leadService = app(\App\Services\Lead\LeadService::class);
        $stageUpdated = $leadService->syncAllLeadStages();
        $totalLeads = \App\Models\ShipmentLead\Lead::count();
        $results[] = "Lead Stages: Evaluated {$totalLeads} lead(s). Updated {$stageUpdated} lead stage(s) (moved QGLT to Quotations and QGLT+GLT to Final Leads).";
    } catch (\Throwable $e) {
        $results[] = 'Stage evaluation notice: ' . $e->getMessage();
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

