<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TaxReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->type ?? 'output'; // output | input | summary

        $start = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $end = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // ===== ภาษีขาย =====
        $output = Document::where('document_type', 'tax')
            ->whereBetween('document_date', [$start, $end])
            ->get();

        // ===== ภาษีซื้อ =====
        $input = Document::where('document_type', 'delivery')
            ->whereBetween('document_date', [$start, $end])
            ->get();

        // ===== สรุป =====
        $summary = [
            'output_subtotal' => $output->sum('subtotal'),
            'output_vat'      => $output->sum('vat_amount'),
            'output_total'    => $output->sum('total'),

            'input_subtotal'  => $input->sum('subtotal'),
            'input_vat'       => $input->sum('vat_amount'),
            'input_total'     => $input->sum('total'),

            'net_vat'         => $output->sum('vat_amount') - $input->sum('vat_amount'),
        ];

        return view('tax-report.tax-report', compact(
            'type',
            'output',
            'input',
            'summary'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->type ?? 'output';

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end   = Carbon::parse($request->end_date)->endOfDay();

        $docs = Document::where('document_type',
            $type === 'input' ? 'purchase_tax' : 'tax'
        )->whereBetween('document_date', [$start, $end])->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'วันที่', 'เลขที่เอกสาร', 'คู่ค้า', 'มูลค่า', 'VAT', 'รวม'
        ], null, 'A1');

        $row = 2;
        foreach ($docs as $d) {
            $sheet->fromArray([
                Carbon::parse($d->document_date)->format('d/m/Y'),
                $d->document_no,
                $d->buyer_name,
                $d->subtotal,
                $d->vat_amount,
                $d->total,
            ], null, "A{$row}");
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $file = storage_path('app/public/tax_report.xlsx');
        $writer->save($file);

        return response()->download($file)->deleteFileAfterSend(true);
    }
}
