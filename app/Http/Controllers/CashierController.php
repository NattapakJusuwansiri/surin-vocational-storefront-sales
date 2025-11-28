<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Bill;
use App\Models\BillItem;

class CashierController extends Controller
{
    public function index()
    {
        $stocks = Stock::orderByRaw('(quantity_front + quantity_back) ASC')->get();
        return view('cashier', compact('stocks'));
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $items = $data['items'] ?? [];
        $paidAmount = $data['paid_amount'] ?? 0;

        if(!$items || count($items) === 0){
            return response()->json(['error'=>'ไม่มีสินค้าในบิล']);
        }

        $total = 0;
        foreach ($items as $item) {
            $stock = Stock::find($item['stock_id']);
            if(!$stock) continue;
            if($stock->quantity_back < $item['quantity']){
                return response()->json(['error'=>"จำนวนสินค้า '{$stock->name}' ไม่เพียงพอ"]);
            }
            $stock->quantity_back -= $item['quantity'];
            $stock->save();
            $total += $item['quantity'] * ($item['price'] ?? 0);
        }

        if($paidAmount < $total){
            return response()->json(['error'=>'จำนวนเงินที่จ่ายไม่เพียงพอ']);
        }

        $change = $paidAmount - $total;

        // สร้างบิลพร้อมบันทึก paid_amount และ change_amount
        $bill = Bill::create([
            'total' => $total,
            'paid_amount' => $paidAmount,
            'change_amount' => $change
        ]);

        foreach ($items as $item){
            BillItem::create([
                'bill_id' => $bill->id,
                'stock_id' => $item['stock_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'] ?? 0,
            ]);
        }

        return response()->json(['success'=>"บันทึกเรียบร้อย เงินทอน $change บาท"]);
    }

}
