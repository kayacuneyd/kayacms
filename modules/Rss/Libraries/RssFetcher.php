<?php

namespace Rss\Libraries;

use Rss\Models\RssItemModel;
use Rss\Models\RssSourceModel;

class RssFetcher
{
    public function fetchAll(): array
    {
        $sourceModel = new RssSourceModel();
        $this->purgePreviousDays();
        $results = [];

        foreach ($sourceModel->where('is_active', 1)->findAll() as $source) {
            $results[] = $this->fetchSource($source);
        }

        return $results;
    }

    public function purgePreviousDays(?string $cutoff = null): int
    {
        $cutoff ??= date('Y-m-d 00:00:00');
        $db = \Config\Database::connect();
        $db->table('rss_items')->where('created_at <', $cutoff)->delete();

        return $db->affectedRows();
    }

    public function fetchSource(array $source): array
    {
        $sourceModel = new RssSourceModel();
        $itemModel = new RssItemModel();
        $inserted = 0;

        try {
            $xmlText = $this->download((string) $source['feed_url']);
            $xmlText = $this->normalizeXml($xmlText);
            $feed = @simplexml_load_string($xmlText, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_RECOVER);
            if (! $feed) {
                throw new \RuntimeException('Feed XML could not be parsed.');
            }

            foreach ($this->items($feed) as $item) {
                $guid = trim((string) ($item['guid'] ?: $item['url']));
                $hash = hash('sha256', $guid);
                if ($itemModel->where('guid_hash', $hash)->first()) {
                    continue;
                }

                $itemModel->insert([
                    'source_id' => (int) $source['id'],
                    'guid_hash' => $hash,
                    'guid' => $guid,
                    'original_title' => $item['title'],
                    'original_summary' => $item['summary'],
                    'original_url' => $item['url'],
                    'published_at' => $item['published_at'],
                    'status' => 'new',
                ]);
                $inserted++;
            }

            $sourceModel->update((int) $source['id'], [
                'last_fetched_at' => date('Y-m-d H:i:s'),
                'last_error' => null,
            ]);

            return ['source' => $source['name'], 'inserted' => $inserted, 'ok' => true, 'error' => null];
        } catch (\Throwable $e) {
            $sourceModel->update((int) $source['id'], [
                'last_fetched_at' => date('Y-m-d H:i:s'),
                'last_error' => $e->getMessage(),
            ]);

            return ['source' => $source['name'], 'inserted' => $inserted, 'ok' => false, 'error' => $e->getMessage()];
        }
    }

    private function download(string $url): string
    {
        $client = \Config\Services::curlrequest([
            'timeout' => 15,
            'headers' => ['User-Agent' => 'KayaCmsRSS/1.0'],
        ]);
        $response = $client->get($url);
        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('HTTP ' . $response->getStatusCode());
        }

        return (string) $response->getBody();
    }

    private function normalizeXml(string $xml): string
    {
        $xml = trim(preg_replace('/^\xEF\xBB\xBF/', '', $xml) ?? $xml);
        $rss = strpos($xml, '<rss');
        $feed = strpos($xml, '<feed');
        $xmlDecl = strpos($xml, '<?xml');
        $positions = array_filter([$xmlDecl, $rss, $feed], static fn ($pos) => $pos !== false);
        if ($positions) {
            $xml = substr($xml, min($positions));
        }

        return $xml;
    }

    private function items(\SimpleXMLElement $feed): array
    {
        $nodes = [];
        if (isset($feed->channel->item)) {
            $nodes = $feed->channel->item;
        } elseif (isset($feed->entry)) {
            $nodes = $feed->entry;
        }

        $items = [];
        foreach ($nodes as $node) {
            $url = (string) ($node->link['href'] ?? $node->link ?? '');
            $items[] = [
                'guid' => (string) ($node->guid ?? $node->id ?? $url),
                'title' => trim(strip_tags((string) ($node->title ?? ''))),
                'summary' => trim(strip_tags((string) ($node->description ?? $node->summary ?? $node->content ?? ''))),
                'url' => trim($url),
                'published_at' => $this->date((string) ($node->pubDate ?? $node->published ?? $node->updated ?? '')),
            ];
        }

        return array_values(array_filter($items, static fn ($item) => $item['title'] !== '' && $item['url'] !== ''));
    }

    private function date(string $value): ?string
    {
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}
