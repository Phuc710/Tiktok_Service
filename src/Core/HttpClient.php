<?php

namespace App\Core;

class HttpClient {

    /**
     * Lấy random User-Agent từ config
     */
    public static function randomAgent(): string {
        $config = require __DIR__ . '/../../config/config.php';
        $agents = $config['http']['user_agents'] ?? ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0'];
        return $agents[array_rand($agents)];
    }

    /**
     * Gửi request GET đơn
     * @param string $url URL cần fetch
     * @param bool $getFullUrl Nếu true, trả về URL sau khi redirect
     * @param string|null $proxy Proxy (format: "ip:port" or "user:pass@ip:port")
     */
    public static function get(string $url, bool $getFullUrl = false, ?string $proxy = null): string {
        $ch = curl_init();
        $config = require __DIR__ . '/../../config/config.php';
        $httpConfig = $config['http'];

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => self::randomAgent(),
            CURLOPT_ENCODING       => 'utf-8',
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => $httpConfig['connect_timeout'] ?? 15,
            CURLOPT_TIMEOUT        => $httpConfig['timeout'] ?? 30,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => $httpConfig['default_headers'] ?? [
                'Accept: application/json, text/html, */*',
                'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            ],
        ];

        if ($proxy) {
            $options[CURLOPT_PROXY] = $proxy;
            // Nếu proxy SOCKS5 thì uncomment dòng sau:
            // $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
        }

        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            $options[CURLOPT_IPRESOLVE] = CURL_IPRESOLVE_V4;
        }

        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);

        if ($getFullUrl) {
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            curl_close($ch);
            return $effectiveUrl;
        }

        curl_close($ch);
        return strval($data);
    }

    /**
     * Multi-thread fetch: Gửi nhiều request cùng lúc
     * @param string[] $urls Danh sách URL
     * @param int $concurrency Số luồng đồng thời
     * @return array key = URL, value = response body
     */
    public static function getMulti(array $urls, int $concurrency = 10): array {
        $results = [];
        $chunks = array_chunk($urls, $concurrency);

        foreach ($chunks as $chunk) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($chunk as $url) {
                $ch = curl_init();
                $config = require __DIR__ . '/../../config/config.php';
                $httpConfig = $config['http'];

                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_USERAGENT      => self::randomAgent(),
                    CURLOPT_ENCODING       => 'utf-8',
                    CURLOPT_TIMEOUT        => $httpConfig['timeout'] ?? 30,
                    CURLOPT_CONNECTTIMEOUT => $httpConfig['connect_timeout'] ?? 15,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$url] = $ch;
            }

            // Execute all handles concurrently
            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            } while ($running > 0);

            // Collect results
            foreach ($handles as $url => $ch) {
                $results[$url] = curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }

            curl_multi_close($mh);
        }

        return $results;
    }
}
