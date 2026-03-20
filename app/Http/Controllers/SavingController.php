<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\Member;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SavingController extends Controller
{
    public function index(Request $request)
    {
        $query = Saving::with('member');

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        $savings = $query->orderBy('transaction_date', 'desc')->paginate(20);

        return view('savings.index', compact('savings'));
    }

    public function create()
    {
        $members = Member::where('status', 'active')->get();
        return view('savings.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'type' => 'required|in:pokok,wajib,sukarela',
            'amount' => 'required|numeric|min:0',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $saving = Saving::create($validated);

        AuditLog::log('create', 'Saving', $saving->id, 'Created saving transaction', null, $validated);

        return redirect()->route('savings.index')
            ->with('success', 'Saving transaction created successfully');
    }

    public function show(Saving $saving)
    {
        $saving->load('member');

        return view('savings.show', compact('saving'));
    }

    public function destroy(Saving $saving)
    {
        AuditLog::log('delete', 'Saving', $saving->id, 'Deleted saving transaction', $saving->toArray(), null);

        $saving->delete();

        return redirect()->route('savings.index')
            ->with('success', 'Saving transaction deleted successfully');
    }
}
