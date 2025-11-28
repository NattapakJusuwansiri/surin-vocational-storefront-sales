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
        $year   = $request->year  ?? date('Y');
        $month  = $request->month ?? date('m');
        $search = $request->search ?? '';

        // ช่วงเวลาของเดือนที่เลือก
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // รายการที่ขายทั้งหมดเดือนนั้น
        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        // Summary รายหมวดหมู่ + เพิ่มราคา
        $summary = $this->getSummaryData($year, $month, $search);

        // ยอดขายรวมเดือนนั้น
        $totalSales = $billItems->sum(function ($i) {
            return $i->quantity * $i->price;
        });

        // จำนวนสินค้าที่ขายออกทั้งหมด
        $totalQuantitySold = $billItems->sum('quantity');

        // Top 10 กราฟหมวดหมู่ขายดี
        $topCategories = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(fn($g) => $g->sum('quantity'))
            ->sortDesc()
            ->take(10);

        // Summary
        return view('report.report', compact(
            'summary',
            'month',
            'year',
            'search',
            'totalSales',
            'totalQuantitySold',
            'topCategories'
        ));
    }

    // 🔥 ฟังก์ชันรวม logic Summary เพื่อใช้ได้ทั้งหน้าแสดงผล + export
    private function getSummaryData($year, $month, $search = '')
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $summary = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(function ($group, $category) use ($month, $year, $search) {

                // search หมวดหมู่
                if ($search && stripos($category, $search) === false) {
                    return null;
                }

                $sold = $group->sum('quantity');

                $remain = Stock::where('category', $category)
                    ->sum(DB::raw("quantity_front + quantity_back"));

                // ✔ ยอดขายรวมของหมวดหมู่นี้
                $totalPrice = $group->sum(function ($i) {
                    return $i->quantity * $i->price;
                });

                return [
                    'category' => $category,
                    'sold'     => $sold,
                    'remain'   => $remain,
                    'month'    => "{$year}-{$month}",
                    'price'    => $totalPrice   // ✔ ส่งไป export + table
                ];
            })
            ->filter()
            ->values();

        return $summary;
    }

    public function export(Request $request)
    {
        $year = $request->year ?? date('Y');
        $month = $request->month ?? date('m');
        $search = $request->search;

        // ✔ ใช้ฟังก์ชันเดียวกับหน้าแสดงผล
        $summary = $this->getSummaryData($year, $month, $search);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', 'หมวดหมู่');
        $sheet->setCellValue('B1', 'ขายทั้งหมด (ชิ้น)');
        $sheet->setCellValue('C1', 'คงเหลือ (ชิ้น)');
        $sheet->setCellValue('D1', 'เดือน');
        $sheet->setCellValue('E1', 'ราคา (บาท)');

        // สไตล์หัวตาราง
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // ตัวอักษรสีขาว
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '1E90FF'] // พื้นหลังสีน้ำเงินอ่อน
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ]
        ];

        // Apply style ใหักับหัวตารางทั้งหมด A1 ถึง E1
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

        // ปรับความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $rowNum = 2;

        foreach ($summary as $row) {
            $sheet->setCellValue("A{$rowNum}", $row['category']);
            $sheet->setCellValue("B{$rowNum}", $row['sold']);
            $sheet->setCellValue("C{$rowNum}", $row['remain']);
            $sheet->setCellValue("D{$rowNum}", $row['month']);
            $sheet->setCellValue("E{$rowNum}", $row['price']); // ✔ ราคาแต่ละหมวดหมู่
            $rowNum++;
        }

        $fileName = "summary_{$year}_{$month}.xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

    public function categoryDetail(Request $request, $category)
    {
        $year  = $request->year  ?? date('Y');
        $month = $request->month ?? date('m');

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $billItems = BillItem::with('stock')
            ->whereHas('stock', fn($q) => $q->where('category', $category))
            ->whereBetween('created_at', [$start, $end])
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

        return view('report.detail', compact('detail', 'category', 'year', 'month'));
    }

    public function exportDetail(Request $request, $category)
    {
        $year  = $request->year  ?? date('Y');
        $month = $request->month ?? date('m');

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $billItems = BillItem::with('stock')
            ->whereHas('stock', fn($q) => $q->where('category', $category))
            ->whereBetween('created_at', [$start, $end])
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

        // Header
        $sheet->setCellValue('A1', 'ชื่อสินค้า');
        $sheet->setCellValue('B1', 'จำนวนขายออก (ชิ้น)');
        $sheet->setCellValue('C1', 'จำนวนคงเหลือ (ชิ้น)');
        $sheet->setCellValue('D1', 'ยอดขาย (บาท)');

        $sheet->getStyle('A1:D1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '1E90FF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        foreach (range('A','D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $rowNum = 2;
        foreach ($detail as $row) {
            $sheet->setCellValue("A{$rowNum}", $row['name']);
            $sheet->setCellValue("B{$rowNum}", $row['sold']);
            $sheet->setCellValue("C{$rowNum}", $row['remain']);
            $sheet->setCellValue("D{$rowNum}", $row['totalPrice']);
            $rowNum++;
        }

        $fileName = "detail_{$category}_{$year}_{$month}.xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName);
    }

}
