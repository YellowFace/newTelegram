<?php

namespace App\Http\Controllers;

use App\Models\Query;
use App\Models\User;
use App\Services\Bot;
use App\Services\ParserCommand;
use App\Services\TelegramCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Api;

class IndexController extends Controller
{
    protected Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(getenv('TELEGRAM_TOKEN'));
    }

    public function index(Request $request)
    {
        $update = $this->telegram->getWebhookUpdates();

        $chat = $update->getChat();

        if($chat && $chat->has('id'))
        {
            $message = $update->getMessage()->text;
            $chatId = $chat->id;
            $username = $chat->username;

            $bot = new Bot($chatId, $username, $message, $this->telegram);
            $bot->controller();
        }

        return response(null, 200);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();

        if (!isset($data['id']) || !isset($data['user'])) {
            $response['result'] = 'fail';
            return json_encode($response);
        }

        /** @var Query $query */
        $query = Query::query()->firstWhere('link_id', $data['id']);

        $user = User::query()->findOrFail($data['user']);

        $telegramCommand = new TelegramCommand($this->telegram);
        $telegramCommand->setUser($user);

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

        $telegramCommand->sendMessageToChat($user['chat_id'], $message, false, true, true);
        $query->update(['status' => 'success']);

        $parserCommand = new ParserCommand();
        $queueCount = $parserCommand->getQueueInfo();

        if($queueCount < 10) Cache::forget('stop_processing_links');

        $response['result'] = 'success';
        return json_encode($response);
    }
}

