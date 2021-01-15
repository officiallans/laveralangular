<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{

    public function testInvalid()
    {
        $this
            ->json('POST', '/api/auth/login')
            ->seeStatusCode(401)
            ->seeJsonEquals([
                'error' => 'invalid_credentials'
            ]);

        $this
            ->json('POST', '/api/auth/login', [
                'email' => "manager@gmail.com",
                'password' => "no-secret"
            ])
            ->seeStatusCode(401)
            ->seeJsonEquals([
                'error' => 'invalid_credentials'
            ]);
    }

    public function testLogin()
    {
        $this
            ->json('POST', '/api/auth/login', [
                'email' => "test@gmail.com",
                'password' => "secret"
            ])
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'token'
            ]);

        $this
            ->json('POST', '/api/auth/login', [
                'email' => "manager@gmail.com",
                'password' => "secret"
            ])
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'token'
            ]);

    }

    public function testMyInfoInvalid()
    {
        $this
            ->json('GET', '/api/user/profile/my')
            ->seeJsonEquals([
                'error' => 'token_not_provided'
            ])
            ->seeStatusCode(400);
    }

    public function testMyInfo()
    {
        $this
            ->jsonParticipant('GET', '/api/user/profile/my')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'active',
                'created_at',
                'email',
                'id',
                'name',
                'options',
                'type',
                'updated_at'
            ]);

        $this
            ->jsonManager('GET', '/api/user/profile/my')
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'active',
                'created_at',
                'email',
                'id',
                'name',
                'options',
                'type',
                'updated_at'
            ]);
    }
}
