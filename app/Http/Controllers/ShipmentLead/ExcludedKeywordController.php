<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\ExcludedKeyword;
use App\Models\ShipmentLead\Lead;
use App\Services\Lead\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcludedKeywordController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    /**
     * Display a listing of excluded keywords & phrases.
     */
    public function index(Request $request)
    {
        $query = ExcludedKeyword::with('creator')->latest();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('keyword', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $excludedKeywords = $query->paginate(15)->withQueryString();

        // Calculate lead counts currently in DB matching each keyword in subject
        $keywordLeadCounts = [];
        foreach ($excludedKeywords as $item) {
            $kw = $item->keyword;
            $keywordLeadCounts[$item->id] = Lead::where('email_subject', 'like', "%{$kw}%")->count();
        }

        return view('shipment_leads.excluded_keywords.index', compact('excludedKeywords', 'keywordLeadCounts'));
    }

    /**
     * Store a newly created excluded keyword/phrase in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $cleanKeyword = strtolower(trim((string) $request->input('keyword')));

        if (empty($cleanKeyword) || strlen($cleanKeyword) < 2) {
            return back()->withErrors(['keyword' => 'Please enter a keyword or phrase with at least 2 characters.'])->withInput();
        }

        if (ExcludedKeyword::where('keyword', $cleanKeyword)->exists()) {
            return back()->withErrors(['keyword' => "The keyword or phrase '{$cleanKeyword}' is already in the exclusion list."])->withInput();
        }

        $excludedKeyword = ExcludedKeyword::create([
            'keyword' => $cleanKeyword,
            'description' => $request->input('description'),
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        $prunedCount = 0;
        if ($request->boolean('prune_existing', true)) {
            $prunedCount = $this->leadService->pruneLeadsForKeyword($cleanKeyword);
        }

        $msg = "Keyword '{$cleanKeyword}' added to exclusion list!";
        if ($prunedCount > 0) {
            $msg .= " Also removed {$prunedCount} existing lead(s) matching this keyword in subject.";
        }

        return redirect()->route('shipment-leads.excluded-keywords.index')->with('success', $msg);
    }

    /**
     * Toggle active/inactive status of an excluded keyword.
     */
    public function toggle($id)
    {
        $excluded = ExcludedKeyword::findOrFail($id);
        $excluded->is_active = !$excluded->is_active;
        $excluded->save();

        $statusStr = $excluded->is_active ? 'activated' : 'paused';
        return redirect()->back()->with('success', "Keyword '{$excluded->keyword}' exclusion has been {$statusStr}.");
    }

    /**
     * Remove the specified keyword from exclusion list.
     */
    public function destroy($id)
    {
        $excluded = ExcludedKeyword::findOrFail($id);
        $keywordName = $excluded->keyword;
        $excluded->delete();

        return redirect()->route('shipment-leads.excluded-keywords.index')
            ->with('success', "Keyword '{$keywordName}' removed from exclusion list.");
    }

    /**
     * Scan and prune all existing leads matching any active excluded keywords or domains.
     */
    public function pruneLeads()
    {
        $prunedCount = $this->leadService->pruneNonLeads();

        return redirect()->route('shipment-leads.excluded-keywords.index')
            ->with('success', "Pruning completed! Removed {$prunedCount} non-inquiry lead(s).");
    }
}
