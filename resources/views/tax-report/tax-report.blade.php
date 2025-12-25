@extends('layout.layout')
@section('title', 'รายงานภาษี')

@section('desktop-content')

    <div class="container p-5 bg-white rounded-5">

        <h2 class="text-center mb-4">📊 รายงานภาษี</h2>

        {{-- Filter --}}
        <form method="GET" class="row g-2 mb-4 align-items-end">
            <div class="col-md-3">
                <label>เริ่มวันที่</label>
                <input type="date" name="start_date" class="form-control"
                    value="{{ request('start_date', date('Y-m-01')) }}">
            </div>

            <div class="col-md-3">
                <label>สิ้นสุดวันที่</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
            </div>

            <div class="col-md-3">
                <label>ประเภทรายงาน</label>
                <select name="type" class="form-select">
                    <option value="output" {{ request('type') == 'output' ? 'selected' : '' }}>ภาษีขาย</option>
                    <option value="input" {{ request('type') == 'input' ? 'selected' : '' }}>ภาษีซื้อ</option>
                    <option value="summary" {{ request('type') == 'summary' ? 'selected' : '' }}>สรุปสุทธิ</option>
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary w-100">แสดงรายงาน</button>
            </div>
        </form>

        {{-- SUMMARY --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <h6>VAT ขาย</h6>
                        <h4 class="text-success">{{ number_format($summary['output_vat'], 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <h6>VAT ซื้อ</h6>
                        <h4 class="text-danger">{{ number_format($summary['input_vat'], 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <h6>VAT สุทธิ</h6>
                        <h4 class="{{ $summary['net_vat'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($summary['net_vat'], 2) }}
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        @if (request('type') !== 'summary')
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead>
                        <tr>
                            <th>วันที่</th>
                            <th>เลขที่เอกสาร</th>
                            <th>คู่ค้า</th>
                            <th>มูลค่า</th>
                            <th>VAT</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (request('type') == 'input' ? $input : $output as $row)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($row->document_date)->format('d/m/Y') }}</td>
                                <td>{{ $row->document_no }}</td>
                                <td>{{ $row->buyer_name }}</td>
                                <td>{{ number_format($row->total_amount-$row->vat_amount, 2) }}</td>
                                <td>{{ number_format($row->vat_amount, 2) }}</td>
                                <td>{{ number_format($row->total_amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('tax.report.export', request()->all()) }}" class="btn btn-success mt-3">
                Export Excel
            </a>
        @endif

    </div>

@endsection
