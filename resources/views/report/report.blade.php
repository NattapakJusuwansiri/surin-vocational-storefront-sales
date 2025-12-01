@extends('layout.layout')
@section('title', 'สรุปยอดรายหมวดหมู่')

@section('desktop-content')

    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">สรุปยอดรายหมวดหมู่</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">

        {{-- ฟิลเตอร์เลือก ปี + เดือน --}}
        <form method="GET" class="row mb-3 g-2 align-items-end">

            <div class="col-md-3">
                <label>เริ่มวันที่</label>
                <input type="date" name="start_date" class="form-control"
                    value="{{ request('start_date', date('Y-m-01')) }}">
            </div>

            <div class="col-md-3">
                <label>สิ้นสุดวันที่</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
            </div>

            <div class="col-md-2">
                <label>&nbsp;</label>
                <button class="btn btn-primary w-100">กรองข้อมูล</button>
            </div>
        </form>

        {{-- การ์ดสรุป --}}
        <div class="row my-4">

            {{-- กราฟไว้ซ้าย --}}
            <div class="col-md-6">
                <div class="shadow p-3 rounded bg-white">
                    <canvas id="topCategoryChart" height="130"></canvas>
                </div>
            </div>

            {{-- การ์ดไว้ขวา --}}
            <div class="col-md-6">
                <div class="row g-3">

                    <div class="col-12">
                        <div class="shadow p-3 rounded text-center bg-light">
                            <h5>ยอดขายทั้งหมด</h5>
                            <h2 class="text-success">{{ number_format($totalSales, 2) }} บาท</h2>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="shadow p-3 rounded text-center bg-light">
                            <h5>จำนวนสินค้าที่ขายออก</h5>
                            <h2 class="text-primary">{{ $totalQuantitySold }} ชิ้น</h2>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('topCategoryChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topCategories->keys()) !!},
                    datasets: [{
                        label: 'จำนวนขาย (ชิ้น)',
                        data: {!! json_encode($topCategories->values()) !!},
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: '10 ประเภทสินค้าที่ขายดี', // ชื่อกราฟ
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
        {{-- Search + Export --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm me-2" placeholder="ค้นหาชื่อหรือหมวดหมู่..."
                        style="width:auto;">
                    <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
                </form>
            </div>

            {{-- ปุ่ม Export ไปขวา --}}
            <div class="col-md-6 d-flex justify-content-end">
                <a href="{{ route('summary.export', request()->all()) }}" class="btn btn-success btn-sm">
                    Export Excel
                </a>
            </div>
        </div>

        {{-- ตารางสรุป --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>หมวดหมู่</th>
                        <th>จำนวนขายทั้งหมด (ชิ้น)</th>
                        <th>จำนวนที่เหลือ (ชิ้น)</th>
                        <th>วันที่</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $row)
                        <tr>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['sold'] }}</td>
                            <td>{{ $row['remain'] }}</td>
                            <td>{{ $row['date'] }}</td>
                            <td>
                                <a href="{{ route('summary.detail', [
                                    'category' => $row['category'],
                                    'start_date' => request('start_date'),
                                    'end_date' => request('end_date'),
                                ]) }}"
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
