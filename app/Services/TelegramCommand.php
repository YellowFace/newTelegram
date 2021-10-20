<?php

namespace App\Services;

use Telegram\Bot\Api;

class TelegramCommand
{
    protected Api $telegram;

    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    public function sendMessageToChat($chatId, $message, $die = false, $html = false)
    {
        $reply_markup = $this->getKeyboard();

        $params = [
            'chat_id' => $chatId,
            'reply_markup' => $reply_markup,
            'text' => $message
        ];

        if($html) $params['parse_mode'] = 'HTML';

        $this->telegram->sendMessage($params);

        if($die) die();
    }

    private function getKeyboard()
    {
        $keyboard = [
            ['📘 Помощь'],
        ];

        if ($this->user && $this->user['role'] == 'admin') {
            $adminKeyboard = [
                ['👤 Пользователи'],
                ['💻 Аккаунты'],
                ['⚙ Прокси'],
                ['Ссылок в ожидание обработки'],
            ];

            $keyboard = array_merge($keyboard, $adminKeyboard);
        }

        return $this->telegramApi->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);
    }
}
