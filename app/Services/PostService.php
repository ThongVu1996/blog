<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Models\Post;
use App\Services\BaseService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostService extends BaseService
{
    protected $auth;


    protected $responseHelper;

    public function __construct(ResponseHelper $responseHelper) {
        $this->auth = auth('api'); 
        $this->responseHelper = $responseHelper;
    }

    /**
     * @return Eloquent | QueryBuilder
     */
    public function makeNewQuery()
    {
    }

    /**
     * store
     */
    public function store(Request $request): Post
    {
        $data = $request->all();

        // 1. Kiểm tra xem request có file 'image' hay không
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('', 'public');
            $data['image'] = $path;
        }
        // dd($data);?
        return Post::create($data);
    }

    public function update(Request $request, $id): bool
    {
        $post = Post::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('image')) {
        // Xóa ảnh cũ nếu tồn tại
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            // Lưu ảnh mới
            $data['image'] = $request->file('image')->store('','public');
        }
        return $post->update($data);
    }
    /**
     * index
     */
    public function index(Request $request)
    {
        $categoryId = $request->query('category_id');
        $query = Post::orderBy('created_at', 'desc');
        if ($categoryId) {
            return $query->where('category_id', $categoryId)->get(); 
        }

        return $query->limit(5)->get();
    }

    public function detail($slug)
    {
        $post = Post::where('slug', $slug)->with('category')->first();
        if (!$post) {
            return ['message' => 'Post not found', 404];
        }   
        return $post;
    }

    public function show($id)
    {
        $post = Post::where('id', $id)->first();
        if (!$post) {
            return ['message' => 'Post not found', 404];
        }   
        return $post;
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $post = Post::find($id);
            if (!$post) {
                return ['message' => 'Post not found', 404];
            }   
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->delete();
            DB::commit();
            return true;
        } 
        catch (\Exception $e) {
            DB::rollBack();
            return ['message' => 'Error deleting post', 500];
        }
    }
}
