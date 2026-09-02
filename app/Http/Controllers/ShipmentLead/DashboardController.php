<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        $totalLeads = Lead::count();
        $newToday = Lead::whereDate('received_date', $today)->count();
        $thisWeek = Lead::where('received_date', '>=', $startOfWeek)->count();
        $notRepliedCount = Lead::where('reply_status', 'not_replied')->count();
        $repliedCount = Lead::where('reply_status', 'replied')->count();
        $quotationsSent = Lead::where('lead_status', 'quotation_sent')->count();
        $bookedCount = Lead::where('lead_status', 'booked')->count();
        $wonCount = Lead::where('lead_status', 'won')->count();
        $lostCount = Lead::where('lead_status', 'lost')->count();

        $unrepliedLeads = Lead::where('reply_status', 'not_replied')
            ->with(['account', 'assignedUser'])
            ->orderBy('received_date', 'asc')
            ->take(10)
            ->get();

        $recentLeads = Lead::with(['account', 'assignedUser'])
            ->latest('received_date')
            ->take(10)
            ->get();

        $dates = [];
        $leadsByDayCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('M d');
            $leadsByDayCounts[] = Lead::whereDate('received_date', $date)->count();
        }

        $statuses = ['new', 'not_replied', 'replied', 'follow_up', 'quotation_sent', 'negotiation', 'booked', 'won', 'lost', 'spam', 'closed'];
        $statusCounts = [];
        foreach ($statuses as $status) {
            $statusCounts[ucwords(str_replace('_', ' ', $status))] = Lead::where('lead_status', $status)->count();
        }

        $mailboxData = [];
        $accounts = EmailAccount::withCount('leads')->get();
        foreach ($accounts as $acc) {
            $mailboxData[$acc->email] = $acc->leads_count;
        }

        $shipmentTypeCounts = [
            'Sea FCL' => Lead::where('shipment_type', 'sea_fcl')->count(),
            'Sea LCL' => Lead::where('shipment_type', 'sea_lcl')->count(),
            'Air Freight' => Lead::where('shipment_type', 'air_freight')->count(),
            'Road Freight' => Lead::where('shipment_type', 'road_freight')->count(),
            'Reefer' => Lead::where('shipment_type', 'reefer')->count(),
            'Other/Unknown' => Lead::where('shipment_type', 'unknown')->count(),
        ];

        $lastSyncTime = EmailAccount::max('last_sync_at');

        return view('shipment_leads.dashboard', compact(
            'totalLeads',
            'newToday',
            'thisWeek',
            'notRepliedCount',
            'repliedCount',
            'quotationsSent',
            'bookedCount',
            'wonCount',
            'lostCount',
            'unrepliedLeads',
            'recentLeads',
            'dates',
            'leadsByDayCounts',
            'statusCounts',
            'mailboxData',
            'shipmentTypeCounts',
            'lastSyncTime'
        ));
    }
}
