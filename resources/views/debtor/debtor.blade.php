@extends('layout.layout')
@section('title', 'รายการลูกหนี้')

@section('desktop-content')

    <div class="d-flex align-items-center flex-column text-white">
        <h1 class="text-center p-2">รายการลูกหนี้</h1>
    </div>

    <div class="container p-5 bg-white rounded-5">

        {{-- Search + Export --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <form method="GET" class="d-flex">
                    <input type="search" name="search" value="{{ request('search') }}"
                        class="form-control form-control-sm me-2" placeholder="ค้นหาลูกหนี้..." style="width:auto;">
                    <button type="submit" class="btn btn-primary btn-sm">ค้นหา</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ชื่อลูกหนี้</th>
                        <th>ยอดเงินที่ค้าง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $member->name }} <br>
                                <small class="text-muted">
                                    รหัส: {{ $member->member_code }} ({{ $member->type }})
                                </small>
                            </td>
                            <td class="text-danger fw-bold">
                                {{ number_format($member->credit_balance, 2) }} บาท
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success pay-btn" data-bs-toggle="modal"
                                    data-bs-target="#payDebtModal" data-id="{{ $member->id }}"
                                    data-name="{{ $member->name }}" data-credit="{{ $member->credit_balance }}">
                                    <i class="bi bi-cash-stack"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">ไม่มีลูกหนี้</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection

<!-- Modal ชำระหนี้ -->
<div class="modal fade" id="payDebtModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="payDebtForm" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cash-stack"></i> ชำระหนี้
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <strong>ลูกหนี้:</strong>
                    <span id="modalMemberName"></span>
                </div>

                <div class="mb-2">
                    <strong>ยอดค้าง:</strong>
                    <span class="text-danger fw-bold">
                        <span id="modalCredit"></span> บาท
                    </span>
                </div>

                <div class="mb-3">
                    <label class="form-label">จำนวนเงินที่ชำระ</label>
                    <input type="number" name="amount" id="payAmount" class="form-control" min="1" required>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    ยกเลิก
                </button>
                <button class="btn btn-success">
                    บันทึกการชำระ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelectorAll('.pay-btn').forEach(btn => {
            btn.addEventListener('click', function() {

                const memberId = this.dataset.id;
                const name = this.dataset.name;
                const credit = this.dataset.credit;

                document.getElementById('modalMemberName').textContent = name;
                document.getElementById('modalCredit').textContent = parseFloat(credit).toFixed(
                    2);

                const payInput = document.getElementById('payAmount');
                payInput.max = credit;
                payInput.value = credit;

                document.getElementById('payDebtForm').action =
                    `/debtor/pay/${memberId}`;
            });
        });

    });
</script>
