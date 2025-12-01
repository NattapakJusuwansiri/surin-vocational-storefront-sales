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
        $day    = $request->day   ?? null;
        $search = $request->search ?? '';

        if ($day) {
            $fullDate = Carbon::createFromDate($year, $month, $day);
            $billItems = BillItem::with('stock')
            ->whereDate('created_at', "{$year}-{$month}-{$day}")
            ->get();
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$start, $end])
            ->get();
        }

        $summary = $this->getSummaryData($year, $month, $search, $day);

        $totalSales = $billItems->sum(fn($i) => $i->quantity * $i->price);
        $totalQuantitySold = $billItems->sum('quantity');

        $topCategories = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(fn($g) => $g->sum('quantity'))
            ->sortDesc()
            ->take(10);

        return view('report.report', compact(
            'summary',
            'month',
            'year',
            'day',
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

    private function export($year, $month, $search = '', $day = null)
    {
        if ($day) {
            // ใช้วันที่จาก created_at โดยไม่ตัดเดือน/ปี
            $date = Carbon::createFromDate($year, $month, $day);
            $start = $date->startOfDay();
            $end   = $date->endOfDay();
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        }

        $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $summary = $billItems
            ->groupBy(fn($i) => $i->stock->category)
            ->map(function ($group, $category) use ($year, $month, $day, $search) {

                if ($search && stripos($category, $search) === false) {
                    return null;
                }

                return [
                    'category' => $category,
                    'sold'     => $group->sum('quantity'),
                    'remain'   => Stock::where('category', $category)
                        ->sum(DB::raw("quantity_front + quantity_back")),
                    'date'     => $day ? "$year-$month-$day" : "$year-$month",
                ];
            })
            ->filter()
            ->values();

        return $summary;
    }


    public function categoryDetail(Request $request, $category)
    {
        $year  = $request->year  ?? date('Y');
        $month = $request->month ?? date('m');
        $day   = $request->day   ?? null; // ✨ เพิ่มตรงนี้

        if ($day) {
            $fullDate = Carbon::createFromDate($year, $month, $day);
            $billItems = BillItem::with('stock')
            ->whereDate('created_at', "{$year}-{$month}-{$day}")
            ->whereHas('stock', fn($q) => $q->where('category', $category)) // ✅ ใช้ whereHas
            ->get();
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('stock', fn($q) => $q->where('category', $category)) // ✅ ใช้ whereHas
            ->get();
        }

        $detail = $billItems
            ->groupBy(fn($i) => $i->stock->name)
            ->map(fn($group, $productName) => [
                'name'       => $productName,
                'sold'       => $group->sum('quantity'),
                'remain'     => $group->first()->stock->quantity_front + $group->first()->stock->quantity_back,
                'totalPrice' => $group->sum(fn($i) => $i->quantity * $i->price),
            ])
            ->values();

        return view('report.detail', compact('detail', 'category', 'year', 'month', 'day')); // ✨ ส่ง $day ด้วย
    }


    public function exportDetail(Request $request, $category)
    {
        $year  = $request->year  ?? date('Y');
        $month = $request->month ?? date('m');
        $day   = $request->day   ?? null; // ✨ เพิ่มตรงนี้

        if ($day) {
            $fullDate = Carbon::createFromDate($year, $month, $day);
            $billItems = BillItem::with('stock')
            ->whereDate('created_at', "{$year}-{$month}-{$day}")
            ->whereHas('stock', fn($q) => $q->where('category', $category)) // ✅ ใช้ whereHas
            ->get();
        } else {
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $billItems = BillItem::with('stock')
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('stock', fn($q) => $q->where('category', $category)) // ✅ ใช้ whereHas
            ->get();
        }

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
