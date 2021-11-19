<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Asciitable;
use App\Services\ParserCommand;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'command:test';

    protected $description = 'Command description';

    public function handle()
    {
        $parser = new ParserCommand();

        $response = $parser->getUsers();

        dd($response);
    }
}
