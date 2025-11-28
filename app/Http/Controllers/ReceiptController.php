<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillItem;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReceiptController extends Controller
{
    // แสดงหน้า Bill ทั้งหมด
    public function index(Request $request)
    {
        $year   = $request->year ?? date('Y');
        $month  = $request->month ?? date('m');
        $search = $request->search ?? '';

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $query = BillItem::with('stock')->whereBetween('created_at', [$start, $end]);

        if ($search) {
            $query->whereHas('stock', fn($q) => $q->where('name','like',"%{$search}%"));
        }

        $billItems = $query->orderBy('created_at','desc')->get();

        $data = $billItems->groupBy('bill_id')->map(function($items) {
            return [
                'bill_id' => $items->first()->bill_id,
                'total_quantity' => $items->sum('quantity'),
                'total_price' => $items->sum(fn($i) => $i->quantity * $i->price),
                'datetime' => $items->first()->created_at->format('Y-m-d H:i:s'),
            ];
        })->values();

        return view('bill.bill', compact('data','year','month','search'));
    }

    // ดูรายละเอียดบิลแต่ละใบ
    public function detail($bill_id)
    {
        $items = BillItem::with('stock')->where('bill_id', $bill_id)->get();

        $totalPrice = $items->sum(fn($i) => $i->quantity * $i->price);

        // สมมติมีการรับเงินจากลูกค้า
        $paid = $items->first()->bill->paid ?? $totalPrice;
        $change = $paid - $totalPrice;

        return view('bill.detail', compact('items','totalPrice','paid','change'));
    }

    // Export Excel ของบิลรวม
    public function export(Request $request)
    {
        $year   = $request->year ?? date('Y');
        $month  = $request->month ?? date('m');
        $search = $request->search ?? '';

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $query = BillItem::with('stock')->whereBetween('created_at', [$start, $end]);

        if ($search) {
            $query->whereHas('stock', fn($q) => $q->where('name','like',"%{$search}%"));
        }

        $billItems = $query->orderBy('created_at','desc')->get();

        $data = $billItems->groupBy('bill_id')->map(function($items, $index) {
            return [
                'no' => $index+1,
                'bill_id' => $items->first()->bill_id,
                'total_quantity' => $items->sum('quantity'),
                'total_price' => $items->sum(fn($i) => $i->quantity * $i->price),
                'datetime' => $items->first()->created_at->format('Y-m-d H:i:s'),
            ];
        })->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1','ลำดับ');
        $sheet->setCellValue('B1','จำนวนสินค้า');
        $sheet->setCellValue('C1','ราคาที่จ่าย');
        $sheet->setCellValue('D1','วันที่และเวลา');

        $rowNum = 2;
        foreach($data as $row){
            $sheet->setCellValue("A{$rowNum}", $row['no']);
            $sheet->setCellValue("B{$rowNum}", $row['total_quantity']);
            $sheet->setCellValue("C{$rowNum}", $row['total_price']);
            $sheet->setCellValue("D{$rowNum}", $row['datetime']);
            $rowNum++;
        }

        $fileName = "receipts_{$year}_{$month}.xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(fn() => $writer->save('php://output'), $fileName);
    }

    // Export Excel ของบิลแต่ละใบ
    public function exportDetail($bill_id)
    {
        $items = BillItem::with('stock')->where('bill_id',$bill_id)->get();

        $totalPrice = $items->sum(fn($i) => $i->quantity * $i->price);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1','ลำดับ');
        $sheet->setCellValue('B1','ชื่อสินค้า');
        $sheet->setCellValue('C1','จำนวนสินค้า');
        $sheet->setCellValue('D1','ราคา');

        $rowNum = 2;
        foreach($items as $item){
            $sheet->setCellValue("A{$rowNum}", $rowNum-1);
            $sheet->setCellValue("B{$rowNum}", $item->stock->name);
            $sheet->setCellValue("C{$rowNum}", $item->quantity);
            $sheet->setCellValue("D{$rowNum}", $item->quantity * $item->price);
            $rowNum++;
        }

        // สรุปท้าย
        $sheet->setCellValue("C{$rowNum}", "รวมทั้งหมด");
        $sheet->setCellValue("D{$rowNum}", $totalPrice);

        $fileName = "receipt_detail_{$bill_id}.xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(fn() => $writer->save('php://output'), $fileName);
    }
}
