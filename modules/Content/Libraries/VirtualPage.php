<?php

namespace Content\Libraries;

use Content\Models\VirtualPageModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * VirtualPage — URL → content mapping that has no backing content row.
 *
 * A virtual page is a DB record (slug + handler + JSON payload). When the
 * slug is requested the dispatcher decides what to do:
 *
 *  - `template`  → render a theme view listed in payload.view (optional
 *                  payload.data passed into the view)
 *  - `markdown`  → render payload.body through the content renderer
 *                  (the body is treated as safe-HTML-ready markdown source)
 *  - `redirect`  → 302 to payload.url
 */
class VirtualPage
{
    protected VirtualPageModel $model;

    public function __construct(?VirtualPageModel $model = null)
    {
        $this->model = $model ?? new VirtualPageModel();
    }

    /**
     * Dispatch a slug against the virtual page table.
     *
     * @return array{type: string, title: string, view?: string, data?: array, body?: string, url?: string}
     */
    public function dispatch(string $slug): array
    {
        $page = $this->model->findBySlug($slug);

        if (! $page) {
            throw PageNotFoundException::forPageNotFound();
        }

        $payload = $this->model->payloadArray($page);

        return [
            'type'  => (string) ($page['handler'] ?? 'template'),
            'title' => (string) ($page['title'] ?? ucfirst($slug)),
            'view'  => (string) ($payload['view'] ?? ''),
            'data'  => is_array($payload['data'] ?? null) ? $payload['data'] : [],
            'body'  => (string) ($payload['body'] ?? ''),
            'url'   => (string) ($payload['url'] ?? ''),
        ];
    }

    /**
     * Validate that a handler's required payload fields are present.
     */
    public function validatePayload(string $handler, array $payload): array
    {
        $errors = [];

        if ($handler === 'template' && trim((string) ($payload['view'] ?? '')) === '') {
            $errors[] = 'Template handler requires a view name.';
        }

        if ($handler === 'redirect' && trim((string) ($payload['url'] ?? '')) === '') {
            $errors[] = 'Redirect handler requires a target URL.';
        }

        if ($handler === 'markdown' && trim((string) ($payload['body'] ?? '')) === '') {
            $errors[] = 'Markdown handler requires a body.';
        }

        return $errors;
    }

    /**
     * Minimal Markdown → HTML converter for the `markdown` handler. Supports
     * ATX headings, paragraphs, bold/italic, inline code, fenced code blocks,
     * unordered lists and links — then passes through ContentRenderer so HTML
     * output is sanitized against the project allowlist.
     */
    public function renderMarkdown(string $source): string
    {
        $source = preg_replace("/\r\n?/", "\n", $source) ?? '';
        $lines  = explode("\n", $source);

        $html      = '';
        $inList    = false;
        $inCode    = false;
        $codeBuf   = '';
        $paragraph = [];

        $flushParagraph = static function () use (&$paragraph, &$html): void {
            if ($paragraph) {
                $html .= '<p>' . \App\Libraries\ContentRenderer::sanitize(implode(' ', $paragraph)) . '</p>';
                $paragraph = [];
            }
        };

        $flushList = static function () use (&$inList, &$html): void {
            if ($inList) {
                $html .= '</ul>';
                $inList = false;
            }
        };

        $inline = static function (string $text): string {
            $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text) ?? $text;
            $text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text) ?? $text;
            $text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text) ?? $text;
            $text = preg_replace('/\[([^\]]+)\]\(([^)\s]+)\)/', '<a href="$2">$1</a>', $text) ?? $text;

            return $text;
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '```') {
                if ($inCode) {
                    $html .= '<pre><code>' . esc(trim($codeBuf)) . '</code></pre>';
                    $codeBuf = '';
                    $inCode  = false;
                } else {
                    $flushParagraph();
                    $flushList();
                    $inCode = true;
                }
                continue;
            }

            if ($inCode) {
                $codeBuf .= $line . "\n";
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushList();
                $level = strlen($m[1]);
                $html .= "<h{$level}>" . $inline($m[2]) . "</h{$level}>\n";
                continue;
            }

            if (preg_match('/^[-*+]\s+(.+)$/', $trimmed, $m)) {
                $flushParagraph();
                $html .= ($inList ? '' : '<ul>') . '<li>' . $inline($m[1]) . '</li>';
                $inList = true;
                continue;
            }

            if ($trimmed === '') {
                $flushParagraph();
                $flushList();
                continue;
            }

            $paragraph[] = $inline($trimmed);
        }

        $flushParagraph();
        $flushList();

        if ($inCode) {
            $html .= '<pre><code>' . esc(trim($codeBuf)) . '</code></pre>';
        }

        return trim($html);
    }
}