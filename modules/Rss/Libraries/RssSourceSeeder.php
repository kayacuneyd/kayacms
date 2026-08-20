<?php

namespace Rss\Libraries;

use Rss\Models\RssSourceModel;

class RssSourceSeeder
{
    /**
     * Starter list of RSS sources to seed via `spark rss:seed-sources`.
     * Empty by default — edit this list with your own feeds (or insert
     * rows directly into the `rss_sources` table) before running the seed
     * command. Each row: [name, feed_url, country, language, source_type].
     */
    public static function sources(): array
    {
        return [
            // ['Example News', 'https://example.com/rss', 'Country', 'en', 'media'],
        ];
    }

    public function seed(): array
    {
        $model = new RssSourceModel();
        $created = 0;
        $updated = 0;

        foreach (self::sources() as [$name, $feedUrl, $country, $language, $sourceType]) {
            $existing = $model->groupStart()
                ->where('feed_url', $feedUrl)
                ->orWhere('name', $name)
                ->groupEnd()
                ->first();
            $data = [
                'name' => $name,
                'feed_url' => $feedUrl,
                'country' => $country,
                'language' => $language,
                'source_type' => $sourceType,
                'is_active' => 1,
            ];

            if ($existing) {
                $model->update((int) $existing['id'], $data);
                $updated++;
            } else {
                $model->insert($data);
                $created++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }
}
