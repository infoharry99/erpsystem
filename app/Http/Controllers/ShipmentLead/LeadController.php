<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\Email;
use App\Models\ShipmentLead\EmailAccount;
use App\Models\ShipmentLead\Lead;
use App\Models\ShipmentLead\LeadNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['account', 'assignedUser', 'email']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('email_subject', 'like', "%{$search}%")
                  ->orWhere('origin', 'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%");
            });
        }

        if ($request->filled('email_account_id')) {
            $query->where('email_account_id', $request->email_account_id);
        }

        if ($request->filled('reply_status')) {
            $query->where('reply_status', $request->reply_status);
        }

        if ($request->filled('lead_status')) {
            $query->where('lead_status', $request->lead_status);
        }

        if ($request->filled('shipment_type')) {
            $query->where('shipment_type', $request->shipment_type);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('received_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('received_date', '<=', $request->date_to);
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('received_date', 'asc');
                break;
            case 'replied':
                $query->orderBy('reply_status', 'asc')->orderBy('received_date', 'desc');
                break;
            case 'not_replied':
                $query->orderBy('reply_status', 'desc')->orderBy('received_date', 'desc');
                break;
            default:
                $query->orderBy('received_date', 'desc');
                break;
        }

        $leads = $query->paginate(15)->withQueryString();

        $accounts = EmailAccount::all();
        $users = User::all();

        return view('shipment_leads.leads.index', compact('leads', 'accounts', 'users'));
    }

    public function show($id)
    {
        $lead = Lead::with(['email.attachments', 'account', 'assignedUser', 'leadNotes.user', 'repliedByAccount'])
            ->findOrFail($id);

        $conversation = Email::where('email_account_id', $lead->email_account_id)
            ->where(function ($q) use ($lead) {
                if ($lead->email && $lead->email->thread_id) {
                    $q->where('thread_id', $lead->email->thread_id);
                }
                $q->orWhere('from_email', $lead->customer_email)
                  ->orWhere('to_email', $lead->customer_email);
            })
            ->with('attachments')
            ->orderBy('created_at', 'asc')
            ->get();

        $users = User::all();
        $statuses = ['new', 'not_replied', 'replied', 'follow_up', 'quotation_sent', 'negotiation', 'booked', 'won', 'lost', 'spam', 'closed'];

        return view('shipment_leads.leads.show', compact('lead', 'conversation', 'users', 'statuses'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'lead_status' => 'required|string',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update(['lead_status' => $request->lead_status]);

        return redirect()->back()->with('success', 'Lead status updated successfully!');
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update(['assigned_to' => $request->assigned_to]);

        return redirect()->back()->with('success', 'Lead assignment updated!');
    }

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $lead = Lead::findOrFail($id);

        LeadNote::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id(),
            'note' => $request->note,
        ]);

        return redirect()->back()->with('success', 'Internal note added!');
    }

    public function updateExtracted(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $lead->update($request->only([
            'customer_name', 'customer_phone', 'company_name',
            'shipment_type', 'origin', 'destination', 'pol', 'pod',
            'pickup_address', 'delivery_address', 'commodity',
            'weight', 'dimensions', 'quantity', 'pallets',
            'container_type', 'shipment_date', 'incoterms'
        ]));

        return redirect()->back()->with('success', 'Shipment details updated successfully!');
    }
}
