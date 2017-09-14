<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class UserTest extends TestCase
{
    /**
     * The external Account Key Service  (POST https://account-key-service.herokuapp.com/v1/account)
     * will return a JSON response containing the email and the account_key
     */
    public function testCurlExternalService()
    {
        $email = "test_".time()."@example.com";
        $key = bin2hex(openssl_random_pseudo_bytes(32));

        $user = new User();
        $response = $user->curlExternalService($email, $key);

        $this->assertTrue($response['success']);
        $this->assertEquals($email, $response['data']['email']);
    }

    /**
     * This endpoint should only accept the fields email, phone_number, full_name, password, and metadata
     */
    public function testOnlyAcceptSelectedFields()
    {
        $data = [
            "email" => "test_".time()."@example.com",
            "phone_number" => rand(1000000000, 9999999999),
            "full_name" => bin2hex(openssl_random_pseudo_bytes(16)),
            "password" => bin2hex(openssl_random_pseudo_bytes(10)),
            "metadata" => "test_".time().", age ".rand(10, 99),
            "random_number" => rand(10, 99),
            "date_time" => date('Y-m-d H:i:s'),
        ];
        $user = new User();

        $trimData = $user->trimData($data);

        $this->assertArrayHasKey("email", $trimData);
        $this->assertArrayHasKey("phone_number", $trimData);
        $this->assertArrayHasKey("full_name", $trimData);
        $this->assertArrayHasKey("password", $trimData);
        $this->assertArrayHasKey("metadata", $trimData);
        $this->assertArrayNotHasKey("random_number", $trimData);
        $this->assertArrayNotHasKey("date_time", $trimData);

    }
}
