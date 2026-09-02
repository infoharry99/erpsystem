<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\ShipmentLead\EmailAccount;
use App\Services\Email\ImapConnectionService;
use Illuminate\Http\Request;

class EmailAccountController extends Controller
{
    protected ImapConnectionService $connectionService;

    public function __construct(ImapConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }

    public function index()
    {
        $accounts = EmailAccount::withCount(['emails', 'leads'])->get();
        return view('shipment_leads.accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('shipment_leads.accounts.form', ['account' => new EmailAccount()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:shipment_email_accounts,email',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer',
            'imap_encryption' => 'nullable|string|in:ssl,tls,none',
            'imap_username' => 'required|string|max:255',
            'imap_password' => 'required|string',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|string|in:ssl,tls,none',
            'inbox_folder' => 'required|string|max:255',
            'sent_folder' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $account = EmailAccount::create($validated);

        return redirect()->route('shipment-leads.accounts.index')
            ->with('success', "Email account '{$account->name}' created successfully!");
    }

    public function edit($id)
    {
        $account = EmailAccount::findOrFail($id);
        return view('shipment_leads.accounts.form', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $account = EmailAccount::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:shipment_email_accounts,email,' . $account->id,
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer',
            'imap_encryption' => 'nullable|string|in:ssl,tls,none',
            'imap_username' => 'required|string|max:255',
            'imap_password' => 'nullable|string',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer',
            'smtp_encryption' => 'nullable|string|in:ssl,tls,none',
            'inbox_folder' => 'required|string|max:255',
            'sent_folder' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if (empty($validated['imap_password'])) {
            unset($validated['imap_password']);
        }

        $account->update($validated);

        return redirect()->route('shipment-leads.accounts.index')
            ->with('success', "Email account '{$account->name}' updated successfully!");
    }

    public function destroy($id)
    {
        $account = EmailAccount::findOrFail($id);
        $account->delete();

        return redirect()->route('shipment-leads.accounts.index')
            ->with('success', "Email account deleted successfully!");
    }

    public function testConnection(Request $request)
    {
        $account = new EmailAccount($request->only([
            'name', 'email', 'imap_host', 'imap_port', 'imap_encryption',
            'imap_username', 'inbox_folder', 'sent_folder'
        ]));

        $password = $request->input('imap_password');
        if (!empty($password)) {
            $account->imap_password = $password;
        } elseif ($request->filled('id')) {
            $existing = EmailAccount::find($request->id);
            if ($existing) {
                $account->imap_password = $existing->decrypted_password;
            }
        }

        $result = $this->connectionService->testConnection($account);

        return response()->json($result);
    }
}
