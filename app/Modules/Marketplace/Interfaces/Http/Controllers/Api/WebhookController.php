<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Application\Actions\Webhooks\ProcessAvitoWebhookAction;
use App\Modules\Marketplace\Application\Actions\Webhooks\ProcessMoySkladWebhookAction;
use App\Modules\Marketplace\Application\Actions\Webhooks\ProcessOzonWebhookAction;
use App\Modules\Marketplace\Application\Actions\Webhooks\ProcessWildberriesWebhookAction;
use App\Modules\Marketplace\Application\Actions\Webhooks\ProcessYandexWebhookAction;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks\AvitoWebhookRequest;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks\MoySkladWebhookRequest;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks\OzonWebhookRequest;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks\WildberriesWebhookRequest;
use App\Modules\Marketplace\Interfaces\Http\Requests\Api\Webhooks\YandexWebhookRequest;
use Illuminate\Http\JsonResponse;

class WebhookController extends Controller
{
    /**
     * Handle incoming webhook from Wildberries.
     */
    public function wildberries(
        WildberriesWebhookRequest $request,
        ProcessWildberriesWebhookAction $action
    ): JsonResponse {
        $action->execute($request->header('Authorization', ''));

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming webhook from Ozon.
     */
    public function ozon(
        OzonWebhookRequest $request,
        ProcessOzonWebhookAction $action
    ): JsonResponse {
        $action->execute(
            $request->header('Client-Id', ''),
            $request->input('message_type')
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming webhook from MoySklad.
     */
    public function moysklad(
        MoySkladWebhookRequest $request,
        ProcessMoySkladWebhookAction $action
    ): JsonResponse {
        $action->execute(
            $request->header('Authorization', ''),
            $request->input('events', [])
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming webhook from Avito.
     */
    public function avito(
        AvitoWebhookRequest $request,
        ProcessAvitoWebhookAction $action
    ): JsonResponse {
        $action->execute(
            $request->header('Authorization', ''),
            $request->input('event_name', '')
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming webhook from Yandex Market.
     */
    public function yandex(
        YandexWebhookRequest $request,
        ProcessYandexWebhookAction $action
    ): JsonResponse {
        $action->execute(
            $request->header('Api-Key', ''),
            (string) $request->input('order_id', '')
        );

        return response()->json(['status' => 'ok']);
    }
}
