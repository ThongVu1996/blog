<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    const STATUS_CODE_SUCCESS = 200;
    const STATUS_CODE_BAD_REQUEST = 400;
    const STATUS_CODE_UNAUTHORIZED = 401;
    const STATUS_CODE_FORBIDDEN = 403;
    const STATUS_CODE_NOT_FOUND = 404;
    const STATUS_CODE_VALIDATE_ERROR = 422;
    const STATUS_CODE_SERVER_ERROR = 500;
    const HTTP_TOO_MANY_REQUESTS = 429;

    /**
     * @return JsonResponse
     */
    public static function sendResponse($code, $message, $data = null, $errors = null): JsonResponse
    {
        return response()->json([
            'status_code' => $code,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    /**
     * sendError
     *
     * @param  string $message
     * @param  string $code
     * @return array
     */
    public static function sendError(string $message, $code = self::STATUS_CODE_BAD_REQUEST): array
    {
        return [
            'message' => $message,
            'status_code' => $code,
        ];
    }

    /**
     * sendErrorResponse
     *
     * @param  mixed $message
     * @param  mixed $errors
     * @param  mixed $code
     * @return JsonResponse
     */
    public static function sendErrorResponse($message, $errors = null, $code = self::STATUS_CODE_BAD_REQUEST): JsonResponse
    {
        return self::sendResponse($code, $message, null, $errors);
    }

    /**
     * @param $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function sendSuccessResponse($data, $message = '', $code = self::STATUS_CODE_SUCCESS): JsonResponse
    {
        return self::sendResponse($code, $message, $data);
    }

    /**
     * @return JsonResponse
     */
    public static function sendJsonResponse($data): JsonResponse
    {
        return response()->json($data);
    }
}
