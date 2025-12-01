<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillItem;
use App\Models\Stock;
use Carbon\Carbon;
use DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    public function categorySummary(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()   : Carbon::now()->endOfDay();
        $search    = $request->search ?? '';

        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $summary = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(function ($group, $category) use ($search) {
                if ($search && stripos($category, $search) === false) return null;

                $lastDate = $group->max('created_at')->format('Y-m-d');

                return [
                    'category' => $category,
                    'sold'     => $group->sum('quantity'),
                    'remain'   => Stock::where('category', $category)
                        ->sum(DB::raw("quantity_front + quantity_back")),
                    'date'     => $lastDate,
                ];
            })
            ->filter()
            ->values();

        $totalSales = $billItems->sum(fn($i) => $i->quantity * $i->price);
        $totalQuantitySold = $billItems->sum('quantity');

        $topCategories = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(fn($g) => $g->sum('quantity'))
            ->sortDesc()
            ->take(10);

        return view('report.report', compact(
            'summary',
            'startDate',
            'endDate',
            'search',
            'totalSales',
            'totalQuantitySold',
            'topCategories'
        ));
    }

    // 🔥 ฟังก์ชันรวม logic Summary เพื่อใช้ได้ทั้งหน้าแสดงผล + export
    private function getSummaryData($year, $month, $search = '', $day)
    {
        if ($day) {
            // ฟิลเตอร์เฉพาะวันที่เดียวแบบถูกต้อง
            $billItems = BillItem::with('stock')
                ->whereDate('created_at', "{$year}-{$month}-{$day}")
                ->get();
        } else {
            // ฟิลเตอร์ทั้งเดือน
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            $billItems = BillItem::with('stock')
                ->whereBetween('created_at', [$start, $end])
                ->get();
        }

        // รวมสรุป
        return $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(function ($group, $category) use ($search) {

                if ($search && stripos($category, $search) === false) {
                    return null;
                }

                $lastDate = $group->max('created_at')->format('Y-m-d');

                return [
                    'category' => $category,
                    'sold'     => $group->sum('quantity'),
                    'remain'   => Stock::where('category', $category)
                        ->sum(DB::raw("quantity_front + quantity_back")),
                    'date'     => $lastDate,
                ];
            })
            ->filter()
            ->values();
    }

    function export(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()   : Carbon::now()->endOfDay();

        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $summary = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(function ($group, $category) {
                return [
                    'category' => $category,
                    'sold'     => $group->sum('quantity'),
                    'remain'   => Stock::where('category', $category)->sum(DB::raw("quantity_front + quantity_back")),
                ];
            })
            ->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1','หมวดหมู่');
        $sheet->setCellValue('B1','จำนวนขายออก');
        $sheet->setCellValue('C1','จำนวนคงเหลือ');

        $rowNum = 2;
        foreach($summary as $row){
            $sheet->setCellValue("A{$rowNum}", $row['category']);
            $sheet->setCellValue("B{$rowNum}", $row['sold']);
            $sheet->setCellValue("C{$rowNum}", $row['remain']);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "summary_{$startDate->format('Ymd')}_to_{$endDate->format('Ymd')}.xlsx";

        return response()->streamDownload(fn() => $writer->save('php://output'), $fileName);
    }



    public function categoryDetail(Request $request, $category)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()   : Carbon::now()->endOfDay();

        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('stock', fn($q) => $q->where('category', $category))
            ->get();

        $detail = $billItems
            ->groupBy(fn($i) => $i->stock->name)
            ->map(fn($group, $productName) => [
                'name'       => $productName,
                'sold'       => $group->sum('quantity'),
                'remain'     => $group->first()->stock->quantity_front + $group->first()->stock->quantity_back,
                'totalPrice' => $group->sum(fn($i) => $i->quantity * $i->price),
            ])
            ->values();

        return view('report.detail', compact('detail', 'category', 'startDate', 'endDate'));
    }

    public function exportDetail(Request $request, $category)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()   : Carbon::now()->endOfDay();

        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereHas('stock', fn($q) => $q->where('category', $category))
            ->get();

        $detail = $billItems
            ->groupBy(fn($i) => $i->stock->name)
            ->map(fn($group, $productName) => [
                'name'       => $productName,
                'sold'       => $group->sum('quantity'),
                'remain'     => $group->first()->stock->quantity_front + $group->first()->stock->quantity_back,
                'totalPrice' => $group->sum(fn($i) => $i->quantity * $i->price),
            ])
            ->values();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1','ชื่อสินค้า');
        $sheet->setCellValue('B1','จำนวนขายออก');
        $sheet->setCellValue('C1','จำนวนคงเหลือ');
        $sheet->setCellValue('D1','ยอดขาย');

        $rowNum = 2;
        foreach($detail as $row){
            $sheet->setCellValue("A{$rowNum}", $row['name']);
            $sheet->setCellValue("B{$rowNum}", $row['sold']);
            $sheet->setCellValue("C{$rowNum}", $row['remain']);
            $sheet->setCellValue("D{$rowNum}", $row['totalPrice']);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "detail_{$category}_{$startDate->format('Ymd')}_to_{$endDate->format('Ymd')}.xlsx";

        return response()->streamDownload(fn() => $writer->save('php://output'), $fileName);
    }

}
