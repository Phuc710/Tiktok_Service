<?php
/**
 * routes/api.php — REST API Routes (JSON)
 * GET|POST /api
 */

use App\Core\Auth;
use App\Services\TikTokService;

$router->any('/api', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    Auth::requireApiKey();

    $input   = readInput();
    $service = new TikTokService($config['api_url'], $config['proxy'] ?? null);

    // Bulk: nhiều URL
    if (!empty($input['urls'])) {
        $urls = array_filter((array)$input['urls']);
        if (empty($urls)) jsonResponse(['success' => false, 'error' => 'urls array is empty'], 400);
        if (count($urls) > 50) jsonResponse(['success' => false, 'error' => 'Maximum 50 URLs per request'], 400);

        $concurrency = min((int)($input['concurrency'] ?? 8), 20);
        $results     = $service->getMultiVideoInfo($urls, $concurrency);

        $response = [];
        foreach ($results as $url => $video) {
            $response[] = [
                'url'    => $url,
                'status' => $video ? 'ok' : 'error',
                'data'   => $video ? $video->toArray() : null,
            ];
        }
        jsonResponse(['success' => true, 'data' => $response]);
    }

    // Single URL
    if (!empty($input['url'])) {
        $video = $service->getVideoInfo(trim($input['url']));
        if (!$video) jsonResponse(['success' => false, 'error' => 'Video not found or private.'], 404);
        jsonResponse(['success' => true, 'data' => $video->toArray()]);
    }

    jsonResponse(['success' => false, 'error' => 'Missing parameter: url or urls'], 400);
});
