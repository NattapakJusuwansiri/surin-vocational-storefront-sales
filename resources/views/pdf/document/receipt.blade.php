<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จรับเงิน</title>
    <style>
        body {
            font-family: 'THSarabunNew';
            font-size: 16pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>ใบเสร็จรับเงิน / RECEIPT</h2>
    <p>เลขที่: {{ $document->document_no }}</p>
    <p>วันที่: {{ \Carbon\Carbon::parse($document->document_date)->format('d/m/Y') }}</p>

    <h4>ผู้รับเงิน</h4>
    <p>{{ $document->buyer_name }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>รายการ</th>
                <th>จำนวน</th>
                <th>หน่วยละ</th>
                <th>จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td style="text-align:center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>รวมทั้งสิ้น</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPrice, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
