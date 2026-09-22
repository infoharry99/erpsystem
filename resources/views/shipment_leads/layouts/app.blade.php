<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shipment Lead Management') - Globetrotters Logistics</title>

    <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --gt-light-bg: #f4f9ff;
            --gt-white: #ffffff;
            --gt-light-blue: #e0f2fe;
            --gt-blue-border: #bae6fd;
            --gt-primary: #0284c7;
            --gt-primary-hover: #0369a1;
            --gt-text-dark: #0f172a;
            --gt-text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--gt-light-bg);
            color: var(--gt-text-dark);
        }

        h1, h2, h3, h4 {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            font-weight: 700;
            color: var(--gt-text-dark);
        }
        .page-main-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.15rem;
            color: #0f172a;
            letter-spacing: -0.2px;
        }
        .brand-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            color: var(--gt-text-dark);
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--gt-primary) !important;
            border-color: var(--gt-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--gt-primary-hover) !important;
            border-color: var(--gt-primary-hover) !important;
            box-shadow: 0 4px 8px rgba(3, 105, 161, 0.3);
        }

        /* Sidebar - Pure White with Light Blue Accent */
        .sidebar {
            min-height: 100vh;
            background-color: var(--gt-white);
            color: var(--gt-text-dark);
            width: 230px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s;
            border-right: 1px solid var(--gt-blue-border);
            box-shadow: 2px 0 10px rgba(2, 132, 199, 0.05);
        }

        .sidebar .nav-link {
            color: #475569;
            padding: 0.65rem 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            margin: 0.15rem 0.5rem;
            transition: all 0.2s ease;
        }
        .sidebar .nav-link:hover {
            color: var(--gt-primary);
            background-color: #f0f9ff;
        }
        .sidebar .nav-link.active {
            color: #ffffff !important;
            background-color: var(--gt-primary);
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }
        .sidebar .nav-link i {
            width: 1.35rem;
        }

        /* Main Content Layout - Maximized Horizontal Room */
        .main-content {
            margin-left: 230px;
            padding: 1.15rem 1.5rem;
        }

        /* Top Navbar - Clean White & Light Blue */
        .navbar-top {
            background-color: var(--gt-white);
            border-bottom: 1px solid var(--gt-blue-border);
            padding: 0.7rem 1.5rem;
            margin-left: 230px;
            box-shadow: 0 1px 3px rgba(2, 132, 199, 0.05);
        }

        /* Modern Compact Table Styles */
        .table-leads {
            width: 100%;
            table-layout: fixed;
            font-size: 0.8125rem;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-leads thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.65rem 0.6rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .table-leads tbody td {
            padding: 0.65rem 0.6rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #1e293b;
            background-color: #ffffff;
        }
        .table-leads tbody tr {
            transition: background-color 0.15s ease;
        }
        .table-leads tbody tr:hover td {
            background-color: #f8fafc !important;
        }
        .table-leads tbody tr.lead-unreplied td:first-child {
            border-left: 3.5px solid #f59e0b;
        }
        .table-leads thead th:first-child,
        .table-leads tbody td:first-child {
            padding-left: 0.85rem;
        }
        .table-leads thead th:last-child,
        .table-leads tbody td:last-child {
            padding-right: 0.85rem;
        }

        /* Avatar Circle for Initials */
        .avatar-initial {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: -0.5px;
        }
        .avatar-bg-0 { background-color: #e0f2fe; color: #0284c7; }
        .avatar-bg-1 { background-color: #dcfce7; color: #16a34a; }
        .avatar-bg-2 { background-color: #fef3c7; color: #d97706; }
        .avatar-bg-3 { background-color: #f3e8ff; color: #9333ea; }
        .avatar-bg-4 { background-color: #ffe4e6; color: #e11d48; }
        .avatar-bg-5 { background-color: #ccfbf1; color: #0d9488; }

        /* Modern Status Pills */
        .pill-status {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
            line-height: 1.2;
        }
        .pill-replied {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .pill-not-replied {
            background-color: #fff1f2;
            color: #e11d48;
            border: 1px solid #fecdd3;
        }
        .pill-unread {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 700;
        }
        .pill-read {
            background-color: #f8fafc;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
        .unread-dot {
            width: 7px;
            height: 7px;
            min-width: 7px;
            border-radius: 50%;
            background-color: #2563eb;
            display: inline-block;
        }
        .pill-type {
            background-color: #f0f9ff;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }
        .pill-route {
            background-color: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            font-size: 0.72rem;
            padding: 0.18rem 0.45rem;
            border-radius: 4px;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pill-lead-status {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-size: 0.7rem;
            text-transform: capitalize;
        }
        .btn-action-open {
            padding: 0.22rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            background-color: #f0f9ff;
            color: #0284c7;
            border: 1px solid #bae6fd;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            white-space: nowrap;
        }
        .btn-action-open:hover {
            background-color: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }

        /* Modern Compact Form Styling */
        .card-modern {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .card-modern .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem 1.25rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .form-section-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #0284c7;
            margin-top: 1.25rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .form-section-title:first-child {
            margin-top: 0;
        }
        .form-compact .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.25rem;
        }
        .form-compact .form-control,
        .form-compact .form-select {
            font-size: 0.8125rem;
            padding: 0.42rem 0.7rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            background-color: #ffffff;
            transition: all 0.15s ease;
        }
        .form-compact .form-control:focus,
        .form-compact .form-select:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
            outline: none;
        }
        .form-compact .form-control::placeholder {
            color: #94a3b8;
            font-size: 0.8rem;
        }
        .form-hint {
            font-size: 0.72rem;
            color: #94a3b8;
            margin-top: 0.25rem;
        }

        /* Badges */
        .badge-replied {
            background-color: #10b981;
            color: white;
            font-weight: 600;
        }
        .badge-not-replied {
            background-color: #ef4444;
            color: white;
            font-weight: 600;
        }

        /* Cards */
        .card {
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
        }
        .card-stat {
            border: 1px solid var(--gt-blue-border);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.05);
            transition: transform 0.2s ease;
        }
        .card-stat:hover {
            transform: translateY(-3px);
        }

        /* Pagination Controls */
        .pagination {
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        .pagination .page-item .page-link {
            color: var(--gt-text-dark);
            border-radius: 0.375rem !important;
            margin: 0 3px;
            padding: 0.45rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid var(--gt-blue-border);
            background-color: #ffffff;
            transition: all 0.2s;
        }
        .pagination .page-item:hover .page-link {
            background-color: #f0f9ff;
            color: var(--gt-primary);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--gt-primary);
            border-color: var(--gt-primary);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3);
        }
        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-3">
        <!-- Globetrotters Logo -->
        <div class="text-center mb-4 px-2 pt-2">
            <img src="{{ asset('images/logo.png') }}" alt="Globetrotters Logo" class="img-fluid mb-2" style="max-height: 55px; width: auto;">
            <div class="small fw-bold text-uppercase text-secondary" style="letter-spacing: 1px; font-size: 0.7rem;">Shipment Leads CRM</div>
        </div>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="{{ route('shipment-leads.dashboard') }}" class="nav-link {{ request()->routeIs('shipment-leads.dashboard*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.leads.index') }}" class="nav-link {{ request()->routeIs('shipment-leads.leads.index') && !request()->has('reply_status') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-check"></i> All Leads
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'not_replied']) }}" class="nav-link {{ request()->get('reply_status') === 'not_replied' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-exclamation text-danger"></i> Not Replied
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'replied']) }}" class="nav-link {{ request()->get('reply_status') === 'replied' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-check text-success"></i> Replied
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'quotation_sent']) }}" class="nav-link {{ request()->get('lead_status') === 'quotation_sent' ? 'active' : '' }}">
                    <i class="fa-solid fa-file-invoice-dollar text-warning"></i> Quotations
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.accounts.index') }}" class="nav-link {{ request()->routeIs('shipment-leads.accounts*') ? 'active' : '' }}">
                    <i class="fa-solid fa-at"></i> Email Accounts
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.users.index') }}" class="nav-link {{ request()->routeIs('shipment-leads.users*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users-gear"></i> Team Users
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.sync-logs.index') }}" class="nav-link {{ request()->routeIs('shipment-leads.sync-logs*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i> Sync History
                </a>
            </li>
            <li>
                <a href="{{ route('shipment-leads.profile.change-password') }}" class="nav-link {{ request()->routeIs('shipment-leads.profile.change-password*') ? 'active' : '' }}">
                    <i class="fa-solid fa-key"></i> Change Password
                </a>
            </li>
        </ul>

        <hr class="text-secondary opacity-25">
        <div class="px-2 d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                User: <strong class="text-dark d-block text-truncate" style="max-width: 140px;">{{ Auth::user()->name ?? 'Admin User' }}</strong>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm" title="Sign Out">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Top Navbar -->
    <div class="navbar-top d-flex justify-content-between align-items-center">
        <div>
            <h4 class="m-0 page-main-title">@yield('page_title', 'Dashboard')</h4>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small" id="lastSyncText">
                <i class="fa-regular fa-clock me-1"></i>
                Last Sync: <strong id="lastSyncTime">{{ !empty($lastSyncTime) ? \Carbon\Carbon::parse($lastSyncTime)->timezone('Europe/London')->format('M d, H:i') . ' (UK)' : 'Not synced yet' }}</strong>
            </span>

            <a href="/clear-cache" class="btn btn-outline-secondary btn-sm" title="Clear System and View Cache">
                <i class="fa-solid fa-broom me-1"></i> Clear Cache
            </a>

            <button class="btn btn-primary btn-sm px-3" id="btnRefreshEmails" onclick="triggerEmailSync()">
                <i class="fa-solid fa-rotate me-1" id="syncSpinner"></i> Refresh Emails
            </button>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-power-off me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Toast Notification Modal -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="syncToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Emails Synchronized Successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function triggerEmailSync() {
            const btn = document.getElementById('btnRefreshEmails');
            const spinner = document.getElementById('syncSpinner');
            btn.disabled = true;
            spinner.classList.add('fa-spin');

            fetch("{{ route('shipment-leads.sync') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                spinner.classList.remove('fa-spin');

                if (data.success) {
                    const stats = data.stats;
                    const msg = `Sync Complete!\nNew Emails: ${stats.imported} | Leads: ${stats.leads} | Replies: ${stats.replies}`;
                    document.getElementById('toastMessage').innerText = msg;
                    const toast = new bootstrap.Toast(document.getElementById('syncToast'));
                    toast.show();

                    if (data.last_sync_formatted) {
                        document.getElementById('lastSyncTime').innerText = data.last_sync_formatted;
                    }
                    setTimeout(() => location.reload(), 2000);
                } else {
                    alert('Sync Error: ' + data.message);
                }
            })
            .catch(err => {
                btn.disabled = false;
                spinner.classList.remove('fa-spin');
                alert('An error occurred while syncing emails.');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
