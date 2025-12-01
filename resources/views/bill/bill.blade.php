@extends('layout.layout')
@section('title', 'รายการใบเสร็จ')

@section('desktop-content')

    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">รายการใบเสร็จ</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">

        {{-- ฟิลเตอร์เลือกช่วงวันที่ --}}
        <form method="GET" class="row mb-3 g-2 align-items-end">

            <div class="col-md-3">
                <label>วันที่เริ่มต้น</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>

            <div class="col-md-3">
                <label>วันที่สิ้นสุด</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <label>&nbsp;</label>
                <button class="btn btn-primary w-100">กรองข้อมูล</button>
            </div>
        </form>

        {{-- Search + Export --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm me-2" placeholder="ค้นหาสินค้า..." style="width:auto;">
                    <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
                </form>
            </div>

            <div class="col-md-6 d-flex justify-content-end">
                <a href="{{ route('receipts.export', request()->all()) }}" class="btn btn-success btn-sm">
                    Export Excel
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>จำนวนสินค้า</th>
                        <th>ราคาที่จ่าย</th>
                        <th>วันที่และเวลา</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['total_quantity'] }}</td>
                            <td>{{ number_format($row['total_price'], 2) }}</td>
                            <td>{{ $row['datetime'] }}</td>
                            <td>
                                <a href="{{ route('receipts.detail', ['bill_id' => $row['bill_id']] + request()->all()) }}"
                                    class="btn btn-sm btn-info">
                                    ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection
