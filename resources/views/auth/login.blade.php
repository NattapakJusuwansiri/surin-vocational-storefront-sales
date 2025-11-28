<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="d-flex justify-content-center align-items-center w-100 h-100">

        <div class="login-img">
            <h1>ลงชื่อเข้าใช้</h1>

            <div class="text-center login-card">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- ชื่อผู้ใช้งาน --}}
                    <div class="mb-3 text-start">
                        <div class="input-group">
                            <input type="text" name="username" class="form-control" placeholder="Username"
                                id="username" required>
                        </div>
                    </div>

                    {{-- รหัสผ่าน --}}
                    <div class="mb-3 text-start">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="Password" required>
                            <button type="button" class="btn" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    {{-- ลืมรหัสผ่าน --}}
                    <div class="d-flex justify-content-end text-white-50 mb-3">
                        <a href="/reset-password" class="text-info text-decoration-none">ลืมรหัสผ่าน</a>
                    </div>

                    {{-- ปุ่มเข้าสู่ระบบ --}}
                    <div>
                        <button type="submit" class="btn-login-img">
                            <img src="{{ asset('img/Login/Login-Button.png') }}" alt="เข้าสู่ระบบ"
                                class="img-fluid w-100">
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    {{-- Toggle Password --}}
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        togglePassword.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            togglePassword.innerHTML = type === 'password' ?
                '<i class="bi bi-eye"></i>' :
                '<i class="bi bi-eye-slash"></i>';
        });
    </script>
</body>

</html>
