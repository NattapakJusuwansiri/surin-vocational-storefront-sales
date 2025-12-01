@extends('layout.layout')
@section('title', 'รายละเอียดบิล')

@section('desktop-content')

<div class="d-flex align-items-center flex-column text-white">
    <h1 class="text-center p-2">รายละเอียดบิล</h1>
</div>

<div class="container p-5 bg-white rounded-5">

    <div class="row mb-3">
        <div class="col-md-6">
            {{-- ส่ง filter กลับไปด้วย --}}
            <a href="{{ route('receipts.index', ['year'=>$year, 'month'=>$month, 'day'=>$day ?? '']) }}" 
               class="btn btn-secondary btn-sm">
               กลับไปหน้ารวมบิล
            </a>
        </div>
        <div class="col-md-6 d-flex justify-content-end">
            <div class="col-md-6 d-flex justify-content-end">
                {{-- ส่ง filter Excel ด้วย --}}
                <a href="{{ route('receipts.detail.export', [
                        'bill_id' => $items->first()->bill_id,
                        'year' => $year,
                        'month' => $month,
                        'day' => $day ?? ''
                    ]) }}" class="btn btn-success btn-sm me-2">
                    Export Excel
                </a>

                {{-- ปุ่ม PDF / Tax --}}
                <a href="#" class="btn btn-danger btn-sm me-2">Export PDF</a>
                <a href="#" class="btn btn-primary btn-sm">Export Tax</a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped text-center align-middle">
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวนสินค้า</th>
                    <th>ราคา</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->stock->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">ไม่มีข้อมูล</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                    <td><strong>{{ number_format($totalPrice, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>จ่าย:</strong></td>
                    <td><strong>{{ number_format($paid ?? $totalPrice, 2) }}</strong></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end"><strong>ทอน:</strong></td>
                    <td><strong>{{ number_format($change ?? 0, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

@endsection
