<?php
namespace App\Helper;

use App\User;
use Illuminate\Support\Facades ;
class Helper {

    // Check Token
    public static function checktoken($token){
        $token = request()->header('token');
        if ($token){
            $user = User::where('token',$token)->first();
            if ($user){
                return $user ;
            }
        }
    }

    // Upload file
    public static function uploadFile($file){
        $path = 'uploads';
        $file_name = time().'_'.$file->getClientOriginalName() ;
        if (Facades\File::exists($path.'/'.$file_name)){
            return response()->json(['status'=>403,'msg' => 'File da ton tai'],403);
        }
        return $file->move($path, $file_name);
    }

    // Delete file
    public static function deleteFile($file){
        if (Facades\File::exists('uploads/'.$file)){
            return Facades\File::delete('uploads/'.$file);
        }
    }
}