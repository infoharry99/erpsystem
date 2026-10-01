<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\ExcludedDomain;
use App\Models\ShipmentLead\Lead;
use App\Services\Lead\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcludedDomainController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of excluded domains.
     */
    public function index(Request $request)
    {
        $query = ExcludedDomain::with('creator')->latest();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $excludedDomains = $query->paginate(15)->withQueryString();

        // Calculate lead counts that exist for each domain currently in DB
        $domainLeadCounts = [];
        foreach ($excludedDomains as $item) {
            $domain = $item->domain;
            $domainLeadCounts[$item->id] = Lead::where(function ($q) use ($domain) {
                $q->where('customer_email', 'like', '%@' . $domain)
                  ->orWhere('customer_email', 'like', '%@%.' . $domain);
            })->count();
        }

        return view('shipment_leads.excluded_domains.index', compact('excludedDomains', 'domainLeadCounts'));
    }

    /**
     * Store a newly created excluded domain in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $rawDomain = strtolower(trim((string) $request->input('domain')));
        $cleanDomain = preg_replace('#^https?://#i', '', $rawDomain);
        $cleanDomain = ltrim($cleanDomain, '@');
        if (str_starts_with($cleanDomain, 'www.')) {
            $cleanDomain = substr($cleanDomain, 4);
        }
        $cleanDomain = explode('/', $cleanDomain)[0];
        $cleanDomain = trim($cleanDomain);

        if (empty($cleanDomain) || !str_contains($cleanDomain, '.')) {
            return back()->withErrors(['domain' => 'Please enter a valid domain name (e.g. mesk.com or maersk.com).'])->withInput();
        }

        if (ExcludedDomain::where('domain', $cleanDomain)->exists()) {
            return back()->withErrors(['domain' => "The domain '{$cleanDomain}' is already in the excluded domains list."])->withInput();
        }

        $excludedDomain = ExcludedDomain::create([
            'domain' => $cleanDomain,
            'description' => $request->input('description'),
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        $prunedCount = 0;
        if ($request->boolean('prune_existing', true)) {
            $prunedCount = $this->leadService->pruneLeadsForDomain($cleanDomain);
        }

        $msg = "Domain '{$cleanDomain}' added to exclusion list!";
        if ($prunedCount > 0) {
            $msg .= " Also removed {$prunedCount} existing lead(s) associated with this domain.";
        }

        return redirect()->route('shipment-leads.excluded-domains.index')->with('success', $msg);
    }

    /**
     * Toggle active/inactive status of an excluded domain.
     */
    public function toggle($id)
    {
        $excluded = ExcludedDomain::findOrFail($id);
        $excluded->is_active = !$excluded->is_active;
        $excluded->save();

        $statusStr = $excluded->is_active ? 'activated' : 'paused';
        return redirect()->back()->with('success', "Domain '{$excluded->domain}' exclusion has been {$statusStr}.");
    }

    /**
     * Remove the specified domain from exclusion list.
     */
    public function destroy($id)
    {
        $excluded = ExcludedDomain::findOrFail($id);
        $domainName = $excluded->domain;
        $excluded->delete();

        return redirect()->route('shipment-leads.excluded-domains.index')
            ->with('success', "Domain '{$domainName}' removed from exclusion list.");
    }

    /**
     * Scan and prune all existing leads matching any active excluded domain.
     */
    public function pruneLeads()
    {
        $prunedCount = $this->leadService->pruneNonLeads();

        return redirect()->route('shipment-leads.excluded-domains.index')
            ->with('success', "Pruning completed! Removed {$prunedCount} non-inquiry / excluded domain lead(s).");
    }
}
