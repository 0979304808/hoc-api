<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCreate;
use App\Http\Requests\PostUpdate;
use App\Http\Resources\PostResource;
use App\Posts;
use Illuminate\Http\Request;
use App\Traits\TraitsData;


class PostController extends Controller
{
    use TraitsData;

    // Lấy ra tất cả danh sách bài đăng
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $sort = $request->get('sort', 'asc');
        $search = $request->get('search', null);
        $post = Posts::where('title', 'like', '%' . $search . '%')->orderBy('id', $sort)->paginate($limit);
        return success(PostResource::collection($post), 200);
    }

    // Tạo bài viết ( chỉ tạo được khi có token )
    public function store(PostCreate $request)
    {
        $user = $this->CheckToken($request);
        if (!empty($user)) {
            $image = $this->uploadFlie($request);
            $validated = $request->validated();
            $validated['user_id'] = $user->id;
            $validated['image'] = $image;
            $post = Posts::create($validated);
            return success(new PostResource($post), 201);
        } else {
            return error('khong co quyen truy cap', 401);
        }

    }

    // Lấy ra 1 bài viết
    public function show(Posts $post)
    {
        return success(new PostResource($post), 200);
    }


    // Cập nhật bài viết ( chỉ người đăng mới có quyền sửa )
    public function update(PostUpdate $request, Posts $post)
    {
//        $file = $this->uploadFlie($request);
        $user = $this->CheckToken($request);
        if (isset($user)) {
            if ($user->id == $post->user_id) {
                $validated = $request->validated();
                $this->deleteFile($post->image); // Xóa file ảnh cũ
                $image = $this->uploadFlie($request); // Update file ảnh mới
                $validated['image'] = $image;
                $post->update($validated);
                return success(new PostResource($post), 200);
            } else {
                return error('khong co quyen truy cap', 401);
            }
        } else {
            return error('khong co quyen truy cap', 401);
        }
    }

    // Xóa bài viết ( chỉ người đăng mới có quyền xóa )
    public function destroy(Request $request, Posts $post)
    {
        $user = $this->CheckToken($request);
        if (isset($user)) {
            if ($user->id == $post->user_id) {
                $this->deleteFile($post->image); // Xóa ảnh trong file image/posts
                $post->delete();
                return success('Xoa thanh cong', 200);
            }
        }
        return error('khong co quyen truy cap', 401);
    }
}
