<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Http\Requests\AuthRegister;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
;
class AuthController extends Controller
{
    // Đăng Ký ( tạo token  )
    public function register(AuthRegister $request)
    {
        $validated = $request->validated();
        $validated['token'] = Str::random(40);
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        return response()->json(['status'=>201,'data'=>$user,'token'=>$user->token],201);
    }

    // Xóa tài khoản
    public function delete(Request $request,User $user) {
        $user = Helper::checktoken($request);
        if (isset($user)){
            if ($user->id == $user->id){
                $user->delete();
                return response()->json(['status'=>200,'msg'=>'Xóa tài khoản thành công'],200);
            }
        }
        return response()->json(['status'=>401,'msg'=>'Bạn không có quyền truy cập'],401);
    }

    // Lấy thông tin của toi
    public function me(Request $request) {
        $user = Helper::checktoken($request);
        if (!$user){
            return response()->json(['status'=>401,'msg'=>'Bạn không có quyền truy cập'],401);
        }
        return response()->json(['status'=>200,'data'=>$user],200);
    }


}
