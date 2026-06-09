<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(AuthenticateUserRequest $request)
    {
        // Buat variable untuk menyimpan data yang sudah divalidasi tadi
        $credentials = $request->validated();

        // Kita akan cari user berdasarkan email yg kita masukkan
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return ApiResponse::error('Invalid Credentials, Response::HTTP_UNAUTHORIZED');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success(
            [
                'token' => $token,
                'user' => new UserResource($user),
            ],
            'Login Successfull'
        );
    }

    public function me(Request $request)
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'User Data'
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(null, 'Logut Successfull');
    }
}
