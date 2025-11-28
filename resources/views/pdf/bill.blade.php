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

        .dotted-line {
            border-bottom: 2px dotted blue;
            display: inline-block;
        }

        .box_text {
            margin: 5px 0;
        }

        .title_doc {
            text-align: center;
            font-weight: bold;
            font-size: 36px;
            margin-bottom: 20px;
        }

        .checkbox-item {
            display: block;
            position: relative;
            padding-left: 25px;
            margin-bottom: 5px;
        }

        .checkbox-item::before {
            content: " ";
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid black;
            position: absolute;
            left: 0;
            top: 0;
        }

        .checkbox-item.checked::before {
            content: "✓";
            font-weight: bold;
            text-align: center;
            line-height: 16px;
        }
    </style>
</head>

<body>
    @php
        use Carbon\Carbon;

        // ตรวจสอบว่ามีค่าหรือไม่
        $monthThai = '-';
        if (!empty($fields['field_6'])) {
            $date = Carbon::parse($fields['field_6']);
            // แสดงแค่ชื่อเดือนภาษาไทย
            $months = [
                'มกราคม',
                'กุมภาพันธ์',
                'มีนาคม',
                'เมษายน',
                'พฤษภาคม',
                'มิถุนายน',
                'กรกฎาคม',
                'สิงหาคม',
                'กันยายน',
                'ตุลาคม',
                'พฤศจิกายน',
                'ธันวาคม',
            ];
            $monthThai = $months[$date->month - 1];
        }
    @endphp
    <div style="margin-top: 10%; margin-left:5rem; overflow: hidden;">
        <img src="{{ public_path('img/pdf/LOGO.png') }}" alt="LOGO"
            style="width:80px; float: left; margin-right: 10px; padding-top: 6px">
        <div style="font-size: 18px; line-height: 20px;">
            เทศบาลตำบลท่าข้าม<br>
            122 หมู่ที่ 3 ตำบลท่าข้าม อำเภอบางปะกง จังหวัดฉะเชิงเทรา 24130<br>
            โทรศัพท์ : 038-573441-2 อีเมลล์ : admin@thakam.go.th
        </div>
    </div>

    <h2 style="text-align: center">ใบรับเงินเดือน (Pay Slip)</h2>

    <div class="box_text" style="text-align: right; margin-right:5rem;">
        <span>รหัสพนักงาน
            <span class="dotted-line" style="width: 10%; text-align: center;">
                {{ $fields['field_20'] ?? '-' }}</span>
            <br></span>
        <span>ชื่อ
            <span class="dotted-line" style="width: 30%; text-align: center;">
                {{ $fields['field_2'] ?? '-' }} {{ $fields['field_1'] ?? '-' }}</span>
            <br></span>
        <span>ตำแหน่ง
            <span class="dotted-line" style="width: 30%; text-align: center;">
                {{ $fields['field_5'] ?? '-' }}</span>
            <br></span>
        <span>ประจำเดือน
            <span class="dotted-line" style="width: 20%; text-align: center;">
                {{ $monthThai ?? '-' }}</span>
            <br></span>
    </div>

    <div class="box_text" style="text-align: left; margin-left:5rem; margin-right:5rem; width: calc(100% - 10rem);">
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border:1px solid #000; padding:5px; width:50%;">รายรับ</th>
                    <th style="border:1px solid #000; padding:5px; width:50%;">รายจ่าย</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <!-- รายรับ -->
                    <td style="border:1px solid #000; padding:5px; vertical-align: top;">
                        @php $total_receipt = 0; @endphp
                        @foreach ($fields['field_3'] as $row)
                            @if ($row['receipt'])
                                @php
                                    $parts = explode(':', $row['receipt']);
                                    $label = $parts[0] ?? '';
                                    $amount = floatval(str_replace([',', ' '], '', $parts[1] ?? 0));
                                    $total_receipt += $amount;
                                @endphp
                                <table style="width:100%; border:none; border-collapse: collapse;">
                                    <tr>
                                        <td style="text-align:left; border:none;">{{ $label }}</td>
                                        <td style="text-align:right; border:none;">{{ number_format($amount, 2) }} บาท
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        @endforeach

                    </td>

                    <!-- รายจ่าย -->
                    <td style="border:1px solid #000; padding:5px; vertical-align: top;">
                        @php $total_expense = 0; @endphp
                        @foreach ($fields['field_3'] as $row)
                            @if ($row['expense'])
                                @php
                                    $parts = explode(':', $row['expense']);
                                    $label = $parts[0] ?? '';
                                    $amount = floatval($parts[1] ?? 0);
                                    $total_expense += $amount;
                                @endphp
                                <table style="width:100%; border:none; border-collapse: collapse;">
                                    <tr>
                                        <td style="text-align:left; border:none;">{{ $label }}</td>
                                        <td style="text-align:right; border:none;">{{ number_format($amount, 2) }} บาท
                                        </td>
                                    </tr>
                                </table>
                            @endif
                        @endforeach
                    </td>
                </tr>

                <!-- แถวรวมรายรับ/รายจ่าย -->
                <tr>
                    <td style="border:1px solid #000; padding:5px;">
                        <table style="width:100%; border:none; border-collapse: collapse; font-weight:bold;">
                            <tr>
                                <td style="text-align:left; border:none;">รวมรายรับ</td>
                                <td style="text-align:right; border:none;">{{ number_format($total_receipt, 2) }} บาท
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="border:1px solid #000; padding:5px;">
                        <table style="width:100%; border:none; border-collapse: collapse; font-weight:bold;">
                            <tr>
                                <td style="text-align:left; border:none;">รวมรายจ่าย</td>
                                <td style="text-align:right; border:none;">{{ number_format($total_expense, 2) }} บาท
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        รับสุทธิ:
                    </td>
                    <td style="text-align:right;">
                        <span style="border-bottom:1px solid #000;">
                            {{ number_format($total_receipt - $total_expense, 2) }} 
                        </span>บาท
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

    <div class="signature-section"
        style="display: flex; flex-direction: column; align-items: flex-end; gap: 2rem; margin-top:1rem; margin-bottom:1.5rem;">

        <div class="signature-item" style="text-align: center; margin-top: 3rem;">
            <div style="position: relative; display: inline-block;">
                <!-- รูป trash_1 -->
                <img src="{{ public_path('img/pdf/signature1.png') }}" alt="signature1"
                    style="width:40%; display: block;">
                <!-- รูป stamp ทับ trash_1 -->
                <img src="{{ public_path('img/pdf/stamp.png') }}" alt="stamp"
                    style="width:20%; position: absolute; top: 0; right: 58%; opacity: 0.9;">
            </div>

        </div>

    </div>


</body>

</html>
