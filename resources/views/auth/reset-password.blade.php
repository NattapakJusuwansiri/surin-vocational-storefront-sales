<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/reset-password.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="body-bg">
    <div class="container d-flex flex-column justify-content-center align-items-center">
        <div class="header-text">
            เปลี่ยนรหัสผ่าน
        </div>
        <div class="p-3 container">
            <form method="POST" action="{{ route('password.update') }}"
                class="row g-3 mx-3 d-flex flex-column justify-content-center align-items-center">
                @csrf
                <div class="form-bg col-md-10">

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- แถวแรก -->
                    <div class="row mt-2">
                        <div class="col-12 col-md-6">
                            <label for="Username" class="form-label">Username</label>
                            <input type="text" name="Username" id="Username" class="form-control"
                                value="{{ session('username') }}" {{ session('first_login') ? 'readonly' : '' }}
                                required>


                        </div>
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                value="{{ session('email') }}" {{ session('first_login') ? 'readonly' : '' }} required>
                        </div>
                    </div>

                    <!-- แถวสอง -->
                    <div class="row mt-2">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">รหัสผ่านใหม่ (ต้องไม่ต่ำกว่า 9 ตัว)</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="confirm" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="password_confirmation" id="confirm" class="form-control"
                                required>
                        </div>
                    </div>

                    <!-- ปุ่ม -->
                    <div class="row mt-2">
                        <div class="col-12 col-md-12 justify-content-center align-items-center my-2x">
                            <button type="submit" class="btn btn-primary w-100 p-2">
                                แก้ไขรหัสผ่าน
                                {{-- <img src="../img/register/Button.png" alt="button" class="img-fluid w-100"> --}}
                            </button>
                        </div>
                        <div class="d-flex justify-content-center mt-2">
                            ต้องการ<a href="/login" class="text-danger text-decoration-none">เข้าสู่ระบบ</a>?
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'ตกลง'
            });
        </script>
    @endif
