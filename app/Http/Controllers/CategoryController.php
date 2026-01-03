<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $authService;

    /**
     * CategoryController constructor.
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
        $data = CategoryService::getInstance()->store($request->all());

        return $this->sendResponse($data, 'store');
    }

    public function index(Request $request): JsonResponse
    {
        $data = CategoryService::getInstance()->index($request);

        return $this->sendResponse($data, 'index');
    }

    public function update(Request $request): JsonResponse
    {
        $data = CategoryService::getInstance()->update($request, $request->id);

        return $this->sendResponse($data, 'update');
    }

    public function destroy($id): JsonResponse
    {
        $data = CategoryService::getInstance()->delete($id);

        return $this->sendResponse($data, 'delete');
    }
}
