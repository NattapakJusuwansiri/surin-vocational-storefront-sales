<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Models\MemberCreditLog;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    // ค้นหาสมาชิกด้วยรหัส
    public function find($code)
    {
        $member = Member::where('member_code', $code)->first();

        if (!$member) {
            return response()->json([
                'error' => 'ไม่พบสมาชิก'
            ], 404);
        }

        return response()->json([
            'id'     => $member->id,
            'name'   => $member->name,
            'credit' => $member->credit_balance
        ]);
    }

    public function quickCreate(Request $request)
    {
        $request->validate([
            'member_code' => 'required|unique:members,member_code',
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:student,teacher'
        ]);

        $member = Member::create([
            'member_code' => $request->member_code,
            'name'        => $request->name,
            'type'        => $request->type,
            'credit_balance' => 0,
            'points' => 0
        ]);

        return response()->json([
            'success' => true,
            'member' => $member
        ]);
    }

     public function debtorIndex(Request $request)
    {
        $search = $request->search;

        $members = Member::where('credit_balance', '>', 0)
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%");
            })
            ->orderBy('credit_balance', 'desc')
            ->get();

        return view('debtor.debtor', compact('members'));
    }

    // ชำระหนี้
    public function payDebt(Request $request, Member $member)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        DB::transaction(function () use ($request, $member) {

            // หักเครดิต
            $member->credit_balance -= $request->amount;
            if ($member->credit_balance < 0) {
                $member->credit_balance = 0;
            }
            $member->save();

            // log การชำระ
            MemberCreditLog::create([
                'member_id' => $member->id,
                'amount'    => $request->amount,
                'type'      => 'pay',
                'remark'    => 'ชำระหนี้'
            ]);
        });

        return back()->with('success', 'ชำระหนี้เรียบร้อย');
    }
}
