<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;


class AuthenticationService
{
    public static string $TOKEN_NAME = 'token';

    public function isUserExists(string $login, string $email): bool
    {
        return Client::where('login', '=', $login)
            ->orWhere('email', '=', $email)
            ->exists();
    }

    public function tryRegister(string $login, string $email, string $password, int $permission): bool
    {
        if ($this->isUserExists($login, $email))
            return false;

        Client::create([
            'login' => $login,
            'email' => $email,
            'password' => Hash::make($password),
            'permission' => $permission
        ]);

        return true;
    }

    public function authenticate(string $email, string $password): string | null
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password]))
            return null;

        $user = Auth::user();

        return $user->createToken(self::$TOKEN_NAME)->plainTextToken;
    }

    public function tryLogout(string $token): bool
    {
        $token = PersonalAccessToken::findToken($token);
        if (!$token)
            return false;

        $token->delete();
        return true;
    }
}
