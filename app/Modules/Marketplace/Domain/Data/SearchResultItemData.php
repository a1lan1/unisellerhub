<?php

declare(strict_types=1);

namespace App\Modules\Marketplace\Domain\Data;

class SearchResultItemData
{
    public function __construct(
        public string $type,
        public int $id,
        public string $title,
        public string $subtitle,
        public string $url,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'url' => $this->url,
        ];
    }
}
