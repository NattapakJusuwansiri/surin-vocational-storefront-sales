<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page.css') }}">
</head>

<body class="body-bg">

    @php
        $path = request()->path();
        $tokenData = null;
        if (session('token')) {
            $tokenData = json_decode(\Illuminate\Support\Facades\Crypt::decryptString(session('token')), true);
        }
    @endphp

    <!-- ====================== Navbar (แก้ใหม่) ====================== -->
    <nav class="navbar navbar-expand-lg navbar-dark sidebar-bg px-4 py-3">

        <div class="container-fluid">

            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="{{ url('../img/page/Object-1.png') }}" alt="Coin" class="img-fluid" style="height:40px;">
                <div class="d-flex flex-column lh-sm">
                    <small>ระบบจัดการ</small>
                    <strong>สต็อกสินค้า</strong>
                </div>
            </a>

            <!-- Hamburger Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Avatar (ไม่ถูกย่อ) -->
            <div class="d-lg-none d-block ms-auto me-2">
                <a class="avatar" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-3 text-white"></i>
                </a>
            </div>

            <!-- Menu -->
            <div class="collapse navbar-collapse" id="navbarMenu">

                <ul class="navbar-nav me-auto align-items-center mt-3 mt-lg-0">

                    <li class="nav-item">
                        <a href="/cashier" class="nav-link {{ Str::contains($path, 'cashier') ? 'active' : '' }}">
                            ออกใบเสร็จ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/show-stock" class="nav-link {{ Str::contains($path, 'show-stock') ? 'active' : '' }}">
                            รายการสินค้า
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/report" class="nav-link {{ Str::contains($path, 'report') ? 'active' : '' }}">
                            สรุปยอดขาย
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/receipts" class="nav-link {{ Str::contains($path, 'receipts') ? 'active' : '' }}">
                            รายการใบเสร็จ
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="/documents" class="nav-link {{ Str::contains($path, 'documents') ? 'active' : '' }}">
                            ออกเอกสาร
                        </a>
                    </li>
                </ul>

                <!-- Avatar (เฉพาะ Desktop) -->
                <ul class="navbar-nav ms-auto align-items-center d-none d-lg-flex">
                    <li class="nav-item dropdown">
                        <a class="avatar" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-4 text-white"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if (session('token'))
                                <li class="dropdown-item-text d-flex gap-2 align-items-center">
                                    <i class="bi bi-person-circle"></i>
                                    <span>{{ $tokenData['name'] }}</span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="dropdown-item">ออกจากระบบ</button>
                                    </form>
                                </li>
                            @else
                                <li><a class="dropdown-item" href="/login">Login</a></li>
                                <li><a class="dropdown-item" href="/register">Register</a></li>
                            @endif
                        </ul>
                    </li>
                </ul>

            </div>

        </div>
    </nav>

    <!-- ====================== MAIN ====================== -->
    <div class="px-4 flex-grow-1">

        <div class="mobile-only content-trash-bg rounded-3 mt-4">
            <div class="container py-3">
                <div class="bg-white bg-opacity-75 p-3 rounded-5 px-5 shadow-sm">
                    @yield('mobile-content')
                </div>
            </div>
        </div>

        <div class="container-fluid desktop-only mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="content-bg bg-opacity-75 p-3 px-5 rounded-5 shadow-sm">
                        @yield('desktop-content')
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>

</html>
