<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\School\Models\SchoolRegistrationRequest;
use App\Modules\User\Actions\AdminLoginAction;
use App\Modules\User\Actions\LoginAction;
use App\Modules\User\Actions\RegisterAction;
use App\Modules\User\Actions\SendOtpAction;
use App\Modules\User\Actions\VerifyOtpAction;
use App\Modules\User\Requests\AdminLoginRequest;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Resources\UserResource;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Requests\SendOtpRequest;
use App\Modules\User\Requests\VerifyOtpRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterAction  $registerAction,
        private readonly LoginAction     $loginAction,
        private readonly AdminLoginAction $adminLoginAction,
        private readonly SendOtpAction   $sendOtpAction,
        private readonly VerifyOtpAction $verifyOtpAction,
    )
    {
    }

    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        security: [[]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/RegisterRequest'
            )),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered / School request pending'),
            new OA\Response(
                ref: '#/components/responses/ValidationError',
                response: 422
            ),
        ]),
    ]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->registerAction->execute($request->validated());

        if ($result instanceof SchoolRegistrationRequest) {
            return apiResponse(
                statusCode: 201,
                message: 'auth.school_registration_pending'
            );
        }

        $token = $result->createToken('auth-token')->plainTextToken;

        return apiResponse(
            data: ['user' => new UserResource($result)],
            statusCode: 201
        )->withCookie(createAuthCookie($token));
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login',
        security: [[]],
        requestBody: new OA\RequestBody(
            required: true,
        content: new OA\JsonContent(
            required: ['login', 'password'],
            properties: [
            new OA\Property(
                property: 'login',
                description: 'Phone number or email',
                type: 'string'
            ),
            new OA\Property(
                property: 'password',
                type: 'string'
            ),
        ])),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token returned'
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid credentials'
            ),
        ]),
    ]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->loginAction->execute($request->login, $request->password);

        $token = $user->createToken('auth-token')->plainTextToken;

        return apiResponse(
            data: ['user' => new UserResource($user)]
        )->withCookie(createAuthCookie($token));
    }

    #[OA\Post(
        path: '/auth/send-otp',
        summary: 'Send OTP',
        security: [[]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'phone',
                        type: 'string'),
                ])),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP sent'
            ),
        ]),
    ]
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $this->sendOtpAction->execute($request->phone);
        return apiResponse(message: 'auth.otp_sent');
    }

    #[OA\Post(
        path: '/auth/verify-otp',
        summary: 'Verify OTP',
        security: [[]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'phone',
                        type: 'string'),
                    new OA\Property(
                        property: 'otp',
                        type: 'string',
                        maxLength: 6,
                        minLength: 6),
                ])),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP verified'
            ),
        ]),
    ]
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $this->verifyOtpAction->execute($request->phone, $request->otp);

        return apiResponse(message: 'auth.otp_verified');
    }

    #[OA\Post(
        path: '/auth/admin-login',
        summary: 'Admin login',
        security: [[]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email'),
                    new OA\Property(
                        property: 'password',
                        type: 'string'),
                ])),
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token returned'
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid credentials'
            ),
        ]),
    ]
    public function adminLogin(AdminLoginRequest $request): JsonResponse
    {
        $user = $this->adminLoginAction->execute($request->email, $request->password);

        $token = $user->createToken('auth-token')->plainTextToken;

        return apiResponse(
            data: ['user' => new UserResource($user)]
        )->withCookie(createAuthCookie($token));
    }

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Logout (Bearer)',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out'
            ),
        ]),
    ]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        $response = apiResponse(message: 'auth.logged_out');

        if ($request->cookie('royastar_token')) {
            $response->withCookie(cookie()->forget('royastar_token'));
        }

        return $response;
    }
}
