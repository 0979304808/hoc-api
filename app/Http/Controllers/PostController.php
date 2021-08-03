<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCreate;
use App\Http\Requests\PostUpdate;
use App\Http\Resources\PostResource;
use App\Permission;
use App\Posts;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    protected  $user;
    public function __construct()
    {
        $this->user = new User();
    }

    // Lấy ra tất cả danh sách bài đăng
    public function index(Request $request) //list danh sach tat ca post trang 1
    {
        $post = Posts::paginate();
        return response()->json(['data' => PostResource::collection($post)],200);
    }

    // Tạo bài viết ( chỉ tạo được khi có token )
    public function store(PostCreate $request)
    {
        $user = $this->user->checkToken($request);
        $user_id = $this->user->LayId($request);
        if($user){
            $validated = $request->validated();
            $validated['user_id'] = $user_id ;
            $file = $request->file('image');
            $validated['image'] = $file->getClientOriginalName();
            $destinationPath = 'uploads';
            $file->move($destinationPath,$file->getClientOriginalName());
            $post = Posts::create($validated);
            return response()->json(['data' => new PostResource($post)],201);
        }else{
            return response()->json(['msg' => 'Ban khong co quyen truy cap'],401);
        }

    }


    // Lấy ra 1 bài viết
    public function show(Posts $post)
    {
        return response()->json(['data' => new PostResource($post)],200);
    }


    // Cập nhật bài viết ( chỉ người đăng mới có quyền sửa )
    public function update(PostUpdate $request, Posts $post)
    {
        $user = $this->user->checkToken($request);
        $user_id = $this->user->LayId($request);
        if($user){
            if ($user_id == $post->user_id){
                $validated = $request->validated();
                $file = $request->file('image');
                $validated['image'] = $file->getClientOriginalName();
                $destinationPath = 'uploads';
                $file->move($destinationPath,$file->getClientOriginalName());
                $post->update($validated);
                return response()->json(['data' => new PostResource($post)],200);
            }else {
                return response()->json(['msg' => 'Ban khong co quyen truy cap'],401);
            }
        }else{
            return response()->json(['msg' => 'Ban khong co quyen truy cap'],401);
        }
    }

    // Xóa bài viết ( chỉ người đăng mới có quyền xóa )
    public function destroy(Request $request,Posts $post)
    {
        $user = $this->user->checkToken($request);
        $user_id = $this->user->LayId($request);
        if ($user){
            if ($user_id == $post->user_id){
                $post->delete();
                return response()->json(['msg' => 'Xóa thành công'],200);
            }
        }
        return response()->json(['msg' => 'Ban khong co quyen truy cap'],401);

    }
}
