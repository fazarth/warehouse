<?php

namespace App\Http\Controllers;

use App\Models\LoanInstallment;
use App\Models\Loan;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Request $request)
    {
        $query = LoanInstallment::with(['member', 'loan']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        if ($request->has('overdue')) {
            $query->where('status', '!=', 'paid')
                  ->where('due_date', '<', now());
        }

        $installments = $query->orderBy('due_date', 'desc')->paginate(20);

        return view('installments.index', compact('installments'));
    }

    public function pay(Request $request, LoanInstallment $installment)
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0|max:' . $installment->total_amount,
        ]);

        $amount = $request->input('amount') ?? $installment->total_amount;

        $installment->markAsPaid($amount);

        AuditLog::log('pay', 'LoanInstallment', $installment->id, 'Paid installment', null, [
            'amount' => $amount,
            'status' => $installment->status,
        ]);

        // Check if all installments are paid, mark loan as completed
        $loan = $installment->loan;
        $remainingInstallments = $loan->installments()
            ->where('status', '!=', 'paid')
            ->count();

        if ($remainingInstallments === 0) {
            $loan->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            AuditLog::log('complete', 'Loan', $loan->id, 'Loan completed - all installments paid');
        }

        return back()->with('success', 'Payment recorded successfully');
    }

    public function markOverdue()
    {
        $overdueInstallments = LoanInstallment::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdueInstallments as $installment) {
            $installment->update(['status' => 'overdue']);
        }

        return back()->with('success', $overdueInstallments->count() . ' installments marked as overdue');
    }
}
