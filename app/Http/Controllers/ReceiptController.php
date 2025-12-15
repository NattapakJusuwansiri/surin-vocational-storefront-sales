<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BillItem;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReceiptController extends Controller
{
    // แสดงหน้า Bill ทั้งหมด
    public function index(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $search    = $request->search ?? '';

        $query = BillItem::with('stock');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

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

        return view('bill.bill', compact('data', 'startDate', 'endDate', 'search'));
    }

    public function detail(Request $request, $bill_id)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $query = BillItem::with('stock')->where('bill_id', $bill_id);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $items = $query->get();
        $totalPrice = $items->sum(fn($i) => $i->quantity * $i->price);

        $paid = $items->first()->bill->paid ?? $totalPrice;
        $change = $paid - $totalPrice;

        return view('bill.detail', compact('items','totalPrice','paid','change','startDate','endDate'));
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $search    = $request->search ?? '';

        $query = BillItem::with('stock');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

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

        $fileName = "receipts_".($startDate ?? 'all')."_to_".($endDate ?? 'all').".xlsx";
        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), $fileName);
    }

    // Export Excel ของบิลแต่ละใบ
    public function exportDetail(Request $request, $bill_id)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $query = BillItem::with('stock')->where('bill_id',$bill_id);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $items = $query->get();
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

        $fileName = "receipt_detail_{$bill_id}_".($startDate ?? 'all')."_to_".($endDate ?? 'all').".xlsx";

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), $fileName);
    }

    public function exportPdf(Request $request, $bill_id)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $query = BillItem::with('stock')->where('bill_id', $bill_id);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        // รวมราคา
        $totalPrice = $items->sum(fn($i) => $i->quantity * $i->price);

        // ใช้ logic เดียวกับหน้า detail
        $paid   = $items->first()->bill->paid ?? $totalPrice;
        $change = $paid - $totalPrice;

        $pdf = Pdf::loadView('pdf.bill', [
            'items'      => $items,
            'totalPrice'=> $totalPrice,
            'paid'      => $paid,
            'change'    => $change,
            'bill_id'   => $bill_id,
        ])->setPaper('A4');

        return $pdf->stream("bill_{$bill_id}.pdf");
    }


    public function exportTax(Request $request, $bill_id)
    {
        $items = BillItem::with(['stock','bill'])
            ->where('bill_id', $bill_id)
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $totalPrice = $items->sum(fn($i) => $i->quantity * $i->price);
        $paid = $items->first()->bill->paid ?? $totalPrice;
        $change = $paid - $totalPrice;

        $pdf = Pdf::loadView('pdf.tax', [
            'items' => $items,
            'totalPrice' => $totalPrice,
            'paid' => $paid,
            'change' => $change,
            'bill_id' => $bill_id,
        ])->setPaper('A4');

        return $pdf->stream("tax_invoice_{$bill_id}.pdf");
    }

}
