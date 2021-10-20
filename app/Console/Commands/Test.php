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

        $info = $parserCommand->getQueueInfo();

        dd($info);

        return Command::SUCCESS;
    }
}
