<?php

namespace Tests\Unit;

use App\Support\UrlValidation\UrlValidator;
use Tests\TestCase;

class UrlValidatorTest extends TestCase
{
    protected UrlValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new UrlValidator();
        // Reset custom resolver
        UrlValidator::setDnsResolver(null);
    }

    public function test_rejects_non_https(): void
    {
        $error = null;
        $valid = $this->validator->validate('http://shopee.co.id/product/123', 'marketplace', $error);
        $this->assertFalse($valid);
        $this->assertStringContainsString('HTTPS', $error);
    }

    public function test_rejects_non_443_port(): void
    {
        $error = null;
        $valid = $this->validator->validate('https://shopee.co.id:8080/product/123', 'marketplace', $error);
        $this->assertFalse($valid);
        $this->assertStringContainsString('443', $error);
    }

    public function test_rejects_userinfo(): void
    {
        $error = null;
        $valid = $this->validator->validate('https://admin:pass@shopee.co.id/item', 'marketplace', $error);
        $this->assertFalse($valid);
        $this->assertStringContainsString('userinfo', $error);
    }

    public function test_rejects_non_allowlisted_domain(): void
    {
        $error = null;
        $valid = $this->validator->validate('https://malicious-site.com/phishing', 'marketplace', $error);
        $this->assertFalse($valid);
        $this->assertStringContainsString('tidak terdaftar', $error);
    }

    public function test_anti_ssrf_rejects_private_and_loopback_ips(): void
    {
        // Mock DNS resolver returning private IP for allowed host
        UrlValidator::setDnsResolver(function ($host) {
            return ['127.0.0.1'];
        });

        $error = null;
        $valid = $this->validator->validate('https://shopee.co.id/item', 'marketplace', $error);
        $this->assertFalse($valid);
        $this->assertStringContainsString('publik', $error);

        // Also test 169.254.169.254 (Cloud metadata)
        UrlValidator::setDnsResolver(function ($host) {
            return ['169.254.169.254'];
        });

        $valid = $this->validator->validate('https://shopee.co.id/item', 'marketplace', $error);
        $this->assertFalse($valid);
        $this->assertStringContainsString('publik', $error);
    }

    public function test_accepts_valid_public_url(): void
    {
        // Mock DNS resolver returning a valid public IP
        UrlValidator::setDnsResolver(function ($host) {
            return ['143.244.220.150'];
        });

        $error = null;
        $valid = $this->validator->validate('https://shopee.co.id/asus-laptop-123', 'marketplace', $error);
        $this->assertTrue($valid);
        $this->assertNull($error);
    }
}
