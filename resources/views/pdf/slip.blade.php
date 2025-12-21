<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>PDF Report</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ storage_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ storage_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
        }

        body {
            font-family: 'THSarabunNew', sans-serif;
            font-weight: bold;
            font-size: 20px;
            line-height: 1;
        }

        .receipt {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            /* จัดให้อยู่กึ่งกลางแนวนอน */
            background: #ffffff;
            /* border: 1px solid black; */
            padding: 10px;
            border-radius: 5px;
        }

        .receipt h2 {
            text-align: center;
            margin-top: 5px;
            font-size: 38px;
            margin-bottom: 0px;
            font-weight: bold;
            color: black;
        }

        .receipt span {
            font-weight: bold;
        }

        .header {
            display: table;
            width: 100%;
            margin-bottom: 1px;
        }

        .header .info,
        .header .tax-label {
            display: table-cell;
            vertical-align: top;
        }

        .header .info {
            text-align: left;
        }

        .header .tax-label {
            text-align: right;
            font-weight: bold;
            color: black;
        }

        .info p {
            margin: 4px 0;
            font-size: 26px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 10px;
            font-size: 26px;
        }

        th:nth-child(1),
        td:nth-child(1) {
            text-align: left;
            width: 60%;
        }

        th:nth-child(2),
        td:nth-child(2) {
            text-align: center;
            width: 10%;
        }

        th:nth-child(3),
        td:nth-child(3) {
            text-align: right;
            width: 30%;
        }

        .total {
            text-align: right;
            font-weight: bold;
            color: black;
            border-top: 2px solid #000;
            margin-top: 20px;
            padding-top: 12px;
            font-size: 18px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: black;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div id="print-area">
        <div class="receipt">

            <h2><span>ใบเสร็จรับเงิน</span></h2>

            <div class="header">
                <div class="info">
                    <p><strong>เลขที่บิล #{{ $bill_id }}</strong></p>
                    <p>วันที่: {{ $items->first()->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>สินค้า</th>
                        <th style="text-align:center;">จำนวน</th>
                        <th style="text-align:right;">ราคา</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>{{ $item->stock->name }}</td>
                            <td style="text-align:center;">{{ $item->quantity }}</td>
                            <td style="text-align:right;">
                                {{ number_format($item->quantity * $item->price, 2) }} ฿
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="total">
                รวมทั้งหมด: {{ number_format($totalPrice, 2) }} ฿
            </p>

            <p class="total">
                ชำระ: {{ number_format($paid, 2) }} ฿
            </p>

            <p class="total">
                เงินทอน: {{ number_format($change, 2) }} ฿
            </p>

        </div>
    </div>
</body>

</html>
