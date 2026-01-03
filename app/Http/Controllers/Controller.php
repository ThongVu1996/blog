<?php
namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $responseHelper;

    /**
     * constructor
     *
     */
    public function __construct()
    {
        $this->responseHelper = new ResponseHelper;
        $this->middleware('logRequest');
    }

    /**
     * @var string
     */
    protected $guard;

    /**
     * Get the guest middleware for the application.
     *
     * @return string
     */
    public function guestMiddleware()
    {
        $guard = $this->getGuard();
        return $guard ? ('guest:' . $guard) : 'guest';
    }

    /**
     * Get the auth middleware for the application.
     *
     * @return string
     */
    public function authMiddleware()
    {
        $guard = $this->getGuard();
        return $guard ? ('auth:' . $guard) : 'auth';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return string
     */
    protected function getGuard()
    {
        return property_exists($this, 'guard') ? $this->guard : config('auth.defaults.guard');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->getGuard());
    }

    /**
     * Send Error Response
     *
     * @param string $message
     * @param mixed $errors
     * @param integer $code
     * @return JsonResponse
     */
    protected function sendErrorResponse($message, $errors = null, $code = ResponseHelper::STATUS_CODE_BAD_REQUEST): JsonResponse
    {
        // dd($message, $errors, $code);
        return $this->responseHelper->sendResponse($code, $message, null, $errors);
    }

    /**
     * Send Success Response
     *
     * @param $data
     * @param string|null $message
     * @param int|null $code
     * @return JsonResponse
     */
    protected function sendSuccessResponse(
        $data,
        string | null $message = null,
        int | null $code = ResponseHelper::STATUS_CODE_SUCCESS
    ): JsonResponse {
        return $this->responseHelper->sendResponse($code, $message, $data);
    }

    /**
     * sendResponse
     *
     * @param $data
     * @param string $type
     * @param string|null $message
     * @param int|null $code
     * @return JsonResponse
     */
    protected function sendResponse($data, string $type = 'list', string|null $message = null, int|null $code = null): JsonResponse
    {
        if (data_get($data, 'message')) {
            return $this->sendErrorResponse($data['message'], '', data_get($data, 'status_code'));
        }
 
        return $this->sendSuccessResponse(
            $data,
            $message ?? trans('response.' . $type . '_success'),
            $code ?? ResponseHelper::STATUS_CODE_SUCCESS
        );
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getParamRequest(Request $request): array
    {
        $search  = data_get($request, 'search');
        $perPage = data_get($request, 'per_page');
        $orders  = data_get($request, 'orders');
        $filters = data_get($request, 'filters');
        $all     = data_get($request, 'all');

        return ["search" => $search, "orders" => $orders, "filters" => $filters, "perPage" => $perPage, "all" => $all];
    }
}
