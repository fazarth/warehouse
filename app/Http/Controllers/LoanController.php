<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with('member');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        $loans = $query->orderBy('requested_at', 'desc')->paginate(20);

        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $members = Member::where('status', 'active')->get();
        return view('loans.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'tenor_months' => 'required|integer|min:1|max:360',
            'notes' => 'nullable|string',
        ]);

        // Check if member has active loan
        $activeLoan = Loan::where('member_id', $validated['member_id'])
            ->whereIn('status', ['active', 'approved'])
            ->first();

        if ($activeLoan) {
            return back()->withErrors(['member_id' => 'Member already has an active loan'])->withInput();
        }

        $validated['status'] = 'pending';
        $validated['requested_at'] = now();

        $loan = Loan::create($validated);

        AuditLog::log('create', 'Loan', $loan->id, 'Created loan request', null, $validated);

        return redirect()->route('loans.show', $loan)
            ->with('success', 'Loan request created successfully');
    }

    public function show(Loan $loan)
    {
        $loan->load(['member', 'installments', 'approver']);

        return view('loans.show', compact('loan'));
    }

    public function approve(Request $request, Loan $loan)
    {
        if ($loan->status !== 'pending') {
            return back()->with('error', 'Loan cannot be approved');
        }

        $loan->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'started_at' => now(),
        ]);

        // Generate installment schedule
        $loan->generateInstallmentSchedule();

        AuditLog::log('approve', 'Loan', $loan->id, 'Approved loan for member: ' . $loan->member->name);

        return back()->with('success', 'Loan approved successfully');
    }

    public function reject(Request $request, Loan $loan)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        if ($loan->status !== 'pending') {
            return back()->with('error', 'Loan cannot be rejected');
        }

        $loan->update([
            'status' => 'rejected',
            'notes' => $request->input('notes'),
        ]);

        AuditLog::log('reject', 'Loan', $loan->id, 'Rejected loan for member: ' . $loan->member->name);

        return back()->with('success', 'Loan rejected');
    }

    public function destroy(Loan $loan)
    {
        if (in_array($loan->status, ['active', 'approved'])) {
            return back()->with('error', 'Cannot delete active loan');
        }

        AuditLog::log('delete', 'Loan', $loan->id, 'Deleted loan', $loan->toArray(), null);

        $loan->delete();

        return redirect()->route('loans.index')
            ->with('success', 'Loan deleted successfully');
    }
}
