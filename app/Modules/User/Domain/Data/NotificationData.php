<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Data;

use App\Modules\Shared\Domain\ValueObjects\Url;
use App\Modules\User\Domain\Enums\NotificationTypeEnum;

final readonly class NotificationData
{
    public function __construct(
        public string $title,
        public string $message,
        public NotificationTypeEnum $type = NotificationTypeEnum::INFO,
        public ?Url $actionUrl = null,
        public ?string $icon = null,
        public array $channels = ['database', 'broadcast'],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) $data['title'],
            message: (string) $data['message'],
            type: $data['type'] instanceof NotificationTypeEnum
                ? $data['type']
                : NotificationTypeEnum::tryFrom((string) ($data['type'] ?? 'info')) ?? NotificationTypeEnum::INFO,
            actionUrl: isset($data['action_url']) ? new Url((string) $data['action_url']) : null,
            icon: $data['icon'] ?? null,
            channels: $data['channels'] ?? ['database', 'broadcast'],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type->value,
            'action_url' => $this->actionUrl?->getValue(),
            'icon' => $this->icon,
            'channels' => $this->channels,
        ];
    }
}
