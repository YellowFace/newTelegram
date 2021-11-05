<?php

namespace App\Services\Telegram;

use App\Models\User;

class Keyboard
{
    public static function resultPhone($user, $data)
    {
        $defaultMessage = ($user['default_message'] ?? 'Ещё актуально?') . PHP_EOL . $data['url'];

        $params = http_build_query([
            'text' => $defaultMessage
        ]);

        $url = "{$data['result']}?{$params}";

        $keyboard = [
          [
              ['text' => 'Открыть', 'url' => $url]
          ]
        ];

        return json_encode([
            'inline_keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);
    }

    public static function default($user)
    {
        $keyboard = [
            ['📘 Помощь'],
        ];

        if($user) {
            if ($user['role'] == User::ADMIN) {
                $adminKeyboard = [
                    ['👤 Пользователи'],
                    ['💻 Аккаунты'],
                    ['⚙ Прокси'],
                    ['Ссылок в ожидание обработки'],
                ];

                $keyboard = array_merge($keyboard, $adminKeyboard);
            }

            if($user['role'] == User::MODERATOR) {
                $moderatorKeyboard = [
                    ['💻 Аккаунты'],
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
