<?php

namespace App\Support\HtmlSanitization;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class HtmlSanitizer
{
    public const MAX_RAW_BYTES = 100000;

    protected SymfonyHtmlSanitizer $sanitizer;

    public function __construct()
    {
        // Allowed tags only: p, br, strong, b, em, i, ul, ol, li, h2, h3, h4, blockquote.
        // No attributes, no active links.
        $config = (new HtmlSanitizerConfig())
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('b')
            ->allowElement('em')
            ->allowElement('i')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('blockquote');

        $this->sanitizer = new SymfonyHtmlSanitizer($config);
    }

    /**
     * Sanitize raw HTML description.
     * Throws an exception or returns sanitized HTML and derived plain text.
     *
     * @param string|null $rawHtml
     * @return array{html: ?string, text: ?string}
     * @throws \InvalidArgumentException
     */
    public function sanitize(?string $rawHtml): array
    {
        if ($rawHtml === null || trim($rawHtml) === '') {
            return [
                'html' => null,
                'text' => null,
            ];
        }

        // Byte length check
        $byteLength = strlen($rawHtml);
        if ($byteLength > self::MAX_RAW_BYTES) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'description_html' => "Deskripsi HTML melebihi batas maksimal " . self::MAX_RAW_BYTES . " byte ({$byteLength} byte diberikan).",
            ]);
        }

        try {
            $sanitizedHtml = $this->sanitizer->sanitize($rawHtml);
            
            // Derive plain text server-side for internal reference
            $derivedText = trim(html_entity_decode(strip_tags($sanitizedHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $derivedText = preg_replace('/\s+/', ' ', $derivedText);

            return [
                'html' => $sanitizedHtml,
                'text' => $derivedText !== '' ? $derivedText : null,
            ];
        } catch (\Throwable $e) {
            throw new \RuntimeException("Gagal melakukan sanitasi HTML deskripsi: " . $e->getMessage(), 0, $e);
        }
    }
}
