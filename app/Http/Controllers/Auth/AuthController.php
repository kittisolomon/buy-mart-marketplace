<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Otp;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Support\HttpConstants;
use App\Traits\HasJsonResponse;
use Illuminate\Http\JsonResponse;
use App\Services\AuthService;


class AuthController extends Controller
{
    //
    use HasJsonResponse;
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;

    }
   public function register(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            $this->authService->dispatchOtp($user, 'account_verification');

            return $user;
        });

        return $this->jsonResponse(HttpConstants::HTTP_CREATED, 'Registration successfully, Check for OTP in your Mail.', $user);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {

        $user = User::where('email', $request->email)->first();

        switch ($request->type) {
            case 'account_verification':
                $success = $this->authService->accountVerificationOtp($user, $request->otpCode, $request->type);
                break;

            // Future cases: password_update, as will be needed
            default:
                return $this->jsonResponse(HttpConstants::HTTP_BAD_REQUEST, 'Invalid OTP type');

        }

        if (! $success) {
            return $this->jsonResponse(HttpConstants::HTTP_BAD_REQUEST, 'Invalid or expired OTP');
        }

       return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'OTP verified successfully');

    }

    public function login(LoginUserRequest $request): JsonResponse    
    {
        $userCredentials = $request->only('email', 'password');

        $user = User::where('email', $userCredentials['email'])->first();

        if(!$user || !Hash::check($userCredentials['password'], $user->password))
        {
            return $this->jsonResponse(HttpConstants::HTTP_UNAUTHENTICATED, 'Invalid credentials');
        }

        if ($user->is_suspended) {
            return $this->jsonResponse(HttpConstants::HTTP_FORBIDDEN, 'Your account is suspended');
        }

        $user->tokens()->delete(); 

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->jsonResponse(
            HttpConstants::HTTP_SUCCESS,
            'Login successful',
            [
                'user' => $user,
                'token' => $token,
            ]
        );
       
    }
   
    public function logout(Request $request): JsonResponse    
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Logged out successfully');
    }
}
