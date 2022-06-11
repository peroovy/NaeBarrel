<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Services\AuthenticationService;
use App\Services\ProfileService;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    private AuthenticationService $authenticationService;
    private ProfileService $profileService;

    public function __construct(AuthenticationService $authorizationService, ProfileService $profileService)
    {
        $this->authenticationService = $authorizationService;
        $this->profileService = $profileService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'permission' => ['required', new EnumValue(Permissions::class)],
        ]);

        if ($validator->fails())
            return response(status: 400);

        $is_registered = $this->authenticationService->try_register(
            $request['login'], $request['email'], $request['password'], $request['permission']);

        return response(status: $is_registered ? 200 : 422);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails())
            return response(status: 400);

        $token = $this->authenticationService->authenticate($request['email'], $request['password']);

        return $token ? response(['token' => $token]) : response(status: 401);
    }

    public function logout(Request $request)
    {
        $is_logout = $this->authenticationService->try_logout($request->bearerToken());

        return response(status: $is_logout ? 200 : 401);
    }

    public function DeleteProfile(Request $request) {
        $id = Auth::user()->id;
        $this->logout($request);
        return $this->profileService->DeleteProfile($id);
    }
}
