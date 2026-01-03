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
     * @return JsonResponse
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

    public function detail($slug): JsonResponse
    {
        $data = PostService::getInstance()->detail($slug);

        return $this->sendResponse($data, 'detail');
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
