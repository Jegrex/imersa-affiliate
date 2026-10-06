<?php

namespace Tests\Unit;

use App\Support\HtmlSanitization\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    protected HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new HtmlSanitizer();
    }

    public function test_strips_dangerous_scripts_and_events(): void
    {
        $dirty = '<p>Laptop Canggih<script>alert("xss")</script><img src="x" onerror="alert(1)"> <span onclick="bad()">Klik</span></p>';
        $result = $this->sanitizer->sanitize($dirty);

        $this->assertStringNotContainsString('<script>', $result['html']);
        $this->assertStringNotContainsString('alert', $result['html']);
        $this->assertStringNotContainsString('onerror', $result['html']);
        $this->assertStringNotContainsString('onclick', $result['html']);
        $this->assertStringContainsString('Laptop Canggih', $result['html']);
    }

    public function test_preserves_allowed_tags_and_strips_attributes(): void
    {
        $input = '<p class="text-red" id="intro"><strong>Spesifikasi:</strong></p><ul><li>RAM 16GB</li><li>SSD 1TB</li></ul>';
        $result = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('<p>', $result['html']);
        $this->assertStringNotContainsString('class="text-red"', $result['html']);
        $this->assertStringNotContainsString('id="intro"', $result['html']);
        $this->assertStringContainsString('<strong>Spesifikasi:</strong>', $result['html']);
        $this->assertStringContainsString('<li>RAM 16GB</li>', $result['html']);
    }

    public function test_derives_clean_description_text(): void
    {
        $input = '<p>ASUS Zenbook 14.</p><blockquote>Layar OLED jernih.</blockquote>';
        $result = $this->sanitizer->sanitize($input);

        $this->assertStringContainsString('ASUS Zenbook 14.', $result['text']);
        $this->assertStringContainsString('Layar OLED jernih.', $result['text']);
        $this->assertStringNotContainsString('<p>', $result['text']);
        $this->assertStringNotContainsString('<blockquote>', $result['text']);
    }

    public function test_rejects_oversized_payload_without_truncating(): void
    {
        $oversized = str_repeat('A', 100001);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->sanitizer->sanitize($oversized);
    }
}
