<?php

namespace App\Services;

use App\Models\User;
use App\Services\Telegram\Keyboard;
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

    public function sendMessageToChat($chatId, $message, $die = false, $html = false, $disablePreview = false, $keyboard = 'default')
    {
        $reply_markup = $keyboard == 'default' ? Keyboard::default($this->user) : $keyboard;

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
}
