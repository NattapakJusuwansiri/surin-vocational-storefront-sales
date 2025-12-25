<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Member;
use App\Models\MemberCreditLog;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        $stocks = Stock::orderByRaw('(quantity_front + quantity_back) ASC')->get();
        return view('cashier', compact('stocks'));
    }

    public function add(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = json_decode($request->getContent(), true);

            $items       = $data['items'] ?? [];
            $paidAmount  = $data['paid_amount'] ?? 0;
            $paymentType = $data['payment_type'] ?? 'cash';
            $memberCode  = $data['member_code'] ?? null;

            if (count($items) === 0) {
                return response()->json(['error' => 'ไม่มีสินค้าในบิล']);
            }

            // 🔎 หา Member (ถ้ามีกรอก)
            $member = null;

            if ($memberCode) {
                $member = Member::where('member_code', $memberCode)->first();

                if (!$member) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'member_not_found',
                        'member_code' => $memberCode
                    ]);
                }
            }

            if ($paymentType === 'credit' && !$member) {
                DB::rollBack();
                return response()->json(['error' => 'ขายเชื่อต้องกรอกรหัสสมาชิก']);
            }

            $total = 0;

            foreach ($items as $item) {
                $stock = Stock::find($item['stock_id']);
                if (!$stock) continue;

                if ($stock->quantity_front < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "สินค้า {$stock->name} ไม่เพียงพอ"
                    ]);
                }

                $stock->quantity_front -= $item['quantity'];
                $stock->save();

                $total += $item['quantity'] * $item['price'];
            }

            if ($paymentType === 'cash' && $paidAmount < $total) {
                DB::rollBack();
                return response()->json(['error' => 'จำนวนเงินที่จ่ายไม่เพียงพอ']);
            }

            $change = $paymentType === 'cash'
                ? $paidAmount - $total
                : 0;

            // 🧾 สร้างบิล
            $bill = Bill::create([
                'total'         => $total,
                'paid_amount'   => $paidAmount,
                'change_amount' => $change,
                'payment_type'  => $paymentType,
                'member_id'   => $member->id
            ]);

            foreach ($items as $item) {
                BillItem::create([
                    'bill_id'  => $bill->id,
                    'stock_id' => $item['stock_id'],
                    'quantity' => $item['quantity'],
                    'price'    => $item['price'],
                ]);
            }

            // 🎯 คำนวณแต้ม (100 บาท = 1 แต้ม)
            $points = floor($total / 100);

            if ($member) {
                $member->points += $points;

                if ($paymentType === 'credit') {
                    $member->credit_balance += $total;

                    MemberCreditLog::create([
                        'member_id' => $member->id,
                        'bill_id'   => $bill->id,
                        'amount'    => $total,
                        'type'      => 'add',
                        'remark'    => 'ขายเชื่อ'
                    ]);
                }

                $member->save();
            }

            DB::commit();

            return response()->json([
                'success' => 'บันทึกบิลเรียบร้อย',
                'bill_id' => $bill->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'เกิดข้อผิดพลาด',
                'msg'   => $e->getMessage()
            ]);
        }
    }
}
