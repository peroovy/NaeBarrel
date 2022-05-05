<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthorizationService
{
    public static string $TOKEN_NAME = 'token';

    public function is_user_exist(string $login, string $email): bool
    {
        return Client::where([['login', '=', $login], ['email', '=', $email]])->exists();
    }

    public function try_register_client(string $login, string $email, string $password, int $permission): bool
    {
        if ($this->is_user_exist($login, $email))
            return false;

        Client::create([
            'login' => $login,
            'email' => $email,
            'password' => Hash::make($password),
            'permission' => $permission
        ]);

        return true;
    }

    public function authorize(string $email, string $password): string | null
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password]))
            return null;

        $user = Auth::user();

        if ($user->tokens()->exists())
            return null;

        return $user->createToken(self::$TOKEN_NAME)->plainTextToken;
    }

    public function try_logout(string $token): bool
    {
        $token = PersonalAccessToken::findToken($token);
        if (!$token)
            return false;

        $token->delete();
        return true;
    }
}
