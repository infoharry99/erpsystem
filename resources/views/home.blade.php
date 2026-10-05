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
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'quotation_sent']) }}" class="clickable-card h-100" title="Click to log in and view quotations sent">
                    <div class="card card-modern shadow-sm h-100 p-3" style="border-left: 3.5px solid #f59e0b !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="text-uppercase text-warning fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    Quotations Sent (QGLT)
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

        <!-- ROW 2: SECONDARY PIPELINE CARDS -->
        <div class="row g-2 mb-3">
            <div class="col-xl-2 col-md-4 col-6">
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
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'new']) }}" class="clickable-card" title="Click to view new leads">
                    <div class="card card-modern shadow-sm p-2 px-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-primary text-uppercase fw-semibold" style="font-size: 0.68rem;">New Leads</span>
                                <h5 class="m-0 fw-bold text-primary" style="font-size: 1.1rem;">{{ number_format($newLeadsCount) }}</h5>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7; font-size: 0.8rem;">
                                <i class="fa-solid fa-sparkles"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'final_lead']) }}" class="clickable-card" title="Click to view final leads (QGLT + GLT)">
                    <div class="card card-modern shadow-sm p-2 px-3" style="border-left: 3px solid #8b5cf6 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; color: #7c3aed;">Final Leads</span>
                                <h5 class="m-0 fw-bold text-dark" style="font-size: 1.1rem;">{{ number_format($finalLeadsCount) }}</h5>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #ede9fe; color: #7c3aed; font-size: 0.8rem;">
                                <i class="fa-solid fa-flag-checkered"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'booked']) }}" class="clickable-card" title="Click to view booked shipments">
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
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'won']) }}" class="clickable-card" title="Click to view won deals">
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
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'lost']) }}" class="clickable-card" title="Click to view lost/closed leads">
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

        <!-- INTERACTIVE LEAD CONVERSION FUNNEL (PIPELINE VELOCITY) -->
        <div class="card card-modern shadow-sm mb-3 p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2.5">
                <div>
                    <h6 class="m-0 fw-bold text-dark" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-arrows-split-up-and-left text-primary me-1.5"></i> Lead Conversion Funnel (Pipeline Velocity)
                    </h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Operational funnel progression from initial intake to quotation (QGLT) and confirmed booking (GLT)</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">UK London Time</span>
                </div>
            </div>

            <!-- Funnel Pipeline Steps -->
            <div class="row g-2 align-items-center text-center">
                <!-- Step 1: Total Intake -->
                <div class="col-md-3 col-6">
                    <a href="{{ route('shipment-leads.leads.index') }}" class="clickable-card h-100">
                        <div class="p-2.5 rounded-3 border h-100" style="background: #f0f9ff; border-color: #bae6fd !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-white text-primary border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">STAGE 1</span>
                                <i class="fa-solid fa-boxes-stacked text-primary" style="font-size: 0.85rem;"></i>
                            </div>
                            <div class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Total Intake</div>
                            <h4 class="fw-bold text-dark my-1">{{ number_format($totalLeads) }}</h4>
                            <span class="text-muted" style="font-size: 0.7rem;">100% of all inquiries</span>
                        </div>
                    </a>
                </div>

                <!-- Step 2: Replied Inquiries -->
                <div class="col-md-3 col-6">
                    <a href="{{ route('shipment-leads.leads.index', ['reply_status' => 'replied']) }}" class="clickable-card h-100">
                        <div class="p-2.5 rounded-3 border h-100" style="background: #ecfdf5; border-color: #a7f3d0 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-white text-success border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700;">STAGE 2</span>
                                <i class="fa-solid fa-circle-check text-success" style="font-size: 0.85rem;"></i>
                            </div>
                            <div class="text-success fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Replied Leads</div>
                            <h4 class="fw-bold text-success my-1">{{ number_format($repliedCount + $quotationsSent + $finalLeadsCount) }}</h4>
                            <span class="text-muted" style="font-size: 0.7rem;">
                                {{ $totalLeads > 0 ? round((($repliedCount + $quotationsSent + $finalLeadsCount) / $totalLeads) * 100, 1) : 0 }}% response rate
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Step 3: Quotations Sent (QGLT) -->
                <div class="col-md-3 col-6">
                    <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'quotation_sent']) }}" class="clickable-card h-100">
                        <div class="p-2.5 rounded-3 border h-100" style="background: #fef3c7; border-color: #fde68a !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-white text-warning border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700; color: #b45309 !important;">STAGE 3</span>
                                <i class="fa-solid fa-file-invoice-dollar text-warning" style="font-size: 0.85rem;"></i>
                            </div>
                            <div class="fw-bold" style="font-size: 0.7rem; text-transform: uppercase; color: #b45309;">Quotation Sent (QGLT)</div>
                            <h4 class="fw-bold my-1" style="color: #b45309;">{{ number_format($quotationsSent) }}</h4>
                            <span class="text-muted" style="font-size: 0.7rem;">
                                {{ $totalLeads > 0 ? round(($quotationsSent / $totalLeads) * 100, 1) : 0 }}% of inquiries
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Step 4: Final Leads (GLT) -->
                <div class="col-md-3 col-6">
                    <a href="{{ route('shipment-leads.leads.index', ['lead_status' => 'final_lead']) }}" class="clickable-card h-100">
                        <div class="p-2.5 rounded-3 border h-100" style="background: #ede9fe; border-color: #c4b5fd !important;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-white border px-2 py-0.5" style="font-size: 0.68rem; font-weight: 700; color: #6d28d9 !important;">STAGE 4</span>
                                <i class="fa-solid fa-flag-checkered" style="font-size: 0.85rem; color: #6d28d9;"></i>
                            </div>
                            <div class="fw-bold" style="font-size: 0.7rem; text-transform: uppercase; color: #6d28d9;">Final Leads (GLT)</div>
                            <h4 class="fw-bold my-1" style="color: #6d28d9;">{{ number_format($finalLeadsCount) }}</h4>
                            <span class="text-muted" style="font-size: 0.7rem;">
                                {{ $totalLeads > 0 ? round(($finalLeadsCount / $totalLeads) * 100, 1) : 0 }}% conversion
                            </span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- ROW 3: CHARTS & OPERATIONAL BREAKDOWN -->
        <div class="row g-3 mb-3">
            <!-- 14-Day Dual-Series Inquiry Intake vs Response Trend Chart -->
            <div class="col-lg-8">
                <div class="card card-modern shadow-sm p-3 h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2.5 border-bottom pb-2">
                        <div>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.88rem;">
                                <i class="fa-solid fa-chart-line text-primary me-1"></i> Inquiry Intake vs. Response Trend (Last 14 Days)
                            </h6>
                            <span class="text-muted" style="font-size: 0.72rem;">Comparison of daily freight inquiries received vs sales replies dispatched</span>
                        </div>
                        <div class="d-flex align-items-center gap-1 mt-1 mt-sm-0" id="publicTrendChartToggle">
                            <button type="button" class="btn btn-outline-secondary active btn-sm py-0.5 px-2" style="font-size: 0.72rem; border-radius: 6px;" onclick="togglePublicTrend('all', this)">All</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2" style="font-size: 0.72rem; border-radius: 6px;" onclick="togglePublicTrend('intake', this)">Inquiries</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0.5 px-2" style="font-size: 0.72rem; border-radius: 6px;" onclick="togglePublicTrend('replies', this)">Replies</button>
                        </div>
                    </div>
                    <div style="height: 250px; position: relative;">
                        <canvas id="publicLeadVolumeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Stage Conversion Distribution Doughnut Chart -->
            <div class="col-lg-4">
                <div class="card card-modern shadow-sm p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                        <div>
                            <h6 class="fw-bold text-dark m-0" style="font-size: 0.88rem;">
                                <i class="fa-solid fa-chart-pie text-primary me-1"></i> Stage Conversion Distribution
                            </h6>
                            <span class="text-muted" style="font-size: 0.72rem;">Operational distribution across inquiry stages</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-center justify-content-between">
                        <div style="height: 175px; width: 100%; position: relative;">
                            <canvas id="publicLeadStageChart"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                                <div class="fw-bold text-dark fs-4 m-0" style="line-height: 1;">{{ number_format($totalLeads) }}</div>
                                <span class="text-muted" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Leads</span>
                            </div>
                        </div>

                        <!-- Stage Legend Pills -->
                        <div class="w-100 mt-2.5 d-flex flex-column gap-1">
                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                                <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #0284c7;"></span> New Inquiries</span>
                                <span class="text-muted fw-bold">{{ number_format($newLeadsCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($newLeadsCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                                <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #ef4444;"></span> Waiting Reply</span>
                                <span class="text-danger fw-bold">{{ number_format($notRepliedCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($notRepliedCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                                <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #10b981;"></span> Replied</span>
                                <span class="text-success fw-bold">{{ number_format($repliedCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($repliedCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                                <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #f59e0b;"></span> Quotation Sent (QGLT)</span>
                                <span class="text-warning fw-bold" style="color: #b45309 !important;">{{ number_format($quotationsSent) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($quotationsSent / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded bg-light" style="font-size: 0.74rem;">
                                <span class="fw-semibold text-dark"><span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background: #8b5cf6;"></span> Final Leads (GLT)</span>
                                <span class="fw-bold" style="color: #6d28d9;">{{ number_format($finalLeadsCount) }} <span class="fw-normal text-secondary">({{ $totalLeads > 0 ? round(($finalLeadsCount / $totalLeads) * 100, 1) : 0 }}%)</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3.5: FREIGHT MODE BREAKDOWN -->
        <div class="card card-modern shadow-sm p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2.5">
                <div>
                    <h6 class="fw-bold text-dark m-0" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-ship text-primary me-1"></i> Freight Mode & Equipment Intake Breakdown
                    </h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Distribution across Air, Ocean FCL/LCL, Reefer, and Road freight modes</span>
                </div>
            </div>
            <div class="row g-3">
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
                    <div class="col-md-4 col-sm-6">
                        <div class="p-2.5 rounded bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-1.5" style="font-size: 0.78rem;">
                                <span class="fw-semibold text-dark">
                                    <i class="fa-solid {{ $info['icon'] }} me-1" style="color: {{ $info['color'] }}; width: 16px;"></i> {{ $mode }}
                                </span>
                                <span class="text-muted fw-bold">{{ number_format($cnt) }} <span class="fw-normal">({{ $percent }}%)</span></span>
                            </div>
                            <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 9999px;">
                                <div class="progress-bar rounded-pill" role="progressbar" style="width: {{ $percent }}%; background-color: {{ $info['color'] }};"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
        // Global Font Settings for Chart.js
        Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, -apple-system, sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = "#64748b";

        // 1. 14-Day Dual-Series Inquiry Intake vs. Response Trend
        const ctxTrend = document.getElementById('publicLeadVolumeChart').getContext('2d');
        const gradBlue = ctxTrend.createLinearGradient(0, 0, 0, 240);
        gradBlue.addColorStop(0, 'rgba(2, 132, 199, 0.28)');
        gradBlue.addColorStop(1, 'rgba(2, 132, 199, 0.01)');

        const gradGreen = ctxTrend.createLinearGradient(0, 0, 0, 240);
        gradGreen.addColorStop(0, 'rgba(16, 185, 129, 0.25)');
        gradGreen.addColorStop(1, 'rgba(16, 185, 129, 0.01)');

        window.publicTrendChart = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [
                    {
                        label: 'Inquiries Intake',
                        data: {!! json_encode($leadsByDayCounts) !!},
                        borderColor: '#0284c7',
                        backgroundColor: gradBlue,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#0284c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Replies Dispatched',
                        data: {!! json_encode($repliedByDayCounts) !!},
                        borderColor: '#10b981',
                        backgroundColor: gradGreen,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 1.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 10,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 12,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 8,
                        usePointStyle: true
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, font: { size: 10 } },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });

        window.togglePublicTrend = function(type, btn) {
            if (!window.publicTrendChart) return;
            document.querySelectorAll('#publicTrendChartToggle .btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            if (type === 'all') {
                window.publicTrendChart.setDatasetVisibility(0, true);
                window.publicTrendChart.setDatasetVisibility(1, true);
            } else if (type === 'intake') {
                window.publicTrendChart.setDatasetVisibility(0, true);
                window.publicTrendChart.setDatasetVisibility(1, false);
            } else if (type === 'replies') {
                window.publicTrendChart.setDatasetVisibility(0, false);
                window.publicTrendChart.setDatasetVisibility(1, true);
            }
            window.publicTrendChart.update();
        };

        // 2. Stage Conversion Distribution Doughnut Chart
        const ctxPublicStage = document.getElementById('publicLeadStageChart').getContext('2d');
        new Chart(ctxPublicStage, {
            type: 'doughnut',
            data: {
                labels: ['New Inquiries', 'Waiting Reply', 'Replied', 'Quotation Sent (QGLT)', 'Final Leads (GLT)'],
                datasets: [{
                    data: [
                        {{ (int)$newLeadsCount }},
                        {{ (int)$notRepliedCount }},
                        {{ (int)$repliedCount }},
                        {{ (int)$quotationsSent }},
                        {{ (int)$finalLeadsCount }}
                    ],
                    backgroundColor: ['#0284c7', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.92)',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.parsed;
                                const pct = total > 0 ? Math.round((val / total) * 1000) / 10 : 0;
                                return ' ' + context.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                },
                cutout: '72%'
            }
        });
    </script>
</body>
</html>
