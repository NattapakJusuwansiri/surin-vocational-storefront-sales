@extends('layout.layout')
@section('title', 'Cashier')

@section('desktop-content')

    <style>
        .scan-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #777;
            cursor: pointer;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">Cashier</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">

        {{-- ค้นหาและเพิ่มสินค้า --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="searchProduct" class="form-label">ค้นหาสินค้า</label>

                <div class="position-relative">
                    <input list="stockList" id="searchProduct" class="form-control ps-3 pe-5"
                        placeholder="สแกนบาร์โค้ด หรือ ค้นหาสินค้า...">

                    <i class="bi bi-upc-scan scan-icon"></i>

                    <datalist id="stockList">
                        @foreach ($stocks as $stock)
                            <option value="{{ $stock->barcode_unit }}" data-id="{{ $stock->id }}"
                                data-category="{{ $stock->category }}" data-price="{{ $stock->price ?? 0 }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div class="col-md-2">
                <label for="quantityAdd" class="form-label">จำนวน</label>
                <input type="number" id="quantityAdd" class="form-control" placeholder="จำนวน" min="1">
            </div>

            <div class="col-md-2 align-self-end">
                <button class="btn btn-success" id="addProductBtn">เพิ่ม</button>
            </div>
        </div>

        {{-- ตารางสินค้า --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle" id="cashierTable">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อสินค้า</th>
                        <th>หมวดหมู่</th>
                        <th>จำนวน</th>
                        <th>ราคาต่อหน่วย</th>
                        <th>รวม</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total:</th>
                        <th id="totalPrice">0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="row mt-3">
            <h5>ข้อมูลการชำระเงิน</h5>
            <div class="row col-md-6">
                <div class="col-md-6">
                    <label class="form-label">วิธีชำระเงิน</label>
                    <select id="payment_type" class="form-select">
                        <option value="cash">เงินสด</option>
                        <option value="credit">ขายเชื่อ (สมาชิก)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="member_code" class="form-label">รหัสสมาชิก</label>
                    <input type="text" id="member_code" class="form-control" placeholder="กรอกรหัสสมาชิก (ถ้ามี)">
                    <small class="text-muted">
                        เงินสด: กรอกเพื่อสะสมแต้ม | เครดิต: ต้องกรอก
                    </small>
                </div>
            </div>

            {{-- ช่องชำระเงิน --}}
            <div class="col-md-3">
                <label for="paidAmount" class="form-label">เงินที่ชำระ</label>
                <input type="number" id="paidAmount" class="form-control" min="0">
            </div>
            <div class="col-md-3">
                <label for="changeAmount" class="form-label">เงินทอน</label>
                <input type="text" id="changeAmount" class="form-control" readonly>
            </div>
        </div>

        <button id="saveBillBtn" class="btn btn-primary mt-3">บันทึกบิล</button>
        <div id="errorMsg" class="mt-3 text-danger"></div>
        <div id="successMsg" class="mt-3 text-success"></div>
    </div>

    <script>
        const stocks = @json($stocks);
        const searchInput = document.getElementById('searchProduct');
        const quantityInput = document.getElementById('quantityAdd');
        const addBtn = document.getElementById('addProductBtn');
        const tableBody = document.querySelector('#cashierTable tbody');
        const totalPriceElem = document.getElementById('totalPrice');
        const saveBillBtn = document.getElementById('saveBillBtn');
        const errorMsg = document.getElementById('errorMsg');
        const successMsg = document.getElementById('successMsg');
        const paidAmountInput = document.getElementById('paidAmount');
        const changeAmountInput = document.getElementById('changeAmount');

        function findProduct(value) {
            value = value.trim();

            return stocks.find(stock =>
                stock.barcode_unit === value ||
                stock.barcode_pack === value ||
                stock.barcode_box === value ||
                stock.product_code === value ||
                stock.name === value
            ) || null;
        }


        let selectedProduct = null;
        let items = [];

        // เมื่อกรอกชื่อจะเลือกสินค้า
        searchInput.addEventListener('input', function() {
            selectedProduct = findProduct(this.value);
        });


        // รองรับการกด Enter
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBtn.click();
            }
        });

        // รองรับเครื่องสแกนบาร์โค้ด (ยิงลง input + Enter → trigger change)
        searchInput.addEventListener('change', function() {
            const scanned = this.value.trim();

            selectedProduct = findProduct(scanned);

            if (selectedProduct) {
                if (!quantityInput.value) quantityInput.value = 1;
                addBtn.click();
            } else {
                alert('ไม่พบสินค้าที่ตรงกับข้อมูลที่สแกน');
            }
        });

        // ปุ่มเพิ่มสินค้า
        addBtn.addEventListener('click', function() {
            const quantity = parseInt(quantityInput.value);
            if (!selectedProduct) {
                alert('กรุณาเลือกสินค้า');
                return;
            }
            if (!quantity || quantity < 1) {
                alert('กรุณากรอกจำนวน');
                return;
            }

            const existingIndex = items.findIndex(i => i.stock_id === selectedProduct.id);
            if (existingIndex > -1) {
                items[existingIndex].quantity += quantity;
                updateTableRow(existingIndex);
            } else {
                items.push({
                    stock_id: selectedProduct.id,
                    quantity: quantity,
                    price: selectedProduct.price
                });
                addTableRow(items.length - 1);
            }

            searchInput.value = '';
            quantityInput.value = '';
            selectedProduct = null;
            updateTotal();
            updateChange();
        });

        function addTableRow(index) {
            const item = items[index];
            const rowCount = tableBody.rows.length + 1;
            const row = document.createElement('tr');
            row.setAttribute('data-index', index);

            row.innerHTML = `
            <td>${rowCount}</td>
            <td>${getStockName(item.stock_id)}</td>
            <td>${getStockCategory(item.stock_id)}</td>
            <td class="qty">${item.quantity}</td>
            <td>${item.price}</td>
            <td class="subtotal">${item.quantity * item.price}</td>
            <td>
                <button type="button" class="btn btn-sm btn-primary increaseBtn">+</button>
                <button type="button" class="btn btn-sm btn-warning decreaseBtn">-</button>
            </td>
        `;

            tableBody.appendChild(row);

            row.querySelector('.increaseBtn').addEventListener('click', function() {
                item.quantity++;
                updateTableRow(index);
                updateChange();
            });

            row.querySelector('.decreaseBtn').addEventListener('click', function() {
                item.quantity--;
                if (item.quantity <= 0) {
                    items.splice(index, 1);
                    row.remove();
                    updateRowNumbers();
                } else updateTableRow(index);
                updateTotal();
                updateChange();
            });
        }

        function updateTableRow(index) {
            const item = items[index];
            const row = tableBody.querySelector(`tr[data-index="${index}"]`);
            row.querySelector('.qty').textContent = item.quantity;
            row.querySelector('.subtotal').textContent = item.quantity * item.price;
            updateTotal();
        }

        function updateRowNumbers() {
            Array.from(tableBody.rows).forEach((row, i) => {
                row.cells[0].textContent = i + 1;
                row.setAttribute('data-index', i);
            });
        }

        function updateTotal() {
            totalPriceElem.textContent = items.reduce((sum, i) => sum + i.quantity * i.price, 0);
        }

        function updateChange() {
            const total = items.reduce((sum, i) => sum + i.quantity * i.price, 0);
            const paid = parseFloat(paidAmountInput.value) || 0;
            changeAmountInput.value = paid >= total ? paid - total : 0;
        }

        paidAmountInput.addEventListener('input', updateChange);

        function getStockName(id) {
            return stocks.find(s => s.id === id)?.name || '';
        }

        function getStockCategory(id) {
            return stocks.find(s => s.id === id)?.category || '';
        }

        // บันทึกบิล
        saveBillBtn.addEventListener('click', function() {
            errorMsg.textContent = '';
            successMsg.textContent = '';

            const paidAmount = parseFloat(paidAmountInput.value) || 0;
            const total = items.reduce((sum, i) => sum + i.quantity * i.price, 0);
            const paymentType = document.getElementById('payment_type').value;
            const memberCode = document.getElementById('member_code').value.trim();

            if (paymentType === 'credit' && !memberCode) {
                errorMsg.textContent = 'ขายเชื่อต้องกรอกรหัสสมาชิก';
                return;
            }


            if (items.length === 0) {
                errorMsg.textContent = 'ไม่มีสินค้าในบิล';
                return;
            }
            if (paidAmount < total) {
                errorMsg.textContent = 'จำนวนเงินที่จ่ายไม่เพียงพอ';
                return;
            }

            fetch("{{ route('cashier.add') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        items: items,
                        paid_amount: paidAmount,
                        payment_type: paymentType,
                        member_code: memberCode
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error === 'member_not_found') {
                        document.getElementById('modal_member_code').value = data.member_code;
                        new bootstrap.Modal(document.getElementById('memberModal')).show();
                        return;
                    }

                    if (data.success) {
                        successMsg.textContent = data.success;

                        // 🔥 เปิด PDF + สั่งปริ้น
                        const pdfUrl = `/receipts/${data.bill_id}/pdf`;
                        const printWindow = window.open(pdfUrl, '_blank');

                        printWindow.onload = function() {
                            printWindow.focus();
                            printWindow.print();
                        };

                        // reset หน้าจอ
                        items = [];
                        tableBody.innerHTML = '';
                        paidAmountInput.value = '';
                        changeAmountInput.value = '';
                        updateTotal();

                    } else if (data.error) {
                        errorMsg.textContent = data.error;
                    }
                });
        });
    </script>
    <script>
        document.getElementById('member_code').addEventListener('blur', function() {
            const code = this.value.trim();
            if (!code) return;

            fetch(`/members/find/${code}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) {
                        member_error.textContent = data.error;
                        member_error.classList.remove('d-none');
                        member_info.classList.add('d-none');
                        member_id.value = '';
                    } else {
                        member_info.textContent =
                            `ชื่อ ${data.name} | เครดิต ${data.credit} บาท`;
                        member_info.classList.remove('d-none');
                        member_error.classList.add('d-none');
                        member_id.value = data.id;
                    }
                });
        });
    </script>

@endsection

@section('mobile-content')

    <style>
        .scan-icon-mobile {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 22px;
            color: #777;
            cursor: pointer;
        }

        .mobile-cashier-input {
            margin-bottom: 15px;
        }

        .mobile-table td,
        .mobile-table th {
            font-size: 14px;
            padding: 8px;
        }

        .mobile-btn {
            width: 100%;
            margin-top: 5px;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <div class="container p-3 bg-white rounded-4">

        <h3 class="text-center mb-3">Cashier</h3>

        {{-- ค้นหาและเพิ่มสินค้า --}}
        <div class="mobile-cashier-input position-relative">
            <input list="stockList" id="searchProductMobile" class="form-control ps-3 pe-5"
                placeholder="สแกนบาร์โค้ด หรือ ค้นหาสินค้า...">
            <i class="bi bi-upc-scan scan-icon-mobile"></i>

            <datalist id="stockList">
                @foreach ($stocks as $stock)
                    <option value="{{ $stock->barcode_unit }}" data-id="{{ $stock->id }}"
                        data-category="{{ $stock->category }}" data-price="{{ $stock->price ?? 0 }}">
                @endforeach
            </datalist>
        </div>

        <div class="mobile-cashier-input">
            <input type="number" id="quantityAddMobile" class="form-control" placeholder="จำนวน">
        </div>

        <button class="btn btn-success mobile-btn" id="addProductBtnMobile">เพิ่ม</button>

        {{-- ตารางสินค้า (แบบ list สำหรับมือถือ) --}}
        <div class="mt-3">
            <div id="mobileCashierList"></div>
        </div>

        {{-- ช่องชำระเงิน --}}
        <div class="mobile-cashier-input mt-3">
            <input type="number" id="paidAmountMobile" class="form-control" placeholder="เงินที่ชำระ" min="0">
        </div>

        <div class="mobile-cashier-input">
            <input type="text" id="changeAmountMobile" class="form-control" placeholder="เงินทอน" readonly>
        </div>

        <button id="saveBillBtnMobile" class="btn btn-primary mobile-btn mt-2">บันทึกบิล</button>

        <div id="errorMsgMobile" class="mt-2 text-danger"></div>
        <div id="successMsgMobile" class="mt-2 text-success"></div>
    </div>

    <script>
        // ใช้ stocks เหมือน desktop
        const searchInputMobile = document.getElementById('searchProductMobile');
        const quantityInputMobile = document.getElementById('quantityAddMobile');
        const addBtnMobile = document.getElementById('addProductBtnMobile');
        const mobileList = document.getElementById('mobileCashierList');
        const paidInputMobile = document.getElementById('paidAmountMobile');
        const changeInputMobile = document.getElementById('changeAmountMobile');
        const saveBtnMobile = document.getElementById('saveBillBtnMobile');
        const errorMsgMobile = document.getElementById('errorMsgMobile');
        const successMsgMobile = document.getElementById('successMsgMobile');

        let selectedProductMobile = null;
        let itemsMobile = [];

        // เลือกสินค้า
        searchInputMobile.addEventListener('input', function() {
            selectedProductMobile = findProduct(this.value);
        });

        // รองรับ Enter
        searchInputMobile.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBtnMobile.click();
            }
        });

        // เพิ่มสินค้า
        addBtnMobile.addEventListener('click', function() {
            const qty = parseInt(quantityInputMobile.value);
            if (!selectedProductMobile) {
                alert('กรุณาเลือกสินค้า');
                return;
            }
            if (!qty || qty < 1) {
                alert('กรุณากรอกจำนวน');
                return;
            }

            const existIndex = itemsMobile.findIndex(i => i.stock_id === selectedProductMobile.id);
            if (existIndex > -1) {
                itemsMobile[existIndex].quantity += qty;
            } else {
                itemsMobile.push({
                    stock_id: selectedProductMobile.id,
                    quantity: qty,
                    price: selectedProductMobile.price
                });
            }

            renderMobileList();
            selectedProductMobile = null;
            searchInputMobile.value = '';
            quantityInputMobile.value = '';
            updateMobileChange();
        });

        function renderMobileList() {
            mobileList.innerHTML = '';
            itemsMobile.forEach((item, index) => {
                const div = document.createElement('div');
                div.classList.add('border', 'p-2', 'mb-2', 'rounded');
                div.innerHTML = `
                <strong>${getStockName(item.stock_id)}</strong> (${getStockCategory(item.stock_id)})
                <div>จำนวน: ${item.quantity}</div>
                <div>ราคาต่อหน่วย: ${item.price}</div>
                <div>รวม: ${item.quantity * item.price}</div>
                <div class="mt-1">
                    <button class="btn btn-sm btn-primary increaseBtnMobile">+</button>
                    <button class="btn btn-sm btn-warning decreaseBtnMobile">-</button>
                </div>
            `;
                mobileList.appendChild(div);

                div.querySelector('.increaseBtnMobile').addEventListener('click', () => {
                    item.quantity++;
                    renderMobileList();
                    updateMobileChange();
                });
                div.querySelector('.decreaseBtnMobile').addEventListener('click', () => {
                    item.quantity--;
                    if (item.quantity <= 0) {
                        itemsMobile.splice(index, 1);
                    }
                    renderMobileList();
                    updateMobileChange();
                });
            });
        }

        function updateMobileChange() {
            const total = itemsMobile.reduce((sum, i) => sum + i.quantity * i.price, 0);
            const paid = parseFloat(paidInputMobile.value) || 0;
            changeInputMobile.value = paid >= total ? paid - total : 0;
        }

        paidInputMobile.addEventListener('input', updateMobileChange);

        function getStockName(id) {
            return stocks.find(s => s.id === id)?.name || '';
        }

        function getStockCategory(id) {
            return stocks.find(s => s.id === id)?.category || '';
        }

        saveBtnMobile.addEventListener('click', function() {
            errorMsgMobile.textContent = '';
            successMsgMobile.textContent = '';

            const total = itemsMobile.reduce((sum, i) => sum + i.quantity * i.price, 0);
            const paid = parseFloat(paidInputMobile.value) || 0;

            if (itemsMobile.length === 0) {
                errorMsgMobile.textContent = 'ไม่มีสินค้าในบิล';
                return;
            }
            if (paid < total) {
                errorMsgMobile.textContent = 'จำนวนเงินที่จ่ายไม่เพียงพอ';
                return;
            }

            fetch("{{ route('cashier.add') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        items: itemsMobile,
                        paid_amount: paid
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        successMsg.textContent = data.success;

                        // 🔥 เปิด PDF + สั่งปริ้น
                        const pdfUrl = `/receipts/${data.bill_id}/pdf`;
                        const printWindow = window.open(pdfUrl, '_blank');

                        printWindow.onload = function() {
                            printWindow.focus();
                            printWindow.print();
                        };

                        // reset หน้าจอ
                        items = [];
                        tableBody.innerHTML = '';
                        paidAmountInput.value = '';
                        changeAmountInput.value = '';
                        updateTotal();

                    } else if (data.error) {
                        errorMsg.textContent = data.error;
                    }
                });
        });
    </script>

@endsection

<!-- Modal เพิ่มสมาชิก -->
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">เพิ่มสมาชิกใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="modal_member_code">

                <div class="mb-3">
                    <label>ชื่อ–นามสกุล</label>
                    <input type="text" id="modal_member_name" class="form-control">
                </div>

                <div class="mb-3">
                    <label>ประเภท</label>
                    <select id="modal_member_type" class="form-select">
                        <option value="student">นักเรียน</option>
                        <option value="teacher">ครู</option>
                    </select>
                </div>

                <div id="modal_error" class="text-danger"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="saveMemberBtn">
                    บันทึกสมาชิก
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('saveMemberBtn').addEventListener('click', function() {

        const code = document.getElementById('modal_member_code').value;
        const name = document.getElementById('modal_member_name').value;
        const type = document.getElementById('modal_member_type').value;

        fetch("{{ route('members.quickCreate') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    member_code: code,
                    name: name,
                    type: type
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(
                        document.getElementById('memberModal')
                    ).hide();

                    // ใส่ member_code กลับไป
                    document.getElementById('member_code').value = code;

                    // กดบันทึกบิลซ้ำอัตโนมัติ
                    document.getElementById('saveBillBtn').click();
                }
            });
    });
</script>
