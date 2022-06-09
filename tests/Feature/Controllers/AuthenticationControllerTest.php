<?php

namespace Tests\Feature\Controllers;

use App\Enums\Permissions;
use App\Models\Client;
use App\Models\Permission;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthenticationControllerTest extends TestCase
{
    private Client $client;
    private string $client_password;
    private string $token;

    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "login",
            "email" => "email@email",
            "password" => \Hash::make($this->client_password = "secret1"),
            "permission" => Permissions::User
        ]);

        $this->token = $this->client->createToken(AuthenticationService::$TOKEN_NAME)->plainTextToken;
    }

    /**
     * @dataProvider provide_bad_registration_data
     */
    public function test_registration__validation_error(array $body)
    {
        $this->assert_registration($body, true, 400);
    }

    public function test_registration__unique_error()
    {
        $bodies = [
            ["login" => $this->client->login, "email" => "1@1", "password" => "p", "permission" => Permissions::User],
            ["login" => "1", "email" => $this->client->email, "password" => "p", "permission" => Permissions::User],
            ["login" => $this->client->login, "email" => $this->client->email, "password" => "p", "permission" => Permissions::User],
        ];

        foreach ($bodies as $body)
            $this->assert_registration($body, true, 422);
    }

    public function test_registration()
    {
        $bodies = [
            ["login" => "user", "email" => "sobaka@kot", "password" => "secret", "permission" => Permissions::User],
            ["login" => "admin", "email" => "stupid@ya", "password" => "secret", "permission" => Permissions::Admin],
            ["login" => "moder", "email" => "kit@fish", "password" => "secret", "permission" => Permissions::Moderator],
        ];

        foreach ($bodies as $body)
            $this->assert_registration($body, false, 200);
    }

    public function provide_bad_registration_data(): array
    {
        return array(
            [["login" => "123"]],
            [["email" => "123@123"]],
            [["password" => "p"]],
            [["permission" => Permissions::User]],
            [["login" => 123, "email" => "1@1", "password" => "p", "permission" => Permissions::User]],
            [["login" => "123", "email" => "11", "password" => "p", "permission" => Permissions::User]],
            [["login" => "123", "email" => 11, "password" => "p", "permission" => Permissions::User]],
            [["login" => "123", "email" => "1@1", "password" => 12, "permission" => Permissions::User]],
            [["login" => "123", "email" => "1@1", "password" => "p", "permission" => -1]],
            [["login" => "123", "email" => "1@1", "password" => "p", "permission" => "-1"]],
        );
    }

    /**
     * @dataProvider provide_bad_login_data
     */
    public function test_login__validation_error(array $body)
    {
        $this->assert_login($body, 400);
    }

    public function provide_bad_login_data()
    {
        return array(
          [["email" => "1@1"]],
          [["password" => "1@1"]],
          [["email" => "123", "password" => "secret"]],
          [["email" => "abs", "password" => "secret"]],
          [["email" => "123", "password" => 123]],
          [["email" => 123, "password" => "secret"]],
          [["email" => null, "password" => "secret"]],
          [["email" => "1@1", "password" => null]],
        );
    }

    public function test_login__wrong_credentials()
    {
        $bodies = [
            ["email" => $this->client->email, "password" => "unknown"],
            ["email" => "unknwon@unknown", "password" => $this->client_password],
            ["email" => "unknwon@unknown", "password" => $this->client->password],
            ["email" => "unknown@unknown", "password" => "unknown"],
        ];

        foreach ($bodies as $body)
            $this->assert_login($body, 401);
    }

    public function test_login()
    {
        $body = ["email" => $this->client->email, "password" => $this->client_password];

        $response = $this->postJson("api/auth/login", $body);

        $response->assertOk();
        $response->assertJson(fn (AssertableJson $json) => $json->has("token"));
    }

    public function test_logout()
    {
        $this->post("api/auth/logout", headers: ["Authorization" => "Bearer " . $this->token])
            ->assertOk();

        $this->assertDatabaseCount(PersonalAccessToken::class, 0);

        $this->post("api/auth/logout", headers: ["Authorization" => "Bearer " . $this->token])
            ->assertUnauthorized();
    }

    private function assert_registration(array $body, bool $is_error, int $status)
    {
        $request = $this->postJson("api/auth/register", $body);

        $checked_data = [];
        foreach (["login", "email"] as $name)
        {
            if (array_key_exists($name, $body))
                $checked_data[$name] = $body[$name];
        }

        $request->assertStatus($status);

        if ($is_error)
        {
            $this->assertDatabaseCount(Client::class, 1);
        }
        else
        {
            $this->assertDatabaseHas(Client::class, $checked_data);
        }
    }

    private function assert_login(array $body, int $status)
    {
        $response = $this->postJson("api/auth/login", $body);

        $response->assertStatus($status);
    }
}
