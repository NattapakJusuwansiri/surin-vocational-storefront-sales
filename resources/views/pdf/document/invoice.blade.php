<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบกำกับภาษี</title>

    <style>
        @font-face {
            font-family: 'THSarabunNew';
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        body {
            font-family: 'THSarabunNew', sans-serif;
            font-size: 20px;
        }

        .invoice {
            width: 100%;
            max-width: 750px;
            margin: auto;
            padding: 20px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
        }

        .line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 150px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .no-border td {
            border: none !important;
            padding: 4px;
        }

        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
        }

        tbody td {
            border-top: none;
            border-bottom: none;
        }

        tbody tr:last-child td {
            border-bottom: 1px solid #000;
        }
    </style>
</head>

<body>
    <div class="invoice">

        {{-- HEADER --}}
        <div class="top">
            <div class="title">
                ใบเสร็จรับเงิน / ใบกำกับภาษี
            </div>
        </div>

        <table class="no-border" style="margin-top:10px;">
            <tr>
                <td>
                    วันที่<span class="dotted-line" style="width: 100px; text-align: center;">
                        {{ $items->isNotEmpty() ? $items->first()->created_at->format('d/m/Y') : '-' }}
                    </span>
                </td>
                <td class="text-right">
                    เลขที่ <span class="dotted-line" style="width: 35%; text-align: center;">{{ $document->document_no ?? ''}}</span>
                </td>
            </tr>
        </table>

        {{-- BUYER --}}
        <table class="no-border" style="margin-top:10px;">
            <tr>
                <td>
                    ชื่อผู้ซื้อ
                    <span class="dotted-line" style="width: 91%; text-align: left; padding-left:10px;">{{ $document->buyer_name ?? ''}}</span>
                </td>
            </tr>
            <tr>
                <td>
                    ที่อยู่
                    <span class="dotted-line" style="width: 93%; text-align: left; padding-left:10px;">{{ $document->buyer_address ?? ''}}</span>
                </td>
            </tr>
            <tr>
                <td>
                    กำหนดชำระ
                    <span class="dotted-line" style="width: 30%; text-align: left; padding-left:10px;">{{ $document->buyer_tax_id ?? ''}}</span>
                </td>
            </tr>
        </table>

        {{-- ITEMS --}}
        <table>
            <thead>
                <tr>
                    <th style="width:5%">ลำดับ</th>
                    <th>รายการ</th>
                    <th style="width:10%">จำนวน</th>
                    <th style="width:15%">หน่วยละ</th>
                    <th style="width:15%">จำนวนเงิน</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($items as $index => $item)
                    <tr>
                        <td style="text-align:center;">{{ $index + 1 }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }}</td>
                        <td class="text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>

            @php
                $vat = round(($totalPrice * 7) / 107, 2);
                $beforeVat = $totalPrice - $vat;
            @endphp

            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">
                        มูลค่ารวมก่อนเสียภาษี
                        <br>
                        ภาษีมูลค่าเพิ่ม (VAT)
                        <br>
                        <strong>ยอดรวม</strong>
                    </td>
                    <td>
                        <span
                            style="display:inline-block; width:120px; text-align:right;">{{ number_format($beforeVat, 2) }}</span><br>
                        <span
                            style="display:inline-block; width:120px; text-align:right;">{{ number_format($vat, 2) }}</span><br>
                        <strong><span
                                style="display:inline-block; width:120px; text-align:right;">{{ number_format($totalPrice, 2) }}</span></strong>
                    </td>
                </tr>
            </tfoot>

        </table>

    </div>
</body>

</html>
