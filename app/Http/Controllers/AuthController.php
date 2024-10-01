<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            $firstErrorMessage = $errorMessages[0];

            // Return a JSON response with the first error message
            return response()->json(['error' => $firstErrorMessage], 400);
        }

        // Create the user
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'phone_number' => $request->get('phone_number'),
            'address' => $request->get('address'),
            'role' => $request->get('role')
        ]);

        // Generate JWT tokens for the user
        $token = JWTAuth::fromUser($user);
        $refreshToken = JWTAuth::claims(['refresh' => true])->fromUser($user);

        return response()->json(compact('user', 'token', 'refreshToken'), 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Sai tài khoản và mật khẩu'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Tạo token không thành công'], 500);
        }

        // Generate refresh token
        $refreshToken = JWTAuth::claims(['refresh' => true])->attempt($credentials);

        return response()->json(compact('token', 'refreshToken'));
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expired', 'errorCode' => 'TOKEN_EXPIRED'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalid', 'errorCode' => 'TOKEN_INVALID'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token absent', 'errorCode' => 'TOKEN_ABSENT'], 401);
        }

        return response()->json(compact('user'));
    }

    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->input('refreshToken');
            if (!$token = JWTAuth::setToken($refreshToken)->refresh()) {
                return response()->json(['error' => 'Invalid refresh token', 'errorCode' => 'INVALID_REFRESH_TOKEN'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Refresh token expired', 'errorCode' => 'REFRESH_TOKEN_EXPIRED'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid refresh token', 'errorCode' => 'REFRESH_TOKEN_INVALID'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Refresh token absent', 'errorCode' => 'REFRESH_TOKEN_ABSENT'], 401);
        }

        return response()->json(compact('token'));
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Invalidate the token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to logout, please try again'], 500);
        }
    }

    public function deleteAccount(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Authenticate the user with JWT token
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Delete the user account
            $user->delete();

            return response()->json(['message' => 'Account deleted successfully'], 200);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expired', 'errorCode' => 'TOKEN_EXPIRED'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token', 'errorCode' => 'TOKEN_INVALID'], 401);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token absent', 'errorCode' => 'TOKEN_ABSENT'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete account, please try again'], 500);
        }
    }
}
