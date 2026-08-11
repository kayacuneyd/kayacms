<?php

namespace App\Libraries;

use App\Libraries\Hooks;

/**
 * ContentRenderer — single source of truth for turning raw content
 * (plain text / HTML / future Markdown) into the representations the site
 * consumes: safe HTML, plain-text excerpt, and meta (feed/OG) descriptions.
 *
 * The rendering pipeline is exposed through the content hooks system
 * (`content.render.html`, `content.render.text`, `content.render.excerpt`),
 * so plugins can rewrite output without touching view files.
 */
class ContentRenderer
{
    /** Tags permitted in rendered HTML output (sanitization allowlist). */
    protected const ALLOWED_TAGS = [
        'a', 'abbr', 'blockquote', 'br', 'code', 'div', 'em', 'figcaption',
        'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'img', 'li',
        'ol', 'p', 'pre', 's', 'small', 'span', 'strong', 'sub', 'sup',
        'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    /** Attributes permitted per tag (allowlist). */
    protected const ALLOWED_ATTRIBUTES = [
        'a'    => ['href', 'title', 'rel', 'target'],
        'img'  => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'code' => ['class'],
        'pre'  => ['class'],
        'td'   => ['colspan', 'rowspan'],
        'th'   => ['colspan', 'rowspan'],
    ];

    /** Tags removed entirely together with their content. */
    protected const DANGEROUS_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'link',
        'meta', 'button', 'video', 'audio', 'math', 'svg', 'noscript',
    ];

    /** Default length for generated excerpts. */
    protected const EXCERPT_LENGTH = 140;

    /**
     * Render a content body into safe HTML. Sanitizes the source, then runs
     * the result through the `content.render.html` filter.
     */
    public static function render(?string $source, ?object $item = null): string
    {
        $html = static::sanitize((string) $source);

        return Hooks::applyFilters('content.render.html', $html, $item);
    }

    /**
     * Produce a plain-text representation of content (no markup).
     * Passes through the `content.render.text` filter.
     */
    public static function text(?string $source, ?object $item = null): string
    {
        $text = static::stripMarkup((string) $source);

        return Hooks::applyFilters('content.render.text', $text, $item);
    }

    /**
     * Generate an excerpt. Uses the provided excerpt when present, otherwise
     * derives one from the body's plain text, truncated on a word boundary.
     * Passes through the `content.render.excerpt` filter.
     */
    public static function excerpt(?string $source, ?string $provided = null, int $length = self::EXCERPT_LENGTH, ?object $item = null): string
    {
        if ($provided !== null && trim($provided) !== '') {
            $excerpt = trim($provided);
        } else {
            $excerpt = static::truncate(static::stripMarkup((string) $source), $length);
        }

        return Hooks::applyFilters('content.render.excerpt', $excerpt, $item);
    }

    /**
     * Sanitize arbitrary HTML down to the allowlist: strips dangerous tags,
     * event attributes, `style` attributes and unsafe URLs. Output is safe to
     * render unescaped.
     */
    public static function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>', \LIBXML_NOWARNING | \LIBXML_COMPACT);
        libxml_clear_errors();

        static::stripDangerousElements($dom);
        static::sanitizeAttributes($dom);

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out ?? '');
    }

    /**
     * Strip every dangerous tag plus its entire subtree.
     */
    protected static function stripDangerousElements(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        foreach (self::DANGEROUS_TAGS as $tag) {
            foreach ($xpath->query('//' . $tag) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    /**
     * Remove disallowed tags (may keep inner text) and prune attributes that
     * are not allowlisted or whose URL scheme is unsafe.
     */
    protected static function sanitizeAttributes(\DOMDocument $dom): void
    {
        $xpath = new \DOMXPath($dom);
        $nodes = $xpath->query('//*');

        foreach ($nodes as $node) {
            $tag = strtolower($node->nodeName);

            if (in_array($tag, ['html', 'body'], true)) {
                continue;
            }

            $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Keep visible text (strip tags only), drop everything else.
                // Replace the node with its own text content.
                $node->parentNode?->replaceChild($node->ownerDocument->createTextNode($node->textContent), $node);
                continue;
            }

            $attributes = [];
            foreach ($node->attributes as $attr) {
                $name  = strtolower($attr->nodeName);
                $value = trim((string) $attr->nodeValue);

                if (($name === 'href' || $name === 'src') && ! static::isSafeUrl($value)) {
                    continue;
                }

                if (str_starts_with($name, 'on') || $name === 'style' || $name === 'formaction' || $name === 'form') {
                    continue;
                }

                if (in_array($name, $allowed, true)) {
                    $attributes[$name] = $value;
                }
            }

            while ($node->attributes->length > 0) {
                $node->removeAttribute($node->attributes->item(0)->nodeName);
            }

            foreach ($attributes as $name => $value) {
                $node->setAttribute($name, $value);
            }
        }
    }

    /**
     * Strip all markup into plain text.
     */
    public static function stripMarkup(string $source): string
    {
        $text = static::sanitize($source);
        $text = preg_replace('/<[^>]+>/', ' ', $text) ?? $text;
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    /**
     * Truncate a string on a word boundary, appending an ellipsis.
     */
    public static function truncate(string $text, int $length = self::EXCERPT_LENGTH): string
    {
        $text = trim($text);

        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }

        $cut = mb_substr($text, 0, $length, 'UTF-8');
        $pos = mb_strrpos($cut, ' ', 0, 'UTF-8');

        if ($pos !== false) {
            $cut = mb_substr($cut, 0, $pos, 'UTF-8');
        }

        return trim($cut, " \t\n\r\0\x0B.,;!?:") . '…';
    }

    /**
     * Only permit http(s), mailto, tel, or relative/anchor URLs.
     */
    protected static function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || $url[0] === '#' || $url[0] === '/') {
            return true;
        }

        return (bool) preg_match('/^(https?|mailto|tel):/i', $url);
    }
}