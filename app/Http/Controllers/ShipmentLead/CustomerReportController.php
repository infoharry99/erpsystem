<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\Lead;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerReportController extends Controller
{
    /**
     * Display a customer-wise lead summary report grouped by unique email address.
     */
    public function index(Request $request)
    {
        // 1. Calculate overall top-level metrics across all customers
        $metrics = DB::table('shipment_leads')
            ->selectRaw("
                COUNT(DISTINCT LOWER(TRIM(customer_email))) as total_customers,
                COUNT(*) as total_leads,
                SUM(CASE WHEN reply_status = 'replied' THEN 1 ELSE 0 END) as total_replied,
                SUM(CASE WHEN lead_status = 'quotation_sent' THEN 1 ELSE 0 END) as total_quotations,
                SUM(CASE WHEN lead_status = 'final_lead' THEN 1 ELSE 0 END) as total_final
            ")
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->first();

        $totalUniqueCustomers = (int) ($metrics->total_customers ?? 0);
        $totalLeads = (int) ($metrics->total_leads ?? 0);
        $totalReplied = (int) ($metrics->total_replied ?? 0);
        $totalQuotations = (int) ($metrics->total_quotations ?? 0);
        $totalFinal = (int) ($metrics->total_final ?? 0);

        // 2. Build grouped customer query
        $query = DB::table('shipment_leads')
            ->selectRaw("
                LOWER(TRIM(customer_email)) as email,
                COALESCE(
                    MAX(CASE WHEN customer_name IS NOT NULL AND customer_name != '' AND customer_name != 'Unknown' THEN customer_name END),
                    MAX(customer_name),
                    'Unknown'
                ) as customer_name,
                MAX(company_name) as company_name,
                MAX(customer_phone) as customer_phone,
                COUNT(*) as total_leads,
                SUM(CASE WHEN reply_status = 'replied' THEN 1 ELSE 0 END) as replied_leads,
                SUM(CASE WHEN lead_status = 'quotation_sent' THEN 1 ELSE 0 END) as quotation_leads,
                SUM(CASE WHEN lead_status = 'final_lead' THEN 1 ELSE 0 END) as final_leads,
                MAX(received_date) as last_inquiry_date,
                MIN(received_date) as first_inquiry_date
            ")
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '');

        // Search by keyword in email, customer name, or company
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(customer_email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(customer_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(company_name) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->groupBy(DB::raw('LOWER(TRIM(customer_email))'));

        // Stage/Status filter
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'has_quotations':
                    $query->havingRaw('SUM(CASE WHEN lead_status = \'quotation_sent\' THEN 1 ELSE 0 END) > 0');
                    break;
                case 'has_final':
                    $query->havingRaw('SUM(CASE WHEN lead_status = \'final_lead\' THEN 1 ELSE 0 END) > 0');
                    break;
                case 'has_replied':
                    $query->havingRaw('SUM(CASE WHEN reply_status = \'replied\' THEN 1 ELSE 0 END) > 0');
                    break;
                case 'unreplied_only':
                    $query->havingRaw('(COUNT(*) - SUM(CASE WHEN reply_status = \'replied\' THEN 1 ELSE 0 END)) > 0');
                    break;
            }
        }

        // Sorting
        $sort = $request->input('sort', 'most_leads');
        switch ($sort) {
            case 'most_quotations':
                $query->orderByDesc('quotation_leads')->orderByDesc('total_leads')->orderByDesc('last_inquiry_date');
                break;
            case 'most_final':
                $query->orderByDesc('final_leads')->orderByDesc('total_leads')->orderByDesc('last_inquiry_date');
                break;
            case 'most_replied':
                $query->orderByDesc('replied_leads')->orderByDesc('total_leads')->orderByDesc('last_inquiry_date');
                break;
            case 'latest_date':
                $query->orderByDesc('last_inquiry_date');
                break;
            case 'oldest_date':
                $query->orderBy('last_inquiry_date', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('customer_name', 'asc');
                break;
            default:
                $query->orderByDesc('total_leads')->orderByDesc('last_inquiry_date');
                break;
        }

        // Fetch and paginate reliably
        $allResults = $query->get();
        $totalFiltered = $allResults->count();
        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $items = $allResults->slice(($page - 1) * $perPage, $perPage)->values();

        $customers = new LengthAwarePaginator(
            $items,
            $totalFiltered,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('shipment_leads.customer_reports.index', compact(
            'customers',
            'totalUniqueCustomers',
            'totalLeads',
            'totalReplied',
            'totalQuotations',
            'totalFinal'
        ));
    }

    /**
     * Display detailed inquiries for a single customer, separated into 4 tabs:
     * 1. Leads (All leads)
     * 2. Replied Leads
     * 3. Quotation Leads
     * 4. Final Leads
     */
    public function show(Request $request)
    {
        $email = strtolower(trim($request->input('email', '')));

        if (empty($email)) {
            return redirect()->route('shipment-leads.customer-reports.index')->with('error', 'Please specify a customer email address.');
        }

        $allLeads = Lead::with(['account', 'assignedUser', 'email.attachments'])
            ->whereRaw('LOWER(TRIM(customer_email)) = ?', [$email])
            ->orderByDesc('received_date')
            ->get();

        if ($allLeads->isEmpty()) {
            return redirect()->route('shipment-leads.customer-reports.index')->with('error', "No inquiry leads found for customer: {$email}");
        }

        // Customer Details
        $customerName = $allLeads->first(function ($l) {
            return !empty($l->customer_name) && strtolower($l->customer_name) !== 'unknown';
        })?->customer_name ?? $allLeads->first()->customer_name ?? 'Unknown';

        $companyName = $allLeads->first(fn($l) => !empty($l->company_name))?->company_name ?? null;
        $customerPhone = $allLeads->first(fn($l) => !empty($l->customer_phone))?->customer_phone ?? null;

        // Categorize into the 4 requested tabs
        $leads = $allLeads;
        $repliedLeads = $allLeads->filter(fn($l) => $l->reply_status === 'replied');
        $quotationLeads = $allLeads->filter(fn($l) => $l->lead_status === 'quotation_sent');
        $finalLeads = $allLeads->filter(fn($l) => $l->lead_status === 'final_lead');

        $activeTab = $request->input('tab', 'leads');
        if (!in_array($activeTab, ['leads', 'replied', 'quotations', 'final'])) {
            $activeTab = 'leads';
        }

        return view('shipment_leads.customer_reports.show', compact(
            'email',
            'customerName',
            'companyName',
            'customerPhone',
            'leads',
            'repliedLeads',
            'quotationLeads',
            'finalLeads',
            'activeTab'
        ));
    }
}
