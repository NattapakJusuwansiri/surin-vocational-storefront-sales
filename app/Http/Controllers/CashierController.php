<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Bill;
use App\Models\BillItem;
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
            $items = $data['items'] ?? [];
            $paidAmount = $data['paid_amount'] ?? 0;

            if (!$items || count($items) === 0) {
                return response()->json(['error' => 'ไม่มีสินค้าในบิล']);
            }

            $total = 0;

            foreach ($items as $item) {
                $stock = Stock::find($item['stock_id']);
                if (!$stock) continue;

                if ($stock->quantity_front < $item['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'error' => "จำนวนสินค้า '{$stock->name}' ไม่เพียงพอ"
                    ]);
                }

                $stock->quantity_front -= $item['quantity'];
                $stock->save();

                $total += $item['quantity'] * ($item['price'] ?? 0);
            }

            if ($paidAmount < $total) {
                DB::rollBack();
                return response()->json(['error' => 'จำนวนเงินที่จ่ายไม่เพียงพอ']);
            }

            $change = $paidAmount - $total;

            // สร้างบิล
            $bill = Bill::create([
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $change
            ]);

            foreach ($items as $item) {
                BillItem::create([
                    'bill_id' => $bill->id,
                    'stock_id' => $item['stock_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? 0,
                ]);
            }

            DB::commit();

            // ⭐ ส่ง bill_id กลับไป
            return response()->json([
                'success' => "บันทึกเรียบร้อย เงินทอน {$change} บาท",
                'bill_id' => $bill->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'เกิดข้อผิดพลาดในการบันทึกบิล']);
        }
    }
}
