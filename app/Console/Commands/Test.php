<?php

namespace App\Console\Commands;

use App\Services\Bot;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'command:test';

    protected $description = 'Command description';

    public function handle()
    {
        $urls = [
          'https://youla.ru/moskva/smartfony-planshety/smartfony/samsung-galaxy-a21s-32gb-6170240c39e9b244312e1b1f',
          'https://youla.ru/lyubertsy/smartfony-planshety/smartfony/samsung-galaxy-a10-60c1421b549c927b70391d3e'
        ];

        $check = Bot::isValidUrls($urls);

        dd($check);

        return Command::SUCCESS;
    }
}
