<?php

namespace App\Http\Controllers;

use App\Support\LineMessaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Line-Signature');

        if (! LineMessaging::verifyWebhookSignature($request->getContent(), $signature)) {
            return response('invalid signature', 400);
        }

        // follow/unfollowイベントを記録する(友だち状態の追跡は将来拡張用)。
        // MVPでは通知先の絞り込みには使わず、ログ記録のみ行う。
        foreach ($request->input('events', []) as $event) {
            Log::info('LINE webhook event', [
                'type' => $event['type'] ?? null,
                'line_user_id' => $event['source']['userId'] ?? null,
            ]);
        }

        return response('ok', 200);
    }
}
