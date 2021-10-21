<?php

namespace App\Console\Commands;

use App\Services\ParserCommand;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'command:test';

    protected $description = 'Command description';

    public function handle()
    {
        $parserCommand = new ParserCommand();

        $urls = [];

        $urls[] = 'https://youla.ru/moskva/zhivotnye/koshki/ryzhii-kotienok-choko-v-dar-614ca6ae253a12201a25c10f';

        $info = $parserCommand->sendLinksForProcessing($urls, 35);

        dd($info);

        return Command::SUCCESS;
    }
}
