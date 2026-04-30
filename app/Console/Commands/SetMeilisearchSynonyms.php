<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Meilisearch\Client;

class SetMeilisearchSynonyms extends Command
{
    /**
     * @var string
     */
    protected $signature = 'meilisearch:set-synonyms';

    /**
     * @var string
     */
    protected $description = 'Sets synonyms for the Meilisearch products index. These synonyms work in conjunction with the NLP search preprocessing service.';

    public function handle(): void
    {
        $meilisearchHost = config('scout.meilisearch.host');
        $meilisearchKey = config('scout.meilisearch.key');
        $indexName = config('scout.prefix').'products';

        $client = new Client($meilisearchHost, $meilisearchKey);
        $index = $client->index($indexName);

        $synonyms = [
            'sneakers' => ['shoes', 'trainers', 'kicks'],
            't-shirt' => ['tee', 'top', 'shirt'],
            'pants' => ['trousers', 'jeans', 'slacks'],
            'dress' => ['gown', 'frock'],
            'jacket' => ['coat', 'blazer'],
            'bag' => ['purse', 'handbag', 'backpack'],
            'watch' => ['timepiece', 'chronometer'],
            'hat' => ['cap', 'beanie'],
            'gloves' => ['mittens'],
            'scarf' => ['shawl'],
        ];

        try {
            $index->updateSynonyms($synonyms);
            $this->info(sprintf("Synonyms successfully set for the '%s' index.", $indexName));
        } catch (Exception $exception) {
            $this->error('Failed to set synonyms: '.$exception->getMessage());
        }
    }
}
