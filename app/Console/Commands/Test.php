<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Bot;
use App\Services\ParserCommand;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'command:test';

    protected $description = 'Command description';

    public function handle()
    {
        $this->parserCommand = new ParserCommand();

        $info = $this->parserCommand->getUsers();

        $users = $info['users'];
        $notUsed = $info['not_used'];

        $count = count($users);

        $message = 'Login:Password/Раз входил/Вытащил ссылок';

//        if($this->user['role'] != User::ADMIN) $message = str_replace(':Password', '', $message);

        $users = collect($users);

        foreach ($users->chunk(50) as $index => $users) {
            if($index) $message = '';

            foreach ($users as $user) {

                $message .= PHP_EOL . $user['login'];
//                    if($this->user['role'] == User::ADMIN) $message .= ":{$user['password']}";
                $message .= '/' . $user['attempts'];
                $message .= '/' . $user['parsed_success'];
            }
        }

        $message .= PHP_EOL . PHP_EOL . "Всего: {$count} шт., чистые: {$notUsed} шт.";

        return Command::SUCCESS;
    }
}
