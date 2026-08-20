<?php

namespace Rss\Libraries;

use Content\Models\ContentModel;
use Rss\Models\RssItemModel;
use Setting\Models\SettingModel;

class RssIdeaService
{
    private const DEFAULT_SYSTEM_PROMPT = 'You are a copyright-safe editorial assistant. Reply with JSON only.';

    private const DEFAULT_PROMPT_TEMPLATE = "This is for a source-news idea pool. Do not produce a full translation or rewrite the source text. " .
        "Generate an original story idea with these JSON keys: title_suggestion, context_notes (array), original_angle, category_suggestions (array), editor_brief. " .
        "Source title: {title}\nSource summary: {summary}\nSource URL: {url}";

    public function suggest(array $item): array
    {
        $settings = new SettingModel();
        $apiKey = trim((string) $settings->getSetting('rss_ai_api_key', ''));
        if ($apiKey === '') {
            throw new \RuntimeException('RSS AI API key is not configured.');
        }

        $endpoint = trim((string) $settings->getSetting('rss_ai_endpoint', 'https://api.openai.com/v1/chat/completions'));
        $model = trim((string) $settings->getSetting('rss_ai_model', 'gpt-4o-mini'));
        if ($endpoint === '' || $model === '') {
            throw new \RuntimeException('RSS AI endpoint or model is missing.');
        }

        $systemPrompt = trim((string) $settings->getSetting('rss_ai_system_prompt', '')) ?: self::DEFAULT_SYSTEM_PROMPT;
        $template = trim((string) $settings->getSetting('rss_ai_prompt_template', '')) ?: self::DEFAULT_PROMPT_TEMPLATE;
        $prompt = str_replace(
            ['{title}', '{summary}', '{url}'],
            [(string) $item['original_title'], (string) $item['original_summary'], (string) $item['original_url']],
            $template
        );

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $client = \Config\Services::curlrequest(['timeout' => 45]);
        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \RuntimeException('AI request failed with HTTP ' . $response->getStatusCode());
        }

        $data = json_decode((string) $response->getBody(), true);
        $content = (string) ($data['choices'][0]['message']['content'] ?? '');
        $suggestion = json_decode($content, true);
        if (! is_array($suggestion)) {
            throw new \RuntimeException('AI response was not valid JSON.');
        }

        (new RssItemModel())->update((int) $item['id'], [
            'ai_suggestion' => json_encode($suggestion, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'shortlisted',
        ]);

        return $suggestion;
    }

    public function createDraft(array $item, array $source): int
    {
        $suggestion = json_decode((string) ($item['ai_suggestion'] ?? ''), true) ?: [];
        $title = (string) ($suggestion['title_suggestion'] ?? $item['original_title']);
        $body = '<p><strong>RSS idea pool note:</strong> This entry is not a full translation of the source text. It is a starting point for editorial research and original writing.</p>';
        if (! empty($suggestion['editor_brief'])) {
            $body .= '<p>' . esc((string) $suggestion['editor_brief']) . '</p>';
        }
        if (! empty($suggestion['context_notes']) && is_array($suggestion['context_notes'])) {
            $body .= '<ul>';
            foreach ($suggestion['context_notes'] as $note) {
                $body .= '<li>' . esc((string) $note) . '</li>';
            }
            $body .= '</ul>';
        }

        $contentModel = new ContentModel();
        $locale = (string) ((new SettingModel())->getSetting('site_default_locale', 'en'));
        $id = (int) $contentModel->insert([
            'locale' => $locale,
            'content_type' => 'article',
            'title' => $title,
            'slug' => $this->uniqueSlug($title),
            'body' => $body,
            'excerpt' => (string) ($suggestion['original_angle'] ?? ''),
            'status' => 'draft',
            'author_id' => (int) (session('user_id') ?? 1),
            'source_system' => 'rss-idea',
            'source_id' => (string) $item['id'],
            'source_url' => (string) $item['original_url'],
            'custom_data' => json_encode([
                'original_source_name' => $source['name'] ?? '',
                'original_language' => $source['language'] ?? '',
                'rss_item_id' => (int) $item['id'],
                'copyright_policy' => 'idea_pool_only',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        (new RssItemModel())->update((int) $item['id'], [
            'status' => 'drafted',
            'created_content_id' => $id,
        ]);

        return $id;
    }

    private function uniqueSlug(string $title): string
    {
        $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $title) ?: $title), '-')) ?: 'rss-idea';
        $model = new ContentModel();
        $slug = $base;
        $i = 2;
        while ($model->where('slug', $slug)->first()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
