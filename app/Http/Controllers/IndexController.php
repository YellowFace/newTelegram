<?php

namespace App\Http\Controllers;

use App\Models\Query;
use App\Models\User;
use App\Services\Bot;
use App\Services\TelegramCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;

class IndexController extends Controller
{
    protected Api $telegram;
    protected TelegramCommand $telegramCommand;

    public function __construct()
    {
        $this->telegram = new Api(getenv('TELEGRAM_TOKEN'));
    }

    public function index(Request $request)
    {
        $update = $this->telegram->getWebhookUpdate();

        Log::info($update);

        $message = $update->getMessage();
        $chat = $update->getChat();

        Log::info($chat);

        if($chat)
        {
            $chatId = $chat->get('id');
            $username = $chat->get('username');

            $bot = new Bot($chatId, $username, $message, $this->telegram);
            $bot->controller();
        }

        return response(null, 200);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        if (!isset($data['id'])) {
            $response['result'] = 'fail';
            return json_encode($response);
        }

        /** @var Query $query */
        $query = Query::query()->findOrFail($data['id']);

        $user = User::query()->findOrFail($query['user_id']);

        if (!$user['chat_id']) {
            $response['result'] = 'chat_id is empty, please, restart the bot';
            return json_encode($response);
        }

        $message = "<b>Ссылка:</b>\n" . $data['url'] . "\n\n<b>Номер:</b>\n" . $data['result'];

        if(strlen($user['default_message']) && str_contains($data['result'], 'wa.me')) {

            $params = http_build_query([
                'text' => $user['default_message']
            ]);

            $message .= "?{$params}";
        }

        $this->telegramCommand->sendMessageToChat($user['chat_id'], $message);
        $query->update(['status' => 'success']);

        $response['result'] = 'success';
        return json_encode($response);
    }
}

