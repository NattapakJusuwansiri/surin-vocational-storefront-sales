@extends('layout.layout')
@section('title', 'รายการสินค้า')

@section('desktop-content')
    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">รายการสินค้า</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">
        <div class="row mb-3">
            <div class="col-md-12 d-flex justify-content-end">
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addNewStockModal">
                    เพิ่มสินค้าใหม่
                </button>
            </div>
        </div>
        {{-- ฟิลเตอร์ / ค้นหา --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex align-items-center">
                    <span class="me-1">แสดง</span>
                    <select name="perPage" class="form-select form-select-sm me-2" style="width:auto;"
                        onchange="this.form.submit()">
                        <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="-1" {{ request('perPage') == -1 ? 'selected' : '' }}>ทั้งหมด</option>
                    </select>
                    <span class="me-1">รายการ</span>
                </form>
            </div>

            <div class="col-md-6 d-flex justify-content-end">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm me-2" placeholder="ค้นหาชื่อหรือหมวดหมู่..."
                        style="width:auto;">
                    <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
                </form>
            </div>
        </div>

        {{-- ตารางข้อมูล --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่สินค้า</th>
                        <th>จำนวนหน้าร้าน</th>
                        <th>จำนวนหลังร้าน</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $index => $stock)
                        <tr>
                            <td>{{ $stocks->firstItem() + $index }}</td>
                            <td>{{ $stock->name }}</td>
                            <td>{{ $stock->category }}</td>
                            <td>{{ $stock->quantity_front }}</td>
                            <td>{{ $stock->quantity_back }}</td>
                            <td>
                                <!-- ปุ่มเปิด Modal -->
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editStockModal{{ $stock->id }}">
                                    แก้ไข
                                </button>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#addStockModal{{ $stock->id }}">
                                    เติมสินค้า
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">ไม่มีสินค้า</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3 d-flex justify-content-center">
            {{ $stocks->withQueryString()->links() }}
        </div>
    </div>
@endsection

{{-- ==================== Modals ==================== --}}
@foreach ($stocks as $stock)
    <div class="modal fade" id="editStockModal{{ $stock->id }}" tabindex="-1"
        aria-labelledby="editStockModalLabel{{ $stock->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('stock.updateMovement', $stock->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStockModalLabel{{ $stock->id }}">
                            โอนย้ายสินค้า: {{ $stock->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>จำนวนหน้าร้าน: {{ $stock->quantity_front }}</p>
                        <p>จำนวนหลังร้าน: {{ $stock->quantity_back }}</p>

                        <div class="mb-3">
                            <label for="quantity{{ $stock->id }}" class="form-label">จำนวนที่ต้องการย้าย</label>
                            <input type="number" class="form-control" name="quantity" id="quantity{{ $stock->id }}"
                                required min="1">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">จาก</label>
                            <select name="from_location" class="form-select" required>
                                <option value="front">หน้าร้าน</option>
                                <option value="back">หลังร้าน</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ไป</label>
                            <select name="to_location" class="form-select" required>
                                <option value="front">หน้าร้าน</option>
                                <option value="back">หลังร้าน</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">บันทึก</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@foreach ($stocks as $stock)
    <div class="modal fade" id="addStockModal{{ $stock->id }}" tabindex="-1"
        aria-labelledby="addStockModalLabel{{ $stock->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('stock.addStock', $stock->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addStockModalLabel{{ $stock->id }}">
                            เติมสินค้า: {{ $stock->name }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>จำนวนคงเหลือทั้งหมด: {{ $stock->quantity_front + $stock->quantity_back }}</p>

                        <div class="mb-3">
                            <label class="form-label">เลือกรูปแบบ</label>
                            <select name="unit_type" class="form-select" required>
                                <option value="unit">ชิ้น</option>
                                <option value="pack">แพ็ค (12 ชิ้นต่อแพ็ค)</option>
                                <option value="box">กล่อง (24 ชิ้นต่อกล่อง)</option>
                                <option value="dozen">โหล (12 ชิ้นต่อโหล)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="quantity_add{{ $stock->id }}" class="form-label">จำนวน</label>
                            <input type="number" class="form-control" name="quantity"
                                id="quantity_add{{ $stock->id }}" required min="1">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">บันทึก</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<div class="modal fade" id="addNewStockModal" tabindex="-1" aria-labelledby="addNewStockModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('stock.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มสินค้าใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">หมวดหมู่สินค้า</label>
                        <input type="text" name="category" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">จำนวนเริ่มต้น</label>
                        <input type="number" name="quantity" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รหัสสินค้า</label>
                        <input type="text" name="product_code" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Barcode หน่วย</label>
                        <input type="file" name="barcode_unit" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Barcode แพ็ค</label>
                        <input type="file" name="barcode_pack" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Barcode กล่อง</label>
                        <input type="file" name="barcode_box" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">บันทึก</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>
</div>
