<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ParserCommand
{
    protected Client $client;

    protected string $serverIp = 'http://45.67.230.16';

    public function __construct()
    {
        $this->client = new Client();
    }

    public function sendLinksForProcessing($links, $userId)
    {
        $data = [
            'urls' => $links,
            'user_id' => $userId,
        ];

        $response = $this->client->post($this->serverIp . '/api/links', [
            RequestOptions::JSON => $data
        ]);

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function addUsers($users)
    {
        $data = [
            'users' => $users
        ];

        $response = $this->client->post($this->serverIp . '/api/users', [
            RequestOptions::JSON => $data
        ]);

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function getUsers()
    {
        $response = $this->client->get($this->serverIp . '/api/users');

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function addProxies($proxies)
    {
        $data = [
            'proxies' => $proxies
        ];

        $response = $this->client->post($this->serverIp . '/api/proxies', [
            RequestOptions::JSON => $data
        ]);

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function getProxies()
    {
        $response = $this->client->get($this->serverIp . '/api/proxies');

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function getQueueInfo()
    {
        $response = $this->client->get($this->serverIp . '/api/links/queue/count');

        $response = $response->getBody()->getContents();

        $response = json_decode($response, true);

        return $response['count'] ?? -1;
    }


    public function deleteProxies()
    {
        $response = $this->client->delete($this->serverIp . '/api/proxies');

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function deleteAccounts()
    {
        $response = $this->client->delete($this->serverIp . '/api/users');

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }
}
