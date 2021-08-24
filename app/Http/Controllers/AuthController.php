<?php

namespace App\Http\Controllers;

use App\Events\NewEvent;
use App\Events\NewNotification;
use App\Http\Requests\AuthRegister;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\TraitsData;

class AuthController extends Controller
{
    use TraitsData;

    // Đăng Ký ( tạo token  )
    public function register(AuthRegister $request)
    {
        DB::transaction(function () use ($request) {
            $validated = $request->validated();
            $validated['token'] = Str::random(40);
            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);
            if ($user){
                event(new NewEvent($user)); // gửi maill. gửi thông báo chúc mừng đến tài khoản
            }
            return response()->json(['status' => 201, 'data' => $user, 'token' => $user->token], 201);
        });

    }

    // Xóa tài khoản
    public function delete(Request $request, User $user)
    {
        $user = $this->CheckToken($request);
        if (isset($user)) {
            if ($user->id == $user->id) {
                $user->delete();
                return success('Xoa tai khoan thanh cong', 200);
            }
        }
        return error('khong co quyen truy cap', 401);
    }

    // Lấy thông tin của toi
    public function me(Request $request)
    {
        $user = $this->CheckToken($request);
        if (!$user) {
            return error('khong co quyen truy cap', 401);
        }
        return success($user, 200);
    }
}
