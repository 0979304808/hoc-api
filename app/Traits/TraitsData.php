<?php


namespace App\Traits;


use App\User;
use Illuminate\Support\Facades\File;

trait TraitsData {

    // Check Token
    public function CheckToken($token)
    {
        $token = request()->header('token');
        if ($token){
            $user = User::where('token',$token)->first();
            if (!empty($user)){
                return $user ;
            }
        }
    }

    // Upload file
    public function uploadFlie($file)
    {
        $file = request()->file('image');
        $path = 'image/posts';
        $url_image = asset($path).'/'.time().'_'.$file->getClientOriginalName() ;
        if (!File::exists($path.'/'.basename($url_image))){
            $file->move($path, $url_image);
            return $url_image;
        }
    }

    // Delete File
    public function deleteFile($file)
    {
        $path = 'image/posts';
        if (File::exists($path.'/'.basename($file))){
            File::delete($path.'/'.basename($file));
        }
    }
}