<?php

namespace App\Tests\ApiTestCase;

use App\ApiResponse\ApiResponse;
use App\Tests\BaseTestCase\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTestCase extends BaseTestCase
{

    const string VALIDATION_MESSAGE_NOT_BLANK = 'This value should not be blank.';
    const string VALIDATION_MESSAGE_TYPE_INT = 'This value should be of type int.';
    const string VALIDATION_MESSAGE_TYPE_CHOICE = 'The value you selected is not a valid choice.';
    const string VALIDATION_MESSAGE_TYPE_STRING = 'This value should be of type string.';
    //here would be better to have a custom translation with parameters rather than specific for my case.
    //but in tic-tac-toe game, the range is always 0-8.
    const string VALIDATION_MESSAGE_RANGE_0_8 = 'This value should be between 0 and 8.';


    private function assertKOToken(string $message): void
    {
        $this->assertResponseStatusCodeSame(401);
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $array_response = json_decode($response->getContent(), true);
        $this->assertEquals(ApiResponse::KO, $array_response['result']);
        $this->assertEquals($message, $array_response['error_msg']);

    }

    protected function assertUnauthorizedAccess(): void
    {
        $this->assertKOToken('JWT Token not found');
    }


    protected function assertExpiredToken(): void
    {
        $this->assertKOToken('Expired JWT Token');
    }


    protected function assertOkResponseApi(): void
    {
        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $array_response = json_decode($response->getContent(), true);
        $this->assertEquals(ApiResponse::OK, $array_response['result']);
    }

    protected function assertKoResponseApi(int $expectedCode = Response::HTTP_BAD_REQUEST, string $error_msg = '', string $error_msg_contains = ''): void
    {
        $this->assertResponseStatusCodeSame($expectedCode);
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $array_response = json_decode($response->getContent(), true);
        $this->assertEquals(ApiResponse::KO, $array_response['result']);
        if(!empty($error_msg)) {
            $this->assertEquals($error_msg, $array_response['error_msg']);
        }
        if(!empty($error_msg_contains)) {
            $this->assertStringContainsString($error_msg_contains, $array_response['error_msg']);
        }
    }


    protected function getToken(array $user_data): string
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
                'username' => $user_data['username'],
                'password' => $user_data['password']
                ])
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertOkResponseApi();
        return $this->getTokenFromApiResponse();

    }

    private function getTokenFromApiResponse(): string
    {
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        return $response['return']['token'];
    }

    protected function getGameIdFromApiResponse(): string
    {
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);
        return $response['return']['game_id'];
    }
    
}