<?php

namespace App\Services;

use App\Models\Query;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class Bot {

    private $chatId;
    private $username;
    private $message;

    private $explodedMessage;

    /** @var User */
    private $user;

    private ParserCommand $parserCommand;
    private TelegramCommand $telegramCommand;

    function __construct($chat_id, $username, $message, $telegramApi)
    {
        $this->chatId = $chat_id;
        $this->username = $username;
        $this->message = $message;

        $this->parserCommand = new ParserCommand();
        $this->telegramCommand = new TelegramCommand($telegramApi);
    }

    public function controller()
    {
        if ($this->message) {

            $this->checkAccess();

            $this->explodedMessage = explode("\n", $this->message);

            $command = trim($this->explodedMessage[0]);

            switch (true) {
                case $command == '/start': $this->start(); break;
                case $command == '/add accounts': $this->addAccounts(); break;
                case $command == '💻 Аккаунты': $this->getAccounts(); break;
                case $command == '/add users': $this->addUsers(); break;
                case $command == '👤 Пользователи': $this->getUsers(); break;
                case $command == '/delete users': $this->deleteUsers(); break;
                case $command == '/add proxies': $this->addProxies(); break;
                case $command == '⚙ Прокси': $this->getProxies(); break;
                case $command == '📘 Помощь': $this->sendHelpMessage(); break;
                case $command == 'Ссылок в ожидание обработки': $this->showQueueInfo(); break;
                case $command == '/delete proxies': $this->deleteProxies(); break;
                case $command == '/delete accounts': $this->deleteAccounts(); break;
                case $command == '/default message': $this->defaultMessage(); break;
                case $command == '/notify all': $this->notifyAll(); break;
                case preg_match('/https:\/\/youla.(ru|io)\/.+/', $command): $this->sendLinksForProcessing(); break;
            }
        }
    }

    private function notifyAll()
    {
        $this->checkRights();

        $messages = $this->explodedMessage;

        unset($messages[0]);

        $message = implode(PHP_EOL, $messages);

        $message = 'Администратор:' . PHP_EOL . $message;

        $chats = User::query()->whereNotNull('chat_id')->pluck('chat_id')->toArray();

        foreach ($chats as $index => $chat) {
            if($index != 0 && ($index % 30) == 0) sleep(1); //лимит телеграма на 30 в секунду todo global
            try {
                $this->telegramCommand->sendMessageToChat($chat, $message);
            }
            catch (\Exception $exception) {
                continue;
            }
        }

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Уведомление было отправлено.');
    }

    private function checkAccess()
    {
        /** @var User $user */
        $user = User::query()->firstWhere('username', $this->username);

        $this->user = $user;

        if (!$this->user) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'У вас нет доступа к этому боту', true);
        }

        $this->telegramCommand->setUser($this->user);
    }


    private function checkRights($roles = [ User::ADMIN ])
    {
        if(in_array($this->user['role'], $roles)) return true;

        $this->telegramCommand->sendMessageToChat($this->chatId, 'У вас нет доступа к этой команде', true);
    }

    private function start()
    {
        $this->user->update(['chat_id' => $this->chatId]);

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Бот запущен');
    }

    private function showQueueInfo()
    {
        $this->checkRights();

        $count = $this->parserCommand->getQueueInfo();

        $message = "Ссылок в ожидание обработки: {$count} шт.";

        $this->telegramCommand->sendMessageToChat($this->chatId, $message);
    }

    private function sendHelpMessage()
    {
        $messages = [
            'Общие команды:',
            '<b>/default message</b> - подставка сообщения в WhatsApp',
        ];

        if($this->user) {
            if($this->user['role'] == User::ADMIN) {
                $adminMessages = [
                    'Команды администраторов:',
                    '<b>/add users</b> - добавить пользователей',
                    '<b>/delete users</b> - удалить пользователей',
                    '<b>/add accounts</b> - добавить аккаунты',
                    '<b>/add proxies</b> - добавить прокси',
                    '<b>/delete proxies</b> - очищает список прокси',
                    '<b>/notify all</b> - уведомление от лица администратора',
                ];

                $messages = array_merge($messages, $adminMessages);
            }

            if($this->user['role'] == User::MODERATOR) {
                $adminMessages = [
                    'Команды модераторов:',
                    '<b>/add accounts</b> - добавить аккаунты',
                ];

                $messages = array_merge($messages, $adminMessages);
            }
        }

        $message = implode(PHP_EOL, $messages);

        $this->telegramCommand->sendMessageToChat($this->chatId, $message, false, true);
    }

    private function defaultMessage()
    {
        $messages = $this->explodedMessage;

        unset($messages[0]);

        $message = implode(PHP_EOL, $messages);

        if(strlen($message) > 500) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Ошибка: Слишком длинное сообщение.', true);
        }

        $this->user->update(['default_message' => $message]);

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Сообщение было сохранено, оно будет подставляться при открытие номера.');
    }

    private function addAccounts()
    {
        $this->checkRights([User::ADMIN, User::MODERATOR]);

        $this->addLog('use command /add accounts');

        $users = [];

        $items = $this->explodedMessage;
        unset($items[0]);

        foreach ($items as $item) {
            $item = str_replace(' ', '', $item);

            if (!preg_match('/.+:.+/', $item)) {
                $this->telegramCommand->sendMessageToChat($this->chatId, 'Неверный формат. Проверьте список еще раз.', true);
            }
            else {
                $user = [];

                $loginPass = explode(':', $item);

                $user['login'] = $loginPass[0];
                $user['password'] = $loginPass[1];
                $users[] = $user;

                $this->parserCommand->addUsers($users);
            }
        }

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Аккаунты успешно добавлены');
    }

    private function addLog(string $message)
    {
        Log::info("[{$this->user->username}] {$message}");
    }

    public function getAccounts()
    {
        $this->checkRights([User::ADMIN, User::MODERATOR]);

        $this->addLog('use command /get accounts');

        $info = $this->parserCommand->getUsers();

        $users = $info['users'];
        $notUsed = $info['not_used'];

        if(!count($users)) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Нет добавленных аккаунтов', true);
        }

        $count = count($users);
        $message = 'Добавлено ' . $count . ' аккаунтов:' . "\n";

        if ($count > 50) $users = array_slice($users, -50);

        foreach ($users as $user) {
            $message .= PHP_EOL . $user['login'];
            if($this->user['role'] == User::ADMIN) $message .= ":{$user['password']}";
        }

        $message .= PHP_EOL . PHP_EOL . "Не использованы: {$notUsed}";

        $this->telegramCommand->sendMessageToChat($this->chatId, $message);
    }

    private function addUsers()
    {
        $this->checkRights();

        $items = $this->explodedMessage;
        unset($items[0]);

        foreach ($items as $item) {
            $item = str_replace(' ', '', $item);

            if (!preg_match('/^[a-z0-9_]+:\d+$/', $item)) {
                $this->telegramCommand->sendMessageToChat($this->chatId, 'Неверный формат. Проверьте список еще раз.', true);
            }
            else {
                $user = [];

                $userData = explode(':', $item);

                $user['username'] = $userData[0];
                $user['limit'] = $userData[1];

                User::query()->where('username', $user['username'])->delete();

                User::query()->create([
                    'username' => $user['username'],
                    'limit' => $user['limit'],
                    'role' => 'member'
                ]);
            }
        }

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Пользователи успешно добавлены');
    }

    private function deleteUsers()
    {
        $this->checkRights();

        $items = $this->explodedMessage;
        unset($items[0]);

        foreach ($items as $item) {
            $item = str_replace(' ', '', $item);

            User::query()->where('username', $item)->delete();

        }

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Пользователи успешно удалены');
    }

    private function getUsers()
    {
        $this->checkRights();

        $users = User::query()
            ->orderBy('role')
            ->get();

        $message = '';

        foreach ($users as $user) {
            if ($user['role'] == User::ADMIN) $row = $user['username'] . ' (админ)';
            else if($user['role'] == User::MODERATOR) $row = $user['username'] . ':' . $user['limit'] . ' (модератор)';
            else $row = $user['username'] . ':' . $user['limit'];

            $message .= $row . PHP_EOL;
        }

        if ($message) $this->telegramCommand->sendMessageToChat($this->chatId, $message);
    }

    private function addProxies()
    {
        $this->checkRights();

        $proxies = [];

        $items = $this->explodedMessage;
        unset($items[0]);

        foreach ($items as $item) {
            $item = str_replace(' ', '', $item);

            if (!preg_match('/.+:.+/', $item)) {
                $this->telegramCommand->sendMessageToChat($this->chatId, 'Неверный формат. Проверьте список еще раз.', true);
            }
            else {
                $proxy = [];

                $proxy['host'] = $item;
                $proxies[] = $proxy;

                $this->parserCommand->addProxies($proxies);
            }
        }

        $this->telegramCommand->sendMessageToChat($this->chatId, 'Прокси успешно добавлены');
    }

    public function getProxies()
    {
        $this->checkRights();

        $proxies = $this->parserCommand->getProxies();

        if (!count($proxies)) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Нет добавленных прокси', true);
        }

        $message = 'Добавлено ' . count($proxies) . ' прокси:' . "\n";
        if (count($proxies) > 20) $proxies = array_slice($proxies, 0, 20);

        foreach ($proxies as $proxy) {
            $message .= PHP_EOL . "{$proxy['ip']}:{$proxy['port']}";
        }

        $this->telegramCommand->sendMessageToChat($this->chatId, $message);
    }

    private function deleteProxies()
    {
        $this->checkRights();

        $response = $this->parserCommand->deleteProxies();
        if (isset($response['deleted']) && $response['deleted']) {
            $count = $response['deleted'];
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Удалено ' . $count . ' прокси');
        }
    }

    private function deleteAccounts()
    {
        $this->checkRights();

        $response = $this->parserCommand->deleteAccounts();
        if (isset($response['deleted']) && $response['deleted']) {
            $count = $response['deleted'];
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Удалено ' . $count . ' аккаунтов');
        }
    }

    public static function isValidUrls($urls): bool
    {
        foreach ($urls as $url) {
            if(mb_strlen($url) > 255) return false;
            if(!preg_match('/^https:\/\/youla.(ru|io)\/.+/', $url)) return false;
            if(preg_match('/[А-Яа-яЁё]+/', $url)) return false;
        }

        return true;
    }

    private function sendLinksForProcessing()
    {
        if($this->user['role'] == 'member' && getenv('BLOCK_SEND_LINKS')) {
            $this->telegramCommand->sendMessageToChat($this->chatId, "В данный момент идут тех. работы. Подождите.", true);
        }

        $links = $this->explodedMessage;

        // Валидация ссылок
        if(!self::isValidUrls($links)) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Неверный формат. Проверьте ссылки еще раз.', true);
        }

        // Ограничение на количество ссылок
        if (count($this->explodedMessage) > 10) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Максимальное количество ссылок - 10', true);
        }

        // Если пользователь не админ и лимит просмотров закончился, отклоняем запрос
        if ($this->user['role'] != User::ADMIN && count($links) > $this->user['limit']) {
            $this->telegramCommand->sendMessageToChat($this->chatId, 'Превышен лимит просмотров', true);
        }
        else {
            $query = $this->parserCommand->sendLinksForProcessing($links, $this->user['id']);

            if($query['code'] != 0) {
                $message = $query['message'] ?? 'Произошла ошибка отправки';
                $this->telegramCommand->sendMessageToChat($this->chatId, $message, true);
            }

            foreach ($query['links'] as $link) {
                Query::query()->create([
                    'user_id' => $this->user['id'],
                    'link_id' => $link['id'],
                ]);

                if ($this->user['role'] != User::ADMIN) User::query()->where('id', $this->user['id'])->decrement('limit', count($links));
            }

            $inProgress = $query['in_progress'] ?? -1;

            $message = $query['message'] ?? "Ссылки отправлены в обработку, ваших в обработке: {$inProgress} шт.";
            $this->telegramCommand->sendMessageToChat($this->chatId, $message);
        }
    }
}
