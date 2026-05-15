<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\Data;

use Override;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class TelegramNotificationTaskData extends Data
{
    public function __construct(
        public int $chatId,
        public string $text,
        public ?string $parseMode = 'HTML',
        public ?string $id = null,
        public ?string $displayName = null,
    ) {}

    #[Override]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'displayName' => $this->displayName,
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'parse_mode' => $this->parseMode,
        ];
    }

    public function toJsonEncode(): string
    {
        return json_encode(
            $this->toArray()
        );
    }
}
