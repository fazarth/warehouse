<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('identification_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $members = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'required|string|unique:members,phone',
            'identification_number' => 'required|string|unique:members,identification_number',
            'address' => 'required|string',
            'occupation' => 'nullable|string',
            'joined_at' => 'nullable|date',
        ]);

        $validated['status'] = 'active';
        $validated['joined_at'] = $validated['joined_at'] ?? now();

        $member = Member::create($validated);

        AuditLog::log('create', 'Member', $member->id, 'Created new member: ' . $member->name, null, $validated);

        return redirect()->route('members.show', $member)
            ->with('success', 'Member created successfully');
    }

    public function show(Member $member)
    {
        $member->load(['savings', 'loans.installments']);

        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'phone' => 'required|string|unique:members,phone,' . $member->id,
            'identification_number' => 'required|string|unique:members,identification_number,' . $member->id,
            'address' => 'required|string',
            'occupation' => 'nullable|string',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $oldValues = $member->toArray();
        $member->update($validated);

        AuditLog::log('update', 'Member', $member->id, 'Updated member: ' . $member->name, $oldValues, $validated);

        return redirect()->route('members.show', $member)
            ->with('success', 'Member updated successfully');
    }

    public function destroy(Member $member)
    {
        AuditLog::log('delete', 'Member', $member->id, 'Deleted member: ' . $member->name, $member->toArray(), null);

        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully');
    }

    public function deactivate(Member $member)
    {
        $member->update(['status' => 'inactive']);

        AuditLog::log('update', 'Member', $member->id, 'Deactivated member: ' . $member->name);

        return back()->with('success', 'Member deactivated successfully');
    }

    public function activate(Member $member)
    {
        $member->update(['status' => 'active']);

        AuditLog::log('update', 'Member', $member->id, 'Activated member: ' . $member->name);

        return back()->with('success', 'Member activated successfully');
    }
}
