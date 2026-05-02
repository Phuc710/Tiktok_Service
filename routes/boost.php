<?php
/**
 * routes/boost.php — Boost View/Like/Share Routes
 * GET  /boost
 * POST /boost/resolve
 * POST /boost/run
 */

use App\Core\Auth;
use App\Services\BuffService;

// ── GET /boost → Trang Boost UI ───────────────────────────────────────────────
$router->get('/boost', function () { global $config;
    $pageTitle  = 'KaiTiktok · Boost Tương Tác';
    $activePage = 'booster';
    $extraHead  = '<link rel="stylesheet" href="' . $config['base_url'] . '/assets/css/booster.css">';
    renderTemplate('header');
    renderTemplate('booster');
    renderTemplate('footer');
});

// ── POST /boost/resolve → Resolve Video ID ────────────────────────────────────
$router->post('/boost/resolve', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    Auth::requireApiKey();

    $input   = readInput();
    $url     = trim($input['url'] ?? '');
    $videoId = BuffService::extractVideoId($url);

    if (!$videoId) {
        jsonResponse(['success' => false, 'error' => 'Không tìm được Video ID từ URL này.'], 400);
    }
    jsonResponse(['success' => true, 'video_id' => $videoId]);
});

// ── POST /boost/run → Run 1 batch ────────────────────────────────────────────
$router->post('/boost/run', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    Auth::requireApiKey();

    $input   = readInput();
    $videoId = trim($input['video_id'] ?? '');
    $action  = trim($input['action']   ?? 'view');

    if (!$videoId || !ctype_digit($videoId)) {
        jsonResponse(['success' => false, 'error' => 'Video ID không hợp lệ.'], 400);
    }

    $allowed = ['view', 'share', 'download', 'like'];
    if (!in_array($action, $allowed)) $action = 'view';

    $batchSize = (int)($input['batch_size'] ?? $config['boost']['batch_size'] ?? 100);
    $batchSize = max(10, min($batchSize, 500));

    $service = new BuffService($batchSize, 1, 0.05, $action);
    $stats   = $service->boostOneBatch($videoId);

    jsonResponse(['success' => true, 'action' => $action, 'stats' => $stats->toArray()]);
});
