<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Status Overview - Globetrotters Logistics</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --gt-light-bg: #f8fafc;
            --gt-white: #ffffff;
            --gt-light-blue: #e0f2fe;
            --gt-blue-border: #e2e8f0;
            --gt-primary: #0284c7;
            --gt-primary-hover: #0369a1;
            --gt-text-dark: #0f172a;
            --gt-text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--gt-light-bg);
            color: var(--gt-text-dark);
            min-height: 100vh;
        }

        /* Top Navbar */
        .public-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--gt-blue-border);
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .brand-logo-img {
            max-height: 40px;
            width: auto;
        }

        /* Modern Card Styling */
        .card-modern {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .card-modern:hover {
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06) !important;
        }

        .clickable-card {
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .clickable-card:hover {
            color: inherit;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--gt-primary) !important;
            border-color: var(--gt-primary) !important;
            color: #ffffff !important;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.5rem 1.15rem;
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--gt-primary-hover) !important;
            border-color: var(--gt-primary-hover) !important;
            box-shadow: 0 4px 8px rgba(3, 105, 161, 0.3);
        }

        .btn-outline-primary {
            border-color: var(--gt-primary);
            color: var(--gt-primary);
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-outline-primary:hover {
            background-color: var(--gt-primary);
            color: #ffffff;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <!-- Public Top Navbar -->
    <nav class="public-navbar sticky-top">
        <div class="container-fluid px-lg-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none">
                    <img src="{{ asset('images/logo.png') }}" alt="Globetrotters Logistics" class="brand-logo-img">
                </a>
                <div class="vr d-none d-sm-block text-secondary" style="height: 24px; opacity: 0.25;"></div>
                <div class="d-none d-sm-block">
                    <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.1;">Shipment Operations</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Live Public Status Board</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-md-3">
                <!-- London UK Time & Sync Indicator -->
                <div class="d-none d-md-flex align-items-center gap-2 text-muted" style="font-size: 0.75rem;">
                    <span class="status-pill bg-light border text-dark">
                        <i class="fa-regular fa-clock text-primary me-1"></i>
                        {{ now()->timezone('Europe/London')->format('H:i') }} (UK)
                    </span>
                    <span class="status-pill bg-light border text-secondary" title="Mailbox Last Synced">
                        <i class="fa-solid fa-arrows-rotate text-success me-1"></i>
                        Synced: {{ !empty($lastSyncTime) ? \Carbon\Carbon::parse($lastSyncTime)->timezone('Europe/London')->format('M d, H:i') : 'Just now' }}
                    </span>
                </div>

                @auth
                    <a href="{{ route('shipment-leads.dashboard') }}" class="btn btn-primary btn-sm px-3" style="font-size: 0.8125rem;">
                        <i class="fa-solid fa-gauge me-1"></i> Open CRM Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-3" style="font-size: 0.8125rem;">
                        <i class="fa-solid fa-lock me-1"></i> Sign In to View Details
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container-fluid px-3 px-lg-4 py-3">

        <!-- Top Banner / Notice -->
        <div class="card card-modern p-3 mb-3" style="background: linear-gradient(to right, #ffffff, #f0f9ff); border-left: 4px solid var(--gt-primary);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-dark m-0" style="font-size: 1.05rem;">
                        <i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Live Shipment Inquiries Status
                    </h5>
                    <p class="text-muted m-0 mt-1" style="font-size: 0.8rem;">
                        Real-time inquiry counts received across freight inboxes. 
                        <strong>Client contact details and email contents are confidential</strong> and require an authorized staff login to view or reply.
                    </p>
                </div>
                <div>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm px-3" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Staff Login
                    </a>
                </div>
            </div>
        </div>

        <!-- ROW 1: PRIMARY KPI STAT CARDS (EXACT MATCH TO CLIENT REQUEST) -->
        <div class="row g-3 mb-3">
            <!-- Total Inquiries -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('shipment-leads.leads.index') }}" class="clickable-card h-100" title="Click to log in and view all inquiries">
                    <div class="card card-modern shadow-sm h-100 p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    Total Inquiries
                                </span>
                                <h3 class="fw-bold text-dark m-0 my-1" style="font-size: 1.75rem;">
                                    {{ number_format($totalLeads) }}
                                </h3>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <span class="badge bg-light text-primary border me-1" style="font-size: 0.7rem; font-weight: 600;">
                                        +{{ number_format($newToday) }} today
                                    </span>
                                    <span>{{ number_format($thisWeek) }} this week</span>
                                </div>
                            </div>
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0f2fe; color: #0284c7;">
                                <i class="fa-solid fa-boxes-stacked" style="font-size: 1.15rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Waiting For Reply -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'not_replied']) }}" class="clickable-card h-100" title="Click to log in and view unreplied inquiries">
                    <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #ef4444 !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-uppercase text-danger fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    Waiting For Reply
                                </span>
                                <h3 class="fw-bold text-danger m-0 my-1" style="font-size: 1.75rem;">
                                    {{ number_format($notRepliedCount) }}
                                </h3>
                                <div style="font-size: 0.75rem;">
                                    <span class="text-danger fw-semibold">
                                        Requires sales response <i class="fa-solid fa-arrow-right ms-0.5" style="font-size: 0.65rem;"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fee2e2; color: #ef4444;">
                                <i class="fa-solid fa-clock-rotate-left" style="font-size: 1.15rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Replied Inquiries -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'replied']) }}" class="clickable-card h-100" title="Click to log in and view replied inquiries">
                    <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #10b981 !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-uppercase text-success fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    Replied Inquiries
                                </span>
                                <h3 class="fw-bold text-success m-0 my-1" style="font-size: 1.75rem;">
                                    {{ number_format($repliedCount) }}
                                </h3>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    @if($totalLeads > 0)
                                        <span class="badge bg-light text-success border me-1" style="font-size: 0.7rem; font-weight: 600;">
                                            {{ round(($repliedCount / $totalLeads) * 100, 1) }}%
                                        </span>
                                        <span>response rate</span>
                                    @else
                                        <span>No inquiries yet</span>
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #dcfce7; color: #10b981;">
                                <i class="fa-solid fa-circle-check" style="font-size: 1.15rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Quotations Sent -->
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('shipment-leads.leads.index', ['status' => 'quotation_sent']) }}" class="clickable-card h-100" title="Click to log in and view quotations sent">
                    <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #f59e0b !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    Quotations Sent
                                </span>
                                <h3 class="fw-bold text-dark m-0 my-1" style="font-size: 1.75rem;">
                                    {{ number_format($quotationsSent) }}
                                </h3>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <span>In pricing & quotation stage</span>
                                </div>
                            </div>
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-file-invoice-dollar" style="font-size: 1.15rem;"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- ROW 2: SECONDARY PIPELINE CARDS (EXACT MATCH TO CLIENT REQUEST) -->
        <div class="row g-2 mb-3">
            <div class="col-md-3 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['date_from' => \Carbon\Carbon::today()->format('Y-m-d')]) }}" class="clickable-card" title="Click to view today's inquiries">
                    <div class="card card-modern shadow-sm p-2 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Today's Intake</span>
                                <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($newToday) }}</h5>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7; font-size: 0.8rem;">
                                <i class="fa-solid fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['status' => 'booked']) }}" class="clickable-card" title="Click to view booked shipments">
                    <div class="card card-modern shadow-sm p-2 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Booked Shipments</span>
                                <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($bookedCount) }}</h5>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #ede9fe; color: #7c3aed; font-size: 0.8rem;">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['status' => 'won']) }}" class="clickable-card" title="Click to view won deals">
                    <div class="card card-modern shadow-sm p-2 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Won Deals</span>
                                <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($wonCount) }}</h5>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #dcfce7; color: #16a34a; font-size: 0.8rem;">
                                <i class="fa-solid fa-trophy"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['status' => 'lost']) }}" class="clickable-card" title="Click to view lost/closed leads">
                    <div class="card card-modern shadow-sm p-2 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;">Lost / Closed</span>
                                <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($lostCount) }}</h5>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f1f5f9; color: #475569; font-size: 0.8rem;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- ROW 3: CHARTS & OPERATIONAL BREAKDOWN -->
        <div class="row g-3 mb-3">
            <!-- 14-Day Inquiry Intake Chart -->
            <div class="col-lg-8">
                <div class="card card-modern shadow-sm p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.88rem;">
                                <i class="fa-solid fa-chart-line text-primary me-1"></i> Lead Volume Trend (Last 14 Days)
                            </h6>
                            <span class="text-muted" style="font-size: 0.72rem;">Daily inquiry volume received from all connected inboxes</span>
                        </div>
                        <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">UK London Time</span>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="publicLeadVolumeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Freight Mode Breakdown -->
            <div class="col-lg-4">
                <div class="card card-modern shadow-sm p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.88rem;">
                                <i class="fa-solid fa-ship text-primary me-1"></i> Freight Mode Intake
                            </h6>
                            <span class="text-muted" style="font-size: 0.72rem;">Classification of inquiries by logistics mode</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column justify-content-center gap-2 mt-2">
                        @php
                            $modeIcons = [
                                'Sea FCL' => ['icon' => 'fa-ship', 'color' => '#0284c7'],
                                'Sea LCL' => ['icon' => 'fa-boxes-stacked', 'color' => '#0ea5e9'],
                                'Air Freight' => ['icon' => 'fa-plane', 'color' => '#6366f1'],
                                'Road Freight' => ['icon' => 'fa-truck', 'color' => '#8b5cf6'],
                                'Reefer' => ['icon' => 'fa-snowflake', 'color' => '#06b6d4'],
                                'Other/Unknown' => ['icon' => 'fa-box', 'color' => '#94a3b8'],
                            ];
                        @endphp
                        @foreach($shipmentTypeCounts as $mode => $cnt)
                            @php
                                $percent = $totalLeads > 0 ? round(($cnt / $totalLeads) * 100, 1) : 0;
                                $info = $modeIcons[$mode] ?? ['icon' => 'fa-box', 'color' => '#64748b'];
                            @endphp
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.75rem;">
                                    <span class="fw-semibold text-dark">
                                        <i class="fa-solid {{ $info['icon'] }} me-1" style="color: {{ $info['color'] }}; width: 16px;"></i> {{ $mode }}
                                    </span>
                                    <span class="text-muted">{{ number_format($cnt) }} ({{ $percent }}%)</span>
                                </div>
                                <div class="progress" style="height: 5px; background-color: #f1f5f9;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%; background-color: {{ $info['color'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 4: SECURED ACCESS / LOGIN PROMPT -->
        <div class="card card-modern shadow-sm p-3 p-md-4 mb-3" style="border: 1px dashed #cbd5e1; background: #ffffff;">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef2f2; color: #ef4444;">
                            <i class="fa-solid fa-lock" style="font-size: 1.25rem;"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.95rem;">
                                Detailed Customer Messages & Sales Operations are Protected
                            </h6>
                            <p class="text-muted m-0 mt-1" style="font-size: 0.8rem; line-height: 1.5;">
                                There are currently <strong class="text-danger">{{ number_format($notRepliedCount) }}</strong> inquiries awaiting sales reply. 
                                To inspect customer contact info, read the original email threads, download attached documents, and dispatch quotations, please sign in with your Globetrotters user account.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('login') }}" class="btn btn-primary px-4 py-2" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Sign In to View Details
                    </a>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="container-fluid px-3 px-lg-4 py-3 text-center text-muted border-top bg-white" style="font-size: 0.75rem;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span>&copy; {{ date('Y') }} Globetrotters Logistics. All rights reserved.</span>
            <div class="d-flex align-items-center gap-3">
                <span>Timezone: <strong>Europe/London (UK)</strong></span>
                <a href="{{ route('login') }}" class="text-decoration-none text-primary fw-semibold">Staff Login</a>
            </div>
        </div>
    </footer>

    <!-- Chart.js Logic -->
    <script>
        const ctx = document.getElementById('publicLeadVolumeChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [{
                    label: 'Inquiries Received',
                    data: {!! json_encode($leadsByDayCounts) !!},
                    backgroundColor: 'rgba(2, 132, 199, 0.75)',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        ticks: { font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
</body>
</html>
