<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\User\Domain\Interfaces\NotificationServiceInterface;
use App\Modules\User\Interfaces\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->notificationService->getNotificationsForUser($user);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $this->notificationService->markAsRead($request->user(), $id);

        return response()->json(['status' => 'success']);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return response()->json(['status' => 'success']);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->notificationService->delete($request->user(), $id);

        return response()->json(['status' => 'success']);
    }

    public function clearAll(Request $request): JsonResponse
    {
        $this->notificationService->clearAll($request->user());

        return response()->json(['status' => 'success']);
    }
}
