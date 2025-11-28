@extends('layout.layout')
@section('title', 'แก้ไขจำนวนสินค้า')

@section('desktop-content')
    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">โอนย้ายสินค้าข้ามสต็อก</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">
        <h2 class="mb-3">{{ $stock->name }} (หมวดหมู่: {{ $stock->category }})</h2>
        <p>รหัสสินค้า: {{ $stock->product_code }}</p>
        <p>จำนวนหน้าร้าน: {{ $stock->quantity_front }}</p>
        <p>จำนวนหลังร้าน: {{ $stock->quantity_back }}</p>

        <form action="{{ route('stock.updateMovement', $stock->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="quantity" class="form-label">จำนวนที่ต้องการย้าย</label>
                <input type="number" class="form-control" name="quantity" id="quantity" required min="1">
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

            <button type="submit" class="btn btn-success">บันทึก</button>
            <a href="{{ route('show-stock') }}" class="btn btn-secondary">ยกเลิก</a>
        </form>
    </div>
@endsection
