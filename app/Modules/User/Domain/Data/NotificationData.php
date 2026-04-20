<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Data;

use App\Modules\User\Domain\Enums\NotificationTypeEnum;

final readonly class NotificationData
{
    public function __construct(
        public string $title,
        public string $message,
        public NotificationTypeEnum $type = NotificationTypeEnum::INFO,
        public ?string $actionUrl = null,
        public ?string $icon = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) $data['title'],
            message: (string) $data['message'],
            type: $data['type'] instanceof NotificationTypeEnum
                ? $data['type']
                : NotificationTypeEnum::tryFrom((string) ($data['type'] ?? 'info')) ?? NotificationTypeEnum::INFO,
            actionUrl: $data['action_url'] ?? null,
            icon: $data['icon'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type->value,
            'action_url' => $this->actionUrl,
            'icon' => $this->icon,
        ];
    }
}
