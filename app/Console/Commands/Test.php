<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'command:test';

    protected $description = 'Command description';

    public function handle()
    {
        $client = new Client();

        $data = [
            "id" => 1981,
            "url" => "https://youla.ru/moskva/zhivotnye/koshki/ryzhii-kotienok-choko-v-dar-614ca6ae253a12201a25c10f",
            "time" => 24,
            "result" => "Позвоните позже или напишите сообщение"
        ];

        $client->post('http://pidor1488.ru/api/webhook', [
            RequestOptions::JSON => $data,
            RequestOptions::HEADERS => [
                'Accept' => 'application/json'
            ]
        ]);

        return Command::SUCCESS;
    }
}
