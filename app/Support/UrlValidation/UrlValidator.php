<?php

namespace App\Support\UrlValidation;

class UrlValidator
{
    public const MAX_BYTES = 2048;

    /**
     * @var callable|null
     */
    protected static $dnsResolver = null;

    /**
     * Set a custom DNS resolver for testing purposes.
     */
    public static function setDnsResolver(?callable $resolver): void
    {
        self::$dnsResolver = $resolver;
    }

    /**
     * Validate an external URL for a given purpose.
     *
     * @param string $url
     * @param string $purpose 'marketplace' | 'image' | 'video'
     * @param string|null &$errorMessage
     * @return bool
     */
    public function validate(string $url, string $purpose, ?string &$errorMessage = null): bool
    {
        $url = trim($url);

        // 1. Length check on raw input
        if (strlen($url) > self::MAX_BYTES) {
            $errorMessage = "Panjang URL melebihi batas maksimal " . self::MAX_BYTES . " byte.";
            return false;
        }

        // 2. Parse URL
        $parsed = parse_url($url);
        if ($parsed === false || !isset($parsed['scheme']) || !isset($parsed['host'])) {
            $errorMessage = "Format URL tidak valid.";
            return false;
        }

        // 3. Scheme must be strictly HTTPS
        if (strtolower($parsed['scheme']) !== 'https') {
            $errorMessage = "URL wajib menggunakan protokol HTTPS (port 443).";
            return false;
        }

        // 4. Port must be 443 if specified
        if (isset($parsed['port']) && (int) $parsed['port'] !== 443) {
            $errorMessage = "Port URL harus 443.";
            return false;
        }

        // 5. Userinfo check (no user:pass@host)
        if (!empty($parsed['user']) || !empty($parsed['pass'])) {
            $errorMessage = "URL tidak boleh mengandung autentikasi atau userinfo.";
            return false;
        }

        $host = strtolower($parsed['host']);

        // Normalize host (IDN to ASCII if function exists)
        if (function_exists('idn_to_ascii')) {
            $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($asciiHost !== false) {
                $host = strtolower($asciiHost);
            }
        }

        // Reconstruct normalized URL to check byte length
        $normalizedUrl = 'https://' . $host . (isset($parsed['path']) ? $parsed['path'] : '') .
            (isset($parsed['query']) ? '?' . $parsed['query'] : '') .
            (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');

        if (strlen($normalizedUrl) > self::MAX_BYTES) {
            $errorMessage = "Panjang URL hasil normalisasi melebihi batas " . self::MAX_BYTES . " byte.";
            return false;
        }

        // 6. Host allowlist check per purpose
        $allowlist = config("url_validation.allowlists.{$purpose}", []);
        if (empty($allowlist) || !is_array($allowlist)) {
            $errorMessage = "Allowlist host untuk kategori '{$purpose}' belum dikonfigurasi atau kosong.";
            return false;
        }

        $allowed = false;
        foreach ($allowlist as $allowedHost) {
            $allowedHost = strtolower(trim($allowedHost));
            if ($host === $allowedHost) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            $errorMessage = "Host '{$host}' tidak terdaftar dalam allowlist untuk {$purpose}.";
            return false;
        }

        // 7. Anti-SSRF / IP resolution check
        $resolvedIps = $this->resolveHostIps($host);
        if (empty($resolvedIps)) {
            $errorMessage = "Host '{$host}' tidak dapat diresolusi ke alamat IP publik.";
            return false;
        }

        foreach ($resolvedIps as $ip) {
            if (!$this->isPublicIp($ip)) {
                $errorMessage = "Host '{$host}' teresolusi ke alamat IP privat, loopback, atau non-publik ({$ip}). Akses ditolak.";
                return false;
            }
        }

        return true;
    }

    /**
     * Resolve host to IPv4 and IPv6 addresses.
     *
     * @param string $host
     * @return array<string>
     */
    protected function resolveHostIps(string $host): array
    {
        if (self::$dnsResolver !== null) {
            return (array) call_user_func(self::$dnsResolver, $host);
        }

        $ips = [];

        // IPv4 lookups
        $ipv4s = @gethostbynamel($host);
        if (is_array($ipv4s)) {
            $ips = array_merge($ips, $ipv4s);
        }

        // IPv6 lookups if dns_get_record is available
        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_AAAA);
            if (is_array($records)) {
                foreach ($records as $record) {
                    if (isset($record['ipv6'])) {
                        $ips[] = $record['ipv6'];
                    }
                }
            }
        }

        return array_unique($ips);
    }

    /**
     * Check if an IP address is a valid public internet address.
     * Rejects loopback, private, link-local, carrier NAT, broadcast, multicast, etc.
     */
    public function isPublicIp(string $ip): bool
    {
        // filter_var NO_PRIV_RANGE and NO_RES_RANGE
        $filtered = filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if ($filtered === false) {
            return false;
        }

        // Additional IPv4 checks
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            if ($long === false) {
                return false;
            }

            // 0.0.0.0/8
            if (($long & 0xFF000000) === 0x00000000) {
                return false;
            }

            // 127.0.0.0/8 (Loopback)
            if (($long & 0xFF000000) === 0x7F000000) {
                return false;
            }

            // 10.0.0.0/8 (Private)
            if (($long & 0xFF000000) === 0x0A000000) {
                return false;
            }

            // 100.64.0.0/10 (Carrier Grade NAT)
            if (($long & 0xFFC00000) === 0x64400000) {
                return false;
            }

            // 169.254.0.0/16 (Link-local)
            if (($long & 0xFFFF0000) === 0xA9FE0000) {
                return false;
            }

            // 172.16.0.0/12 (Private)
            if (($long & 0xFFF00000) === 0xAC100000) {
                return false;
            }

            // 192.168.0.0/16 (Private)
            if (($long & 0xFFFF0000) === 0xC0A80000) {
                return false;
            }

            // 224.0.0.0/4 (Multicast)
            if (($long & 0xF0000000) === 0xE0000000) {
                return false;
            }

            // 240.0.0.0/4 (Reserved)
            if (($long & 0xF0000000) === 0xF0000000) {
                return false;
            }

            // 255.255.255.255 (Broadcast)
            if ($long === -1 || $ip === '255.255.255.255') {
                return false;
            }
        }

        // Additional IPv6 checks
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $hex = bin2hex(inet_pton($ip));
            // ::1 (Loopback)
            if ($hex === str_repeat('0', 31) . '1') {
                return false;
            }
            // :: (Unspecified)
            if ($hex === str_repeat('0', 32)) {
                return false;
            }
            // fe80::/10 (Link-local)
            if (str_starts_with(strtolower($ip), 'fe80:')) {
                return false;
            }
            // fc00::/7 (Unique local)
            if (str_starts_with(strtolower($ip), 'fc') || str_starts_with(strtolower($ip), 'fd')) {
                return false;
            }
        }

        return true;
    }
}
