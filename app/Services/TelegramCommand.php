<?php

namespace App\Services;

use App\Models\User;
use Telegram\Bot\Api;

class TelegramCommand
{
    protected Api $telegram;

    /** @var User */
    protected $user;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function sendMessageToChat($chatId, $message, $die = false, $html = false, $disablePreview = false)
    {
        $reply_markup = $this->getKeyboard();

        $params = [
            'chat_id' => $chatId,
            'reply_markup' => $reply_markup,
            'text' => $message
        ];

        if($html) $params['parse_mode'] = 'HTML';
        if($disablePreview) $params['disable_web_page_preview'] = true;

        $this->telegram->sendMessage($params);

        if($die) die();
    }

    private function getKeyboard()
    {
        $keyboard = [
            ['📘 Помощь'],
        ];

        if($this->user) {
            if ($this->user['role'] == User::ADMIN) {
                $adminKeyboard = [
                    ['👤 Пользователи'],
                    ['💻 Аккаунты'],
                    ['⚙ Прокси'],
                    ['Ссылок в ожидание обработки'],
                ];

                $keyboard = array_merge($keyboard, $adminKeyboard);
            }

            if($this->user['role'] == User::MODERATOR) {
                $moderatorKeyboard = [
                    ['👤 Пользователи'],
                ];

                $keyboard = array_merge($keyboard, $moderatorKeyboard);
            }
        }

        return json_encode([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }
}
