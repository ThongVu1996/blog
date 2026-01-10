<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Models\Post;
use App\Models\PostView;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostService extends BaseService
{
    protected $auth;

    protected $responseHelper;

    public function __construct(ResponseHelper $responseHelper)
    {
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

        return Post::create($data);
    }

    public function update(Request $request, $id): bool|array
    {
        try {
            Log::info('Debug Upload:', [
                'error_code' => $request->file('image') ? $request->file('image')->getError() : 'No file',
                'error_message' => $request->file('image') ? $request->file('image')->getErrorMessage() : 'No object',
                'post_size' => $_SERVER['CONTENT_LENGTH'] ?? 0,
            ]);
            $post = Post::find($id);
            if (! $post) {
                return ['message' => 'Post not found', 404];
            }
            $data = $request->all();
            Log::info('1', [1 => $request->hasFile('image'), 2 => $request->file('image')]);
            /* dd(1); */

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu tồn tại
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                }
                // Lưu ảnh mới
                $data['image'] = $request->file('image')->store('', 'public');
            }

            return $post->update($data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Bắt lỗi SQL (ví dụ cột content không chứa nổi đống text)
            Log::error('Lỗi lưu DB: ' . $e->getMessage());

            return ['error' => 'Nội dung quá dài hoặc định dạng không đúng', 402];
        } catch (\Throwable $e) {
            // Bắt tất cả các lỗi PHP khác (lỗi logic, lỗi bộ nhớ...)
            Log::error('Lỗi hệ thống: ' . $e->getMessage());

            return ['error' => 'Đã có lỗi xảy ra', 500];
        }
    }

    /**
     * Lấy danh sách posts với pagination
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        // Lấy parameters từ request
        $categoryId = $request->query('category_id');
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        
        // Build query
        $query = Post::with('category')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc');
        
        // Filter theo category nếu có
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Pagination
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Lấy danh sách trending posts dựa trên views
     * 
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrending(Request $request)
    {
        // Lấy parameters
        $limit = $request->query('limit', 5);
        $days = $request->query('days', 7);
        
        // Logic: Trending = posts có nhiều views nhất trong X ngày gần đây
        return Post::with('category')
            ->where('status', 'published')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('views_count', 'desc')
            ->orderBy('created_at', 'desc') // Nếu views bằng nhau, ưu tiên bài mới
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy danh sách featured posts theo thứ tự
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeatured()
    {
        // Logic: Lấy tối đa 3 posts được đánh dấu featured, sắp xếp theo featured_order
        return Post::with('category')
            ->where('status', 'published')
            ->where('is_featured', true)
            ->whereNotNull('featured_order')
            ->orderBy('featured_order', 'asc')
            ->limit(3)
            ->get();
    }

    /**
     * Ghi nhận lượt xem bài viết với chống spam IP
     * 
     * @param string $slug
     * @param Request $request
     * @return Post|array
     */
    public function recordView(string $slug, Request $request)
    {
        // Tìm post
        $post = Post::where('slug', $slug)->first();
        
        if (!$post) {
            return ['message' => 'Post not found', 404];
        }
        
        // Lấy thông tin request
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $timeWindow = 24; // hours - Chỉ tính 1 view/IP trong 24h
        
        // Kiểm tra xem IP này đã view bài này trong khoảng thời gian chưa
        $existingView = PostView::where('post_id', $post->id)
            ->where('ip_address', $ip)
            ->where('viewed_at', '>=', now()->subHours($timeWindow))
            ->exists();
        
        // Nếu chưa có view từ IP này trong 24h, ghi nhận view mới
        if (!$existingView) {
            // Tạo record trong bảng post_views
            PostView::create([
                'post_id' => $post->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'viewed_at' => now(),
            ]);
            
            // Tăng counter trong bảng posts
            $post->increment('views_count');
            
            // Refresh để lấy giá trị mới
            $post->refresh();
        }
        
        return $post;
    }

    public function detail($slug)
    {
        $post = Post::where('slug', $slug)->with('category')->first();
        if (! $post) {
            return ['message' => 'Post not found', 404];
        }

        return $post;
    }

    public function show($id)
    {
        $post = Post::where('id', $id)->first();
        if (! $post) {
            return ['message' => 'Post not found', 404];
        }

        return $post;
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $post = Post::find($id);
            if (! $post) {
                return ['message' => 'Post not found', 404];
            }
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $post->delete();
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            return ['message' => 'Error deleting post', 500];
        }
    }
}
