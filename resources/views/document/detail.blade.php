@extends('layout.layout')
@section('title', 'รายละเอียดเอกสาร')

@section('desktop-content')
    @php
        $docTypes = [
            'delivery' => 'ใบส่งของ',
            'tax' => 'ใบกำกับภาษี',
            'tax_invoice' => 'ใบกำกับภาษี',
            'invoice' => 'ใบแจ้งหนี้',
            'quotation' => 'ใบเสนอราคา',
        ];

        $documentLabel = $docTypes[$document->document_type] ?? $document->document_type;
    @endphp

    @php
        // ตรวจสอบว่ามีข้อมูลผู้ขายหรือไม่
        $hasSeller = false;

        if (
            (!empty($document->seller_name) && $document->seller_name !== 'ไม่ระบุชื่อร้าน') ||
            !empty($document->seller_tax_id) ||
            !empty($document->seller_address) ||
            !empty($document->seller_branch)
        ) {
            $hasSeller = true;
        }
    @endphp

    <div class="container p-5 bg-white rounded-5">

        <h4 class="mb-3">
            {{ $document->document_no }}
            <span class="badge bg-primary ms-2">
                {{ $documentLabel }}
            </span>
        </h4>

        <p>
            วันที่ออกเอกสาร :
            {{ \Carbon\Carbon::parse($document->document_date)->format('d/m/Y') }}
        </p>

        @if ($hasSeller)
            <hr>

            <h6>ข้อมูลผู้ขาย</h6>
            <p>
                ชื่อ : {{ $document->seller_name ?? '-' }} <br>

                @if (!empty($document->seller_tax_id))
                    @if($document->document_type == 'quotation')
                    โทรศัพท์ : {{ $document->seller_tax_id }} <br>
                    @else
                    เลขผู้เสียภาษี : {{ $document->seller_tax_id }} <br>
                    @endif
                @endif

                @if (!empty($document->seller_branch))
                    สาขาที่ : {{ $document->seller_branch }} <br>
                @endif

                @if (!empty($document->seller_address))
                    ที่อยู่ : {{ $document->seller_address }}
                @endif
            </p>
        @endif

        <hr>

        @if ($document->document_type == 'delivery')
            <h6>ข้อมูลผู้รับสินค้า</h6>
            <p>
                ชื่อ : {{ $document->buyer_name ?? '-' }} <br>
                วันที่ส่งสินค้า : {{ $document->buyer_tax_id ?? '-' }} <br>
                ที่อยู่ : {{ $document->buyer_address ?? '-' }}
            </p>
        @elseif($document->document_type == 'tax' || $document->document_type == 'tax_invoice')
            <h6>ข้อมูลผู้ซื้อ</h6>
            <p>
                ชื่อ : {{ $document->buyer_name ?? '-' }} <br>
                เลขผู้เสียภาษี : {{ $document->buyer_tax_id ?? '-' }} <br>
                ที่อยู่ : {{ $document->buyer_address ?? '-' }}
            </p>
        @elseif($document->document_type == 'quotation')
            <h6>ข้อมูลผู้ซื้อ</h6>
            <p>
                ชื่อ : {{ $document->buyer_name ?? '-' }} <br>
                โทรศัพท์ : {{ $document->buyer_tax_id ?? '-' }} <br>
                ที่อยู่ : {{ $document->buyer_address ?? '-' }}
            </p>
        @elseif($document->document_type == 'invoice')
            <h6>ข้อมูลผู้ซื้อ</h6>
            <p>
                ชื่อ : {{ $document->buyer_name ?? '-' }} <br>
                กำหนดชำระ : {{ $document->buyer_tax_id ?? '-' }} <br>
                ที่อยู่ : {{ $document->buyer_address ?? '-' }}
            </p>
        @endif

        <hr>

        <h6>รายการสินค้า</h6>

        <table class="table table-bordered">
            <thead class="table-light text-center">
                <tr>
                    <th>#</th>
                    <th>สินค้า</th>
                    <th width="120">จำนวน</th>
                    <th width="150">ราคา</th>
                    <th width="150">รวม</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($document->items as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td class="text-end">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->price, 2) }}</td>
                        <td class="text-end">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-end fw-bold mt-3">
            รวมทั้งสิ้น : {{ number_format($document->total_amount, 2) }} บาท
        </div>

        @if ($document->vat_amount > 0)
            <div class="text-end">
                ภาษีมูลค่าเพิ่ม (7%) :
                {{ number_format($document->vat_amount, 2) }} บาท
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">
                ← กลับหน้ารายการ
            </a>
        </div>

    </div>

@endsection
