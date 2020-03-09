<?php

namespace App\Tests\Controller;

use App\Tests\Authenticate;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package App\Tests\Controller
 */
class UserControllerTest extends WebTestCase
{
    use Authenticate;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;

    const USER_ROUTE = '/api/v1/users';

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->client = static::createClient();
    }

    public function testShowDetailsUserNotAuthenticateShouldFail()
    {
        $this->client->request('GET', self::USER_ROUTE."/me");

        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testShowDetailsUserAuthenticateSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('GET', self::USER_ROUTE."/me", [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $resultExpected = [
            "status" => "success",
            "data" => $this->registeredData
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($resultExpected), $this->client->getResponse()->getContent());
    }

    public function testUserBlockedTryToSeeDetailsShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/blocked', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::USER_ROUTE.'/me', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(403);

        $this->assertJsonStringEqualsJsonString('{"message": "User cannot authenticate!"}', $this->client->getResponse()->getContent());
    }

    public function testUserDisableTryToSeeDetailsShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/disable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::USER_ROUTE.'/me', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(403);

        $this->assertJsonStringEqualsJsonString('{"message": "User cannot authenticate!"}', $this->client->getResponse()->getContent());
    }

    public function testUserEnabledChangeStatusToDisableShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/disable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUserEnabledChangeStatusToBlockedShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/blocked', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUserEnabledChangeStatusToInvalidShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/blocking', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonStringEqualsJsonString('{
                                                                  "status": "fail",
                                                                  "data": {
                                                                    "status": "This status blocking is invalid!"
                                                                  }
                                                                }', $this->client->getResponse()->getContent());
    }

    public function testUserDisableChangeStatusToBlockedShouldFail()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/disable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/blocked', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(403);

        $this->assertJsonStringEqualsJsonString('{"message": "User cannot authenticate!"}', $this->client->getResponse()->getContent());
    }

    public function testUserDisableChangeStatusToEnableShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/disable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/enable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(403);

        $this->assertJsonStringEqualsJsonString('{"message": "User cannot authenticate!"}', $this->client->getResponse()->getContent());
    }

    public function testUserBlockedChangeStatusToEnableShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/blocked', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/enable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(403);

        $this->assertJsonStringEqualsJsonString('{"message": "User cannot authenticate!"}', $this->client->getResponse()->getContent());
    }

    public function testUserBlockedChangeStatusToDisableShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/blocked', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('PUT', self::USER_ROUTE.'/my-status-to/disable', [],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(403);

        $this->assertJsonStringEqualsJsonString('{"message": "User cannot authenticate!"}', $this->client->getResponse()->getContent());
    }

    public function testChangeProfileDataWithoutLoginShouldFail()
    {
        $this->client->request('PUT', self::USER_ROUTE, [],[]);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testChangeProfileDataWithWrongPasswordShouldFail()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE, [
            "password"=>"112233",
            "name"=>"jacob"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testChangePasswordShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();

        $this->client->request('PUT', self::USER_ROUTE, [
            "password"=>"123456",
            "new_password"=>"654321"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('POST', '/authenticate', [
            "email" => $this->email,
            "password" => "654321"
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testChangeNameShouldSuccess()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('GET', self::USER_ROUTE.'/me', [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(null, $res["data"]["name"]);

        $this->client->request('PUT', self::USER_ROUTE, [
            "password"=>"123456",
            "name"=>"Jacob"
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('GET', self::USER_ROUTE.'/me', [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $res = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals("Jacob", $res["data"]["name"]);
    }

    public function testChangePasswordToNothingShouldNotChange()
    {
        $result = $this->getTokenAuthenticate();
        $this->client->request('GET', self::USER_ROUTE.'/me', [],[], ["HTTP_Authorization" => $result["token"]]);

        $this->client->request('PUT', self::USER_ROUTE, [
            "password"=>"123456",
            "new_password"=>""
        ],[], ["HTTP_Authorization" => $result["token"]]);
        $this->assertResponseStatusCodeSame(204);

        $this->client->request('POST', '/authenticate', [
            "email" => $this->email,
            "password" => "123456"
        ]);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}