<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::query();

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('document_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('document_no', 'like', "%{$request->search}%")
                ->orWhere('buyer_name', 'like', "%{$request->search}%");
            });
        }

        // 🔥 perPage
        $perPage = (int) $request->get('perPage', 10);

        if ($perPage === -1) {
            // แสดงทั้งหมด (ไม่ paginate)
            $documents = $query->orderBy('document_date', 'desc')->get();
        } else {
            $documents = $query
                ->orderBy('document_date', 'desc')
                ->paginate($perPage)
                ->withQueryString(); // ✅ คงค่า filter/search
        }

        return view('document.document', compact('documents', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:tax,tax_invoice,invoice,quotation,delivery',
            'document_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {

            $documentNo = $this->generateDocumentNo(
                $request->document_type,
                $request->document_date
            );

            $document = Document::create([
                'document_type' => $request->document_type,
                'document_no'   => $documentNo,
                'document_date' => $request->document_date,

                // ผู้ขาย
                'seller_name'    => $request->seller_name ?? 'ไม่ระบุชื่อร้าน',
                'seller_tax_id'  => $request->seller_tax_id,
                'seller_address' => $request->seller_address,

                // ผู้ซื้อ
                'buyer_name'     => $request->buyer_name,
                'buyer_tax_id'   => $request->buyer_tax_id,
                'buyer_address'  => $request->buyer_address,

                'total_amount'   => 0,
                'vat_amount'     => 0,
            ]);

            // รายการสินค้า
            $total = 0;

            if (is_array($request->items)) {
                foreach ($request->items as $item) {
                    $qty   = (int) ($item['qty'] ?? 0);
                    $price = (float) ($item['price'] ?? 0);
                    $lineTotal = $qty * $price;

                    $document->items()->create([
                        'item_name' => $item['name'],
                        'quantity'  => $qty,
                        'price'     => $price,
                        'total'     => $lineTotal,
                    ]);

                    $total += $lineTotal;
                }
            }

            // VAT เฉพาะใบกำกับภาษี
            $vat = 0;
            if (in_array($request->document_type, ['tax', 'tax_invoice'])) {
                $vat = round($total * 7 / 107, 2);
            }

            $document->update([
                'total_amount' => $total,
                'vat_amount'   => $vat,
            ]);

            DB::commit();

            return redirect()->route('documents.index')
                ->with('success', 'ออกเอกสารเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage()); // 🔥 debug ชัด
        }
    }

    private function generateDocumentNo(string $type, string $dateInput): string
    {
        $prefixMap = [
            'tax'         => 'TI',
            'tax_invoice' => 'TI',
            'invoice'     => 'IV',
            'quotation'   => 'QT',
            'delivery'    => 'DT',
        ];

        $prefix = $prefixMap[$type] ?? 'DOC';
        $date   = Carbon::parse($dateInput)->format('Ymd');

        $lastDoc = Document::where('document_type', $type)
            ->whereDate('document_date', $dateInput)
            ->orderBy('document_no', 'desc')
            ->lockForUpdate()
            ->first();

        $running = $lastDoc
            ? intval(substr($lastDoc->document_no, -4)) + 1
            : 1;

        return $prefix . $date . str_pad($running, 4, '0', STR_PAD_LEFT);
    }

    public function show($id)
    {
        $document = Document::with('items')->findOrFail($id);

        return view('document.detail', compact('document'));
    }

    public function pdf($id)
    {
        $document = Document::with('items')->findOrFail($id);
        $items = $document->items;

        // คำนวณรวมและ VAT
        $totalPrice = $items->sum(function($item){
            return $item->quantity * $item->price;
        });

        // เลือก Blade ตามประเภทเอกสาร
        $viewMap = [
            'delivery'     => 'pdf.document.delivery',
            'tax'          => 'pdf.document.tax',
            'tax_invoice'  => 'pdf.document.tax',
            'invoice'      => 'pdf.document.invoice',
            'quotation'    => 'pdf.document.quotation',
        ];

        $view = $viewMap[$document->document_type] ?? 'pdf.document.default';

        return Pdf::loadView($view, compact('document', 'items', 'totalPrice'))
                ->stream("{$document->document_no}.pdf");
    }

}
