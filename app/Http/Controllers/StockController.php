<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function showStock(Request $request)
    {
        $query = Stock::query();

        // ค้นหาชื่อสินค้า / หมวดหมู่
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        // เรียงตามผลรวมของ quantity_front + quantity_back จากน้อยไปมาก
        $query->orderByRaw('(quantity_front + quantity_back) ASC');

        // กำหนดจำนวนต่อหน้า
        $perPage = $request->perPage ?? 10;
        if ($perPage == -1) $perPage = $query->count();

        $stocks = $query->paginate($perPage);

        return view('show-stock', compact('stocks'));
    }

    // แสดงฟอร์ม Bootstep
    public function edit($id)
    {
        $stock = Stock::findOrFail($id);
        return view('stock.edit', compact('stock'));
    }

    // อัปเดต movement
    public function updateMovement(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'from_location' => 'required|in:front,back',
            'to_location' => 'required|in:front,back|different:from_location',
        ]);

        $stock = Stock::findOrFail($id);

        $quantity = $request->quantity;
        $from = $request->from_location;
        $to = $request->to_location;

        // ตรวจสอบจำนวนที่มี
        if ($from == 'front' && $stock->quantity_front < $quantity) {
            return back()->withErrors(['quantity' => 'จำนวนหน้าร้านไม่พอ']);
        }
        if ($from == 'back' && $stock->quantity_back < $quantity) {
            return back()->withErrors(['quantity' => 'จำนวนหลังร้านไม่พอ']);
        }

        // อัปเดตจำนวน
        if ($from == 'front') {
            $stock->quantity_front -= $quantity;
        } else {
            $stock->quantity_back -= $quantity;
        }

        if ($to == 'front') {
            $stock->quantity_front += $quantity;
        } else {
            $stock->quantity_back += $quantity;
        }

        $stock->save();

        // บันทึก movement
        StockMovement::create([
            'stock_id' => $stock->id,
            'quantity' => $quantity,
            'from_location' => $from,
            'to_location' => $to,
        ]);

        return redirect()->route('show-stock')->with('success', 'อัปเดตจำนวนสินค้าเรียบร้อย');
    }

    public function addStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_type' => 'required|in:unit,pack,box,dozen',
        ]);

        $stock = Stock::findOrFail($id);
        $quantity = $request->quantity;
        $priceUnite = $request->priceUnite;
        // คำนวณจำนวนชิ้นจริง
        switch ($request->unit_type) {
            case 'pack':
                $quantity *= 12;
                break;
            case 'box':
                $quantity *= 24;
                break;
            case 'dozen':
                $quantity *= 12;
                break;
            case 'unit':
            default:
                break;
        }

        // เพิ่มไปหลังร้านเสมอ
        $stock->quantity_back += $quantity;
        $stock->save();

        // บันทึก movement
        StockMovement::create([
            'stock_id' => $stock->id,
            'quantity' => $quantity,
            'from_location' => null,
            'to_location' => 'back',
            'unit_type' => $request->unit_type,
        ]);

        return redirect()->route('show-stock')->with('success', 'เติมสินค้าสำเร็จ! เพิ่มไปหลังร้านเรียบร้อย');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:stocks,name',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'product_code' => 'required|string|max:255|unique:stocks,product_code',
            'barcode_unit' => 'required|string|unique:stocks,barcode_unit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        Stock::create([
            'name' => $request->name,
            'category' => $request->category,
            'quantity_front' => 0,
            'quantity_back' => $request->quantity,
            'product_code' => $request->product_code,
            'barcode_unit' => $request->barcode_unit,
            'barcode_pack' => $request->barcode_pack ?? null,
            'barcode_box' => $request->barcode_box  ?? null,
            'price' => $request->priceUnite,
        ]);

        return response()->json([
            'success' => true
        ]);
    }


}
