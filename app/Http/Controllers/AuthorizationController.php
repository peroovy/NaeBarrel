<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Services\AuthorizationService;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthorizationController extends Controller
{
    private AuthorizationService $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
            'permission' => ['required', new EnumValue(Permissions::class)],
        ]);

        if ($validator->fails())
            return response(status: 400);

        $is_registered = $this->authorizationService->try_register_client(
            $request['login'], $request['email'], $request['password'], $request['permission']);

        return response(status: $is_registered ? 200 : 400);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails())
            return response(status: 400);

        $token = $this->authorizationService->authorize($request['email'], $request['password']);

        return $token ? response(['token' => $token]) : response(status: 400);
    }

    public function logout(Request $request)
    {
        $is_logout = $this->authorizationService->try_logout($request->bearerToken());

        return response(status: $is_logout ? 200 : 401);
    }
}
