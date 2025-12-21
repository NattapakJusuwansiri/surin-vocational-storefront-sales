@extends('layout.layout')
@section('title', 'รายการเอกสาร')

@section('desktop-content')

    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">รายการเอกสาร</h1>
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
        {{-- Search + Export --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm me-2" placeholder="ค้นหาเอกสาร..." style="width:auto;">
                    <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
                </form>
            </div>

            <div class="col-md-6 d-flex justify-content-end">
                <a href="#" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal">
                    ออกเอกสาร
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ประเภทเอกสาร</th>
                        <th>เลขที่</th>
                        <th>วันที่ออก</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            @php
                                $docTypes = [
                                    'delivery' => 'ใบส่งสินค้า',
                                    'tax_invoice' => 'ใบกำกับภาษี',
                                    'tax' => 'ใบกำกับภาษี',
                                    'invoice' => 'ใบแจ้งหนี้',
                                    'quotation' => 'ใบเสนอราคา',
                                ];
                            @endphp
                            <td>{{ $docTypes[$doc->document_type] ?? $doc->document_type }}</td>
                            <td>{{ $doc->document_no }}</td>
                            <td>{{ \Carbon\Carbon::parse($doc->document_date)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('documents.pdf', $doc->id) }}" target="_blank"
                                    class="btn btn-danger btn-sm">
                                    <i class="bi bi-filetype-pdf"></i>
                                </a>
                                <a href="{{ route('documents.detail', $doc->id) }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-search"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                ไม่มีเอกสาร
                            </td>
                        </tr>
                    @endforelse
                </tbody>


            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            <nav>
                <ul class="pagination mb-0">

                    {{-- ก่อนหน้า --}}
                    <li class="page-item {{ $documents->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $documents->previousPageUrl() }}">
                            <i class="bi bi-chevron-double-left"></i>
                        </a>
                    </li>

                    {{-- เลขหน้า --}}
                    @foreach ($documents->getUrlRange(1, $documents->lastPage()) as $page => $url)
                        <li class="page-item {{ $page == $documents->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endforeach

                    {{-- ถัดไป --}}
                    <li class="page-item {{ $documents->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $documents->nextPageUrl() }}">
                            <i class="bi bi-chevron-double-right"></i>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </div>

@endsection

<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <form action="{{ route('documents.store') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">ออกเอกสาร</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body overflow-y-auto" style="max-height:70vh">
                    <div id="formError" class="alert alert-danger d-none"></div>

                    {{-- เลือกประเภทเอกสาร --}}
                    <div class="mb-3">
                        <label class="form-label">ประเภทเอกสาร</label>
                        <select name="document_type" id="document_type" class="form-select" required>
                            <option value="">-- เลือกประเภท --</option>
                            <option value="delivery">ใบส่งสินค้า</option>
                            <option value="tax">ใบกำกับภาษี</option>
                            <option value="invoice">ใบแจ้งหนี้</option>
                            <option value="quotation">ใบเสนอราคา</option>
                        </select>
                    </div>

                    {{-- ฟอร์มกลาง --}}
                    <div class="mb-3">
                        <label>วันที่ออกเอกสาร</label>
                        <input type="date" name="document_date" class="form-control"
                            value="{{ now()->format('Y-m-d') }}" required readonly>
                    </div>

                    {{-- ใบกำกับภาษี --}}
                    <div id="tax-form" class="doc-form d-none">
                        <hr>
                        <h6 class="fw-bold">ข้อมูลใบกำกับภาษี</h6>

                        {{-- ผู้ขาย --}}
                        <div class="mb-2">
                            <label>ชื่อผู้ขาย</label>
                            <input type="text" name="seller_name" class="form-control">
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>เลขประจำตัวผู้เสียภาษี (ผู้ขาย)</label>
                                <input type="text" name="seller_tax_id" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>สาขาที่</label>
                                <input type="text" name="seller_branch" class="form-control"
                                    placeholder="สำนักงานใหญ่ / สาขา 00001">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label>ที่อยู่ผู้ขาย</label>
                            <textarea name="seller_address" class="form-control" rows="2"></textarea>
                        </div>

                        {{-- ผู้ซื้อ --}}
                        <hr>
                        <h6 class="fw-bold">ข้อมูลผู้ซื้อ</h6>

                        <div class="mb-2">
                            <label>ชื่อผู้ซื้อ</label>
                            <input type="text" name="buyer_name" class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>เลขประจำตัวผู้เสียภาษี (ผู้ซื้อ)</label>
                            <input type="text" name="buyer_tax_id" class="form-control">
                        </div>

                        <div class="mb-2">
                            <label>ที่อยู่ผู้ซื้อ</label>
                            <textarea name="buyer_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>


                    {{-- ใบแจ้งหนี้ --}}
                    <div id="invoice-form" class="doc-form d-none">
                        <hr>
                        <h6 class="fw-bold">ข้อมูลใบแจ้งหนี้</h6>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>ชื่อลูกค้า</label>
                                <input type="text" name="buyer_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>กำหนดชำระ</label>
                                <input type="date" name="buyer_tax_id" class="form-control">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label>ที่อยู่ลูกค้า</label>
                            <textarea name="buyer_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    {{-- ใบเสนอราคา --}}
                    <div id="quotation-form" class="doc-form d-none">
                        <hr>
                        <h6 class="fw-bold">ข้อมูลใบเสนอราคา</h6>

                        {{-- ผู้ขาย --}}
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>ชื่อผู้ขาย</label>
                                <input type="text" name="seller_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>โทรศัพท์</label>
                                <input type="tel" class="form-control" name="seller_tax_id" pattern="[0-9]{10}"
                                    maxlength="10">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label>ที่อยู่ผู้ขาย</label>
                            <textarea name="seller_address" class="form-control" rows="2"></textarea>
                        </div>

                        {{-- ผู้ซื้อ --}}
                        <hr>
                        <h6 class="fw-bold">ข้อมูลผู้ซื้อ</h6>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>ชื่อลูกค้า</label>
                                <input type="text" name="buyer_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>โทรศัพท์</label>
                                <input type="tel" class="form-control" name="buyer_tax_id" pattern="[0-9]{10}"
                                    maxlength="10">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label>ที่อยู่ลูกค้า</label>
                            <textarea name="buyer_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    {{-- ใบส่งสินค้า --}}
                    <div id="delivery-form" class="doc-form d-none">
                        <hr>
                        <h6 class="fw-bold">ข้อมูลใบส่งสินค้า</h6>

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label>ผู้รับสินค้า</label>
                                <input type="text" name="buyer_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>วันที่ส่งสินค้า</label>
                                <input type="date" name="buyer_tax_id" class="form-control">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label>สถานที่จัดส่ง</label>
                            <textarea name="buyer_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    {{-- ตารางสินค้า (ใช้ร่วมทุกประเภท) --}}
                    <div id="items-section" class="d-none">
                        <hr>
                        <h6 class="fw-bold">รายการสินค้า</h6>

                        <table class="table table-bordered" id="doc-items">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>สินค้า</th>
                                    <th width="120">จำนวน</th>
                                    <th width="150" class="price-col">ราคาต่อหน่วย</th>
                                    <th width="150" class="price-col">รวม</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" name="items[0][name]" class="form-control" required>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][qty]" class="form-control qty"
                                            value="1">
                                    </td>
                                    <td class="price-col">
                                        <input type="number" name="items[0][price]" class="form-control price"
                                            value="0">
                                    </td>
                                    <td class="text-end total price-col">0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-row">
                            + เพิ่มสินค้า
                        </button>

                        <div class="text-end mt-3 fw-bold price-col">
                            รวมทั้งสิ้น: <span id="grand-total">0.00</span> บาท
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button class="btn btn-primary">บันทึกเอกสาร</button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- สร้างแถวสินค้า --}}
<script>
    function createItemRow(index, type) {
        return `
        <tr>
            <td>
                <input type="text" name="items[${index}][name]" class="form-control" required>
            </td>
            <td>
                <input type="number" name="items[${index}][qty]" class="form-control qty" value="1">
            </td>
            <td class="price-col">
                <input type="number" name="items[${index}][price]" class="form-control price" value="0">
            </td>
            <td class="text-end total price-col">0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
            </td>
        </tr>`;
    }
</script>

{{-- reset ตาราง --}}
<script>
    let rowIndex = 0;

    function resetItemsTable(type) {
        const tbody = document.querySelector('#doc-items tbody');
        tbody.innerHTML = ''; // 🔥 ล้างแถวทั้งหมด
        rowIndex = 0;

        tbody.insertAdjacentHTML('beforeend', createItemRow(rowIndex, type));
        rowIndex++;

        // reset total
        const gt = document.getElementById('grand-total');
        if (gt) gt.innerText = '0.00';
    }
</script>

{{-- เปลี่ยนประเภทเอกสาร --}}
<script>
    const docTypeSelect = document.getElementById('document_type');
    const itemsSection = document.getElementById('items-section');

    docTypeSelect.addEventListener('change', function() {

        // 🔥 RESET ERROR เมื่อเปลี่ยนประเภทเอกสาร
        document.getElementById('formError').classList.add('d-none');
        document.getElementById('formError').innerHTML = '';
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        const type = this.value;

        // 🔥 ซ่อน + disable ทุกฟอร์มก่อน
        document.querySelectorAll('.doc-form').forEach(form => {
            form.classList.add('d-none');
            form.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = true;
            });
        });

        // ซ่อน section รายการสินค้า
        itemsSection.classList.add('d-none');

        // แสดงราคาปกติ
        document.querySelectorAll('.price-col').forEach(el => el.style.display = '');

        if (!type) return;

        // 🔹 เปิดเฉพาะฟอร์มที่เลือก
        const activeForm = document.getElementById(type.replace('_', '-') + '-form');
        if (activeForm) {
            activeForm.classList.remove('d-none');
            activeForm.querySelectorAll('input, textarea, select').forEach(el => {
                el.disabled = false;
            });
        }

        // 🔹 แสดงรายการสินค้า
        itemsSection.classList.remove('d-none');

        // 🔥 RESET ตารางสินค้าทุกครั้งที่เปลี่ยนประเภท
        resetItemsTable(type);
    });
</script>

{{-- เพิ่มสินค้า --}}
<script>
    document.getElementById('add-row').addEventListener('click', function() {
        const type = document.getElementById('document_type').value;
        const tbody = document.querySelector('#doc-items tbody');

        tbody.insertAdjacentHTML('beforeend', createItemRow(rowIndex, type));
        rowIndex++;
    });
</script>

{{-- คำนวณแต่ละแถว --}}
<script>
    function calculateTotals() {
        let grandTotal = 0;

        document.querySelectorAll('#doc-items tbody tr').forEach(row => {
            const qtyInput = row.querySelector('.qty');
            const priceInput = row.querySelector('.price');
            const totalCell = row.querySelector('.total');

            // ถ้าไม่มี price (เช่น delivery) ให้ข้าม
            if (!qtyInput || !priceInput || !totalCell) return;

            const qty = parseFloat(qtyInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            const lineTotal = qty * price;

            totalCell.innerText = lineTotal.toFixed(2);
            grandTotal += lineTotal;
        });

        const gt = document.getElementById('grand-total');
        if (gt) gt.innerText = grandTotal.toFixed(2);
    }

    // เปลี่ยน qty / price → คำนวณใหม่
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
            calculateTotals();
        }
    });

    // ลบแถว → คำนวณใหม่
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('tr').remove();
            calculateTotals();
        }
    });
</script>

<script>
    document.querySelector('#documentModal form').addEventListener('submit', function(e) {

        // reset error
        document.getElementById('formError').classList.add('d-none');
        document.getElementById('formError').innerHTML = '';
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        let errors = [];
        let firstInvalid = null;

        const type = document.getElementById('document_type').value;

        if (!type) {
            errors.push('กรุณาเลือกประเภทเอกสาร');
            markInvalid(document.getElementById('document_type'));
        }

        // ======================
        // TAX INVOICE
        // ======================
        if (type === 'tax') {
            validateRequired('seller_name', 'กรุณากรอกชื่อผู้ขาย');
            validateRequired('seller_tax_id', 'กรุณากรอกเลขผู้เสียภาษีผู้ขาย');
            validateRequired('buyer_name', 'กรุณากรอกชื่อผู้ซื้อ');
            validateRequired('buyer_tax_id', 'กรุณากรอกเลขประจำตัวผู้เสียภาษีผู้ซื้อ');
        }

        // ======================
        // INVOICE
        // ======================
        if (type === 'invoice') {
            validateRequired('buyer_name', 'กรุณากรอกชื่อลูกค้า');
            validateRequired('buyer_tax_id', 'กรุณาเลือกวันครบกำหนด');
        }

        // ======================
        // Delivery
        // ======================
        if (type === 'delivery') {
            validateRequired('buyer_name', 'กรุณากรอกชื่อผู้รับสินค้า');
            validateRequired('buyer_tax_id', 'กรุณาเลือกวันที่ส่งสินค้า');
            validateRequired('buyer_address', 'กรุณากรอกสถานที่จัดส่ง');
        }

        // ======================
        // QUOTATION
        // ======================
        if (type === 'quotation') {
            validateRequired('seller_name', 'กรุณากรอกชื่อผู้ขาย');
            validateRequired('seller_tax_id', 'กรุณากรอกเลขโทรศัพท์ผู้ขาย');
            validateRequired('buyer_name', 'กรุณากรอกชื่อผู้ซื้อ');
            validateRequired('buyer_tax_id', 'กรุณากรอกเลขโทรศัพท์ผู้ซื้อ');
        }

        // ======================
        // QUOTATION / TAX / INVOICE
        // ======================
        if (['quotation', 'tax', 'invoice'].includes(type)) {
            const rows = document.querySelectorAll('#doc-items tbody tr');

            if (rows.length === 0) {
                errors.push('กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการ');
            }

            rows.forEach((row, i) => {
                const name = row.querySelector(`[name="items[${i}][name]"]`);
                const qty = row.querySelector(`[name="items[${i}][qty]"]`);
                const price = row.querySelector(`[name="items[${i}][price]"]`);

                if (name && !name.value.trim()) {
                    errors.push(`แถวที่ ${i+1}: กรุณากรอกชื่อสินค้า`);
                    markInvalid(name);
                }

                if (qty && qty.value <= 0) {
                    errors.push(`แถวที่ ${i+1}: จำนวนต้องมากกว่า 0`);
                    markInvalid(qty);
                }

                if (price && price.value < 0) {
                    errors.push(`แถวที่ ${i+1}: ราคาต้องไม่ติดลบ`);
                    markInvalid(price);
                }
            });
        }

        // ❌ ถ้ามี error → หยุด submit
        if (errors.length > 0) {
            e.preventDefault();

            const box = document.getElementById('formError');
            box.innerHTML = '<ul class="mb-0"><li>' + errors.join('</li><li>') + '</li></ul>';
            box.classList.remove('d-none');

            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

        // ---------------------
        function validateRequired(name, message) {
            const el = document.querySelector(
                `[name="${name}"]:not([disabled])`
            );

            if (el && !el.value.trim()) {
                errors.push(message);
                markInvalid(el);
            }
        }


        function markInvalid(el) {
            el.classList.add('is-invalid');
            if (!firstInvalid) firstInvalid = el;
        }

    });
</script>

<script>
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
        }
    });
</script>

<script>
    function refreshAfterSubmit(form) {
        setTimeout(() => {
            window.location.reload();
        }, 500); // ให้หน่วง 0.5 วินาที เพื่อให้ request ไปถึง server ก่อน
        return true; // ให้ form submit ตามปกติ
    }
</script>
