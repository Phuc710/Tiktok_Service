<?php
/**
 * routes/download.php — File proxy download
 * GET /download?url=...&filename=...
 */

use App\Core\HttpClient;

$router->get('/download', function () { global $config;
    $url      = $_GET['url']      ?? '';
    $filename = $_GET['filename'] ?? 'tiktok_file';

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        exit('Invalid URL.');
    }

    // Whitelist: chỉ cho phép TikTok CDN domains
    $host    = parse_url($url, PHP_URL_HOST);
    $allowed = [
        'tiktokcdn.com', 'tiktokcdn-us.com', 'tiktokv.com',
        'tiktok.com', 'musical.ly', 'tikwm.com',
    ];

    $isAllowed = false;
    foreach ($allowed as $domain) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            $isAllowed = true;
            break;
        }
    }

    if (!$isAllowed) {
        http_response_code(403);
        exit('Domain not allowed.');
    }

    $safeFilename = preg_replace('/[^\w\.\-]/', '_', basename($filename));
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-cache');

    // Dùng cURL để bypass block (có UA, có proxy)
    echo HttpClient::get($url, false, $config['proxy'] ?? null);
    exit();
});
