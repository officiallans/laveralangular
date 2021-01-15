<?php

trait AuthManager
{
    static $AUTH_URI = '/api/auth/login';

    protected function jsonManager($method, $uri, array $data = [], array $headers = [])
    {
        $response = $this->call('POST', self::$AUTH_URI, [
            'email' => "manager@gmail.com",
            'password' => "secret"
        ]);
        $response = json_decode($response->content());
        $token = $response->token;
        $headers = array_merge(['Authorization' => 'Bearer ' . $token], $headers);
        $this->json($method, $uri, $data, $headers);
        return $this;
    }

    protected function jsonParticipant($method, $uri, array $data = [], array $headers = [])
    {
        $response = $this->call('POST', self::$AUTH_URI, [
            'email' => "test@gmail.com",
            'password' => "secret"
        ]);
        $response = json_decode($response->content());
        $token = $response->token;
        $headers = array_merge(['Authorization' => 'Bearer ' . $token], $headers);
        $this->json($method, $uri, $data, $headers);
        return $this;
    }
}