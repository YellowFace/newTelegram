<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
            RequestOptions::JSON => $data,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
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
        $response = Cache::remember('get-users', CarbonInterval::minute(), function () {
            try {
                $response = $this->client->get($this->serverIp . '/api/users');

                return $response->getBody()->getContents();
            }
            catch (\Exception $exception) {
                Log::error($exception);
                return [];
            }
        });

        Log::info('users');
        Log::info($response);

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
        $response = Cache::remember('get-queue-info', CarbonInterval::minute(), function () {
            try {
                $response = $this->client->get($this->serverIp . '/api/links/queue/count');

                $response = $response->getBody()->getContents();

                return json_decode($response, true);
            }
            catch (\Exception $exception) {
                Log::error($exception);
                return [];
            }
        });

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
