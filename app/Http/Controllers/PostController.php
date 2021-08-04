<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use App\Http\Requests\PostCreate;
use App\Http\Requests\PostUpdate;
use App\Http\Resources\PostResource;
use App\Posts;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Lấy ra tất cả danh sách bài đăng
    public function index(Request $request)
    {
        $check = $request->all();
        $limit = $request->get('limit');
        $sort = $request->get('sort');
        $search = $request->get('search');
        if (!$limit){
            $limit = null ;
        }
        if (!$sort){
            $sort = 'asc' ;
        }
        if (!$search){
            $search = null ;
        }
        $post = Posts::where('title','like','%'.$search.'%')->orderBy('id',$sort)->paginate($limit);
        return response()->json(['status' => 200, 'data' => PostResource::collection($post) ],200);
    }

    // Tạo bài viết ( chỉ tạo được khi có token )
    public function store(PostCreate $request)
    {
        $user = Helper::checktoken($request);
        $file = $request->file('image');
        if(isset($user)){
            $validated = $request->validated();
            $validated['user_id'] = $user->id ;
            $validated['image'] = time().'_'.$file->getClientOriginalName();
            Helper::uploadFile($file); // upload file vao public/uploads
            $post = Posts::create($validated);
            return response()->json(['status'=>201, 'data' => new PostResource($post)],201);
        }else{
            return response()->json(['status' => 401,'msg' => 'Ban khong co quyen truy cap'],401);
        }

    }


    // Lấy ra 1 bài viết
    public function show(Posts $post)
    {
        return response()->json(['status'=>200, 'data' => new PostResource($post)],200);
    }


    // Cập nhật bài viết ( chỉ người đăng mới có quyền sửa )
    public function update(PostUpdate $request, Posts $post)
    {
        $file = $request->file('image');
        $user = Helper::checktoken($request);
        if(isset($user)){
            if ($user->id == $post->user_id){
                $validated = $request->validated();
                $validated['image'] = time().'_'.$file->getClientOriginalName();
                Helper::deleteFile($post->image); // Xóa file cũ
                Helper::uploadFile($file); // upload file
                $post->update($validated);
                return response()->json(['status'=>200,'data' => new PostResource($post)],200);
            }else {
                return response()->json(['status'=>401,'msg' => 'Ban khong co quyen truy cap'],401);
            }
        }else{
            return response()->json(['status'=>401,'msg' => 'Ban khong co quyen truy cap'],401);
        }
    }

    // Xóa bài viết ( chỉ người đăng mới có quyền xóa )
    public function destroy(Request $request,Posts $post)
    {
        $user = Helper::checktoken($request);
        if(isset($user)){
            if ($user->id == $post->user_id){
                Helper::deleteFile($post->image);
                $post->delete();
                return response()->json(['status'=>200, 'msg' => 'Xóa thành công'],200);
            }
        }
        return response()->json(['status'=>401,'msg' => 'Ban khong co quyen truy cap'],401);

    }
}
