<?php

namespace App\Http\Controllers\ShipmentLead;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::withCount('assignedLeads')->get();
        return view('shipment_leads.users.index', compact('users'));
    }

    public function create()
    {
        return view('shipment_leads.users.form', ['user' => new User()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('shipment-leads.users.index')
            ->with('success', "User '{$user->name}' created successfully!");
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('shipment_leads.users.form', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('shipment-leads.users.index')
            ->with('success', "User '{$user->name}' updated successfully!");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (User::count() <= 1) {
            return redirect()->back()->with('error', 'Cannot delete the last remaining user account.');
        }

        $user->delete();

        return redirect()->route('shipment-leads.users.index')
            ->with('success', 'User deleted successfully!');
    }
}
