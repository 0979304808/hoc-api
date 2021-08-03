<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegister;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
;
class AuthController extends Controller
{
    protected $user;
    public $name;
    public function __construct()
    {
        $this->user = new User();
    }
    
    // Đăng Ký ( tạo token  )
    public function register(AuthRegister $request)
    {
        $validated = $request->validated();
        $validated['token'] = Str::random(40);
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        return response()->json(['user'=>$user,'token'=>$user->token],201);
    }

    // Xóa tài khoản

    public function delete(Request $request,User $user) {
        $token = $this->user->checkToken($request);
        $user_id = $this->user->LayId($request);
        if ($token){
            if ($user_id == $user->id){
                $user->delete();
                return response()->json(['msg'=>'Xóa tài khoản thành công'],200);
            }
        }
        return response()->json(['msg'=>'Bạn không có quyền truy cập'],401);
    }

    // Lấy thông tin của toi
    public function me(Request $request) {
        $token = $request->header('token');
        if (!$token){
            return response()->json(['msg'=>'Unauthorized'],401);
        }
        $token = User::where('token',$token)->first();
        if (empty($token)) {
            return response()->json(['msg'=>'Unauthorized'],401);
        }
        return response()->json(['token'=>$token],200);
    }


}
