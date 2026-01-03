<?php

namespace App\Services;

use App\Helpers\ResponseHelper;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService extends BaseService
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
     * attemptLogin
     *
     * @param  mixed  $request
     */
    public function attemptLogin($request): ?array
    {
        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
             return ['message' => 'Sai email hoặc mật khẩu',
                'status_code' => 404,
            ]; // Lỗi Unauthorized
        }
        $token = $this->auth->login($user);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'status_code' => 200,
        ];
    }

    /**
     * logout
     */
    public function logout(): void
    {
        $this->auth->logout();
    }
}
