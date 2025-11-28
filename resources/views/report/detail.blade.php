@extends('layout.layout')
@section('title', "รายละเอียดหมวดหมู่: $category")

@section('desktop-content')
    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">รายละเอียดหมวดหมู่: {{ $category }}</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">

        {{-- ปุ่ม Export --}}
        <div class="mb-3 d-flex justify-content-end">
            <a href="{{ route('summary.exportDetail', ['category' => $category, 'year' => $year, 'month' => $month]) }}"
                class="btn btn-success btn-sm">
                Export Excel
            </a>
        </div>

        {{-- ตารางรายละเอียดสินค้า --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวนขายออก (ชิ้น)</th>
                        <th>จำนวนคงเหลือ (ชิ้น)</th>
                        <th>ยอดขาย (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($detail as $row)
                        <tr>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['sold'] }}</td>
                            <td>{{ $row['remain'] }}</td>
                            <td>{{ number_format($row['totalPrice'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
