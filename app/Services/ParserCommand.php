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

        $response = $this->client->post($this->serverIp . '/links', [
            RequestOptions::JSON => json_encode($data)
        ]);

        return json_decode($response, true);
    }


    public function addUsers($users)
    {
        $data = [
            'users' => $users
        ];

        $response = $this->client->post($this->serverIp . '/users', [
            RequestOptions::JSON => json_encode($data)
        ]);

        return json_decode($response, true);
    }


    public function getUsers()
    {
        $response = $this->client->get($this->serverIp . '/users');

        return json_decode($response, true);
    }

    public function addProxies($proxies)
    {
        $data = [
            'proxies' => $proxies
        ];

        $response = $this->client->post($this->serverIp . '/proxies', [
            RequestOptions::JSON => json_encode($data)
        ]);

        return json_decode($response, true);
    }

    public function getProxies()
    {
        $response = $this->client->get($this->serverIp . '/proxies');

        return json_decode($response, true);
    }

    public function getQueueInfo()
    {
        $response = $this->client->get($this->serverIp . '/links/queue/count');

        return json_decode($response, true);
    }


    public function deleteProxies()
    {
        $response = $this->client->delete($this->serverIp . '/proxies');

        return json_decode($response, true);
    }

    public function deleteAccounts()
    {
        $response = $this->client->delete($this->serverIp . '/users');

        return json_decode($response, true);
    }
}
