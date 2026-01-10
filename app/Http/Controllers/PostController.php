<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    protected $authService;

    /**
     * PostController constructor.
     */
    public function __construct(AuthService $authService, ResponseHelper $responseHelper)
    {
        $this->authService = $authService;
        $this->responseHelper = $responseHelper; // Gán giá trị ở đây
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $res = PostService::getInstance()->store($request);

        return $this->sendResponse($res, 'store');
    }

    public function index(Request $request): JsonResponse
    {
        $data = PostService::getInstance()->index($request);

        return $this->sendResponse($data, 'index');
    }

    public function update(Request $request): JsonResponse
    {
        $data = PostService::getInstance()->update($request, $request->id);

        return $this->sendResponse($data, 'update');
    }

    /**
     * Lấy chi tiết bài viết và ghi nhận lượt xem
     * 
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function detail($slug, Request $request): JsonResponse
    {
        // Bước 1: Ghi nhận view (có IP check)
        PostService::getInstance()->recordView($slug, $request);
        
        // Bước 2: Lấy detail
        $data = PostService::getInstance()->detail($slug);
        
        return $this->sendResponse($data, 'detail');
    }

    /**
     * Lấy danh sách trending posts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function trending(Request $request): JsonResponse
    {
        $data = PostService::getInstance()->getTrending($request);
        return $this->sendResponse($data, 'trending');
    }

    /**
     * Lấy danh sách featured posts
     * 
     * @return JsonResponse
     */
    public function featured(): JsonResponse
    {
        $data = PostService::getInstance()->getFeatured();
        return $this->sendResponse($data, 'featured');
    }

    public function show($id): JsonResponse
    {
        $data = PostService::getInstance()->show($id);

        return $this->sendResponse($data, 'show');
    }

    public function destroy($id): JsonResponse
    {
        $data = PostService::getInstance()->destroy($id);

        return $this->sendResponse($data, 'show');
    }
}
