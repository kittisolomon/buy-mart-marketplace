<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Support\HttpConstants;
use App\Traits\HasJsonResponse;

class AuthController extends Controller
{
    //
    use HasJsonResponse;
    public function register(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!$user) {
            return $this->jsonResponse(HttpConstants::HTTP_INTERNAL_SERVER_ERROR, 'User registration failed, Try Again!');
        }

        return $this->jsonResponse(HttpConstants::HTTP_CREATED, 'User registered successfully', $user);
    }


    public function login(LoginUserRequest $request)
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
   
    public function logout(Request $request)
    {
        $user = Auth::user();

        $user->currentAccessToken()->delete();

        return $this->jsonResponse(HttpConstants::HTTP_SUCCESS, 'Logged out successfully');
    }
}
