<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $result = $this->authService->register($request->all());

        if (isset($result['errors'])) {
            return response()->json($result['errors'], $result['status']);
        }

        return response()->json(['user' => $result['user'], 'token' => $result['token']], $result['status']);
    }

    public function login(Request $request)
    {
        $result = $this->authService->login($request->all());

        if (isset($result['errors'])) {
            return response()->json($result['errors'], $result['status']);
        }

        if (isset($result['message'])) {
            return response()->json(['message' => $result['message']], $result['status']);
        }

        return response()->json(['user' => $result['user'], 'token' => $result['token']], $result['status']);
    }
}
