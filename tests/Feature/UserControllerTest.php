<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{

    public function testCreateUserSuccess()
    {
        $data = [
            "email" => "test_".time()."@example.com",
            "phone_number" => rand(1000000000, 9999999999),
            "full_name" => bin2hex(openssl_random_pseudo_bytes(16)),
            "password" => bin2hex(openssl_random_pseudo_bytes(10)),
            "metadata" => "test_".time().", age ".rand(10, 99)
        ];
        $headers = [ "Content-Type: application/json" ];

        $response = $this->postJson('/v1/users', $data, $headers);
        $response->assertStatus(201);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertSee($data['email']);
        $response->assertDontSee($data['password']);
        $response->assertDontSee("password");
        $response->assertDontSee("id");
    }

    public function testCreateUserFailEmailMissing()
    {
        $data = [
            "phone_number" => rand(1000000000, 9999999999),
            "full_name" => bin2hex(openssl_random_pseudo_bytes(16)),
            "password" => rand(100000, 999999),
            "metadata" => "test_".time().", age ".rand(10, 99)
        ];
        $headers = [ "Content-Type: application/json" ];

        $response = $this->postJson('/v1/users', $data, $headers);
        $response->assertStatus(422);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertSee("errors");
        $response->assertSee("The email field is required");
    }


    public function testGetAllUsers()
    {
        $response = $this->get('/v1/users');
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
    }

    public function testGetQueryUsers()
    {
        $query = "example.com";
        $response = $this->get('/v1/users?query='.$query);
        $response->assertStatus(200);
        $response->assertHeader("Content-Type", "application/json");
        $response->assertSee($query);
    }
}
