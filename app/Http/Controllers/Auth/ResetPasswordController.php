<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /**
     * แสดงหน้าเปลี่ยนรหัสผ่าน
     */
    public function showForm()
    {
        return view('auth.reset-password'); // หรือเปลี่ยน path ตามที่คุณวางไฟล์ view ไว้
    }

    /**
     * ประมวลผลการเปลี่ยนรหัสผ่าน
     */
    public function reset(Request $request)
    {
        $request->validate([
            'Username' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:9|confirmed',
        ], [
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 9 ตัวอักษร',
            'password.confirmed' => 'รหัสผ่านไม่ตรงกัน',
        ]);

        // ค้นหาผู้ใช้จาก username และ email
        $user = User::where('username', $request->Username)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return back()->withErrors(['Username' => 'ไม่พบผู้ใช้ที่ตรงกับข้อมูลที่ระบุ']);
        }

        // อัปเดตรหัสผ่านใหม่ (Hash ก่อนบันทึก)
        $user->update([
            'password_changed' => true,
            'password' => Hash::make($request->password),
        ]);


        return redirect('/login')->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }
}
