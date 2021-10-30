<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Asciitable;
use Illuminate\Console\Command;

class Test extends Command
{
    protected $signature = 'command:test';

    protected $description = 'Command description';

    public function handle()
    {
        $users = User::all();

        foreach ($users->chunk(3) as $users) {
            dd($users);
        }

        $data = [
            ['id' => 1, 'name' => 'Tom', 'status' => 'active'],
            ['id' => 2, 'name' => 'Nick', 'status' => 'disabled'],
            ['id' => 3, 'name' => 'Peter', 'status' => 'active'],
        ];

        $tableGenerator = new Asciitable();

        $table = $tableGenerator->make_table($data, 'Accounts', true);

        echo "<pre>$table</pre>";

//        print_r(Asciitable::scrape_table($table,'name','status'));
    }
}
