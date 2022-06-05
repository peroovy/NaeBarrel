<?php

namespace Tests\Feature;

use App\Enums\Permissions;
use App\Models\Client;
use App\Models\Permission;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private Client $client;
    private string $client_password;

    private AuthenticationService $service;

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AuthenticationService();

        foreach (Permissions::asArray() as $name => $id)
            Permission::insert(["id" => $id, "name" => $name]);

        $this->client = Client::create([
            "login" => "login1",
            "email" => "email1",
            "password" => \Hash::make($this->client_password = "secret1"),
            "permission" => Permissions::User
        ]);
    }

    public function test_existing_user_by_login_and_email()
    {
        $this->assertFalse($this->service->is_user_exists("unknown", "unknown"));

        $this->assertTrue($this->service->is_user_exists($this->client->login, "unknown"));
        $this->assertTrue($this->service->is_user_exists("unknown", $this->client->email));
        $this->assertTrue($this->service->is_user_exists($this->client->login, $this->client->email));
    }

    public function test_registration__login_or_email_already_exist()
    {
        $this->assertFalse(
            $this->service->try_register($this->client->login, "", "", -1)
        );

        $this->assertFalse(
            $this->service->try_register("unknown", $this->client->email, "", -1)
        );

        $this->assertFalse(
            $this->service->try_register($this->client->login, $this->client->email, "", -1)
        );
    }

    public function test_registration()
    {
        $expected = [
            "login" => "super",
            "email" => "sobaka@kotic",
            "password" => "super_secret",
            "permission" => Permissions::User
        ];

        $this->assertTrue($this->service->try_register(...$expected));

        $actual = Client::where("login", "=", $expected["login"])
            ->where("email", "=", $expected["email"])
            ->getModel();

        $this->assertTrue($actual->password != $expected["password"]);
    }

    public function test_authentication()
    {
        $this->assertNull($this->service->authenticate("unknown", "unknown"));
        $this->assertNull($this->service->authenticate($this->client->email, "unknown"));

        $this->assertIsString($this->service->authenticate($this->client->email, $this->client_password));
    }

    public function test_logout()
    {
        $token = $this->service->authenticate($this->client->email, $this->client_password);
        $this->assertTrue($this->service->try_logout($token));

        $this->assertFalse($this->service->try_logout($token));
    }
}
