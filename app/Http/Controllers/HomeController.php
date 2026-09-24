<?php

namespace App\Http\Controllers;

use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display public inquiry numbers and analytics without requiring login.
     * Confidential email bodies and customer contact details remain protected behind authentication.
     */
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

        // 14-day intake trend
        $dates = [];
        $leadsByDayCounts = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->format('M d');
            $leadsByDayCounts[] = Lead::whereDate('received_date', $date)->count();
        }

        // Status breakdown counts
        $statuses = ['new', 'not_replied', 'replied', 'follow_up', 'quotation_sent', 'negotiation', 'booked', 'won', 'lost', 'spam', 'closed'];
        $statusCounts = [];
        foreach ($statuses as $status) {
            $statusCounts[ucwords(str_replace('_', ' ', $status))] = Lead::where('lead_status', $status)->count();
        }

        // Freight mode counts
        $shipmentTypeCounts = [
            'Sea FCL' => Lead::where('shipment_type', 'sea_fcl')->count(),
            'Sea LCL' => Lead::where('shipment_type', 'sea_lcl')->count(),
            'Air Freight' => Lead::where('shipment_type', 'air_freight')->count(),
            'Road Freight' => Lead::where('shipment_type', 'road_freight')->count(),
            'Reefer' => Lead::where('shipment_type', 'reefer')->count(),
            'Other/Unknown' => Lead::where('shipment_type', 'unknown')->count(),
        ];

        $lastSyncTime = EmailAccount::max('last_sync_at');

        return view('home', compact(
            'totalLeads',
            'newToday',
            'thisWeek',
            'notRepliedCount',
            'repliedCount',
            'quotationsSent',
            'bookedCount',
            'wonCount',
            'lostCount',
            'dates',
            'leadsByDayCounts',
            'statusCounts',
            'shipmentTypeCounts',
            'lastSyncTime'
        ));
    }
}
