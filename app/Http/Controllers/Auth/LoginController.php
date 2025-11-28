<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;

class LoginController extends Controller
{
    // แสดงหน้า login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ตรวจสอบการล็อกอิน
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            $user = Auth::user();

            // ✅ ตรวจสอบว่าผู้ใช้นี้เปลี่ยนรหัสผ่านแล้วหรือยัง
            // if (!$user->password_changed) {
            //     // logout ชั่วคราว (ป้องกัน session ค้าง)
            //     Auth::logout();

            //     // ส่ง username และ email ไปยังหน้า reset-password
            //     return redirect()->route('password.reset')
            //         ->with([
            //             'username' => $user->username,
            //             'email' => $user->email,
            //             'first_login' => true,
            //         ]);
            // }

            // ถ้าเคยเปลี่ยนแล้ว → เข้าปกติ
            $sessionKey = Str::random(20);
            $tokenData = [
                'userId' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'session_key' => $sessionKey,
                'login_at' => now()->toDateTimeString(),
            ];

            $encryptedToken = Crypt::encryptString(json_encode($tokenData));
            session(['token' => $encryptedToken]);

            return redirect('/show-stock');
        }

        return back()->with('error', 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
    }

    // ออกจากระบบ
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
