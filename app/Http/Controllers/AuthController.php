<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    /**
     * AuthController constructor.
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
    public function login(Request $request): JsonResponse
    {
        $data = AuthService::getInstance()->attemptLogin($request);

        return $this->sendResponse($data, 'login');
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return $this->sendSuccessResponse(null, trans('auth.logout_success'));
    }
}
