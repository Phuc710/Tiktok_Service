<?php
/**
 * routes/profile.php — Profile Downloader Routes
 * POST /profile/resolve
 * POST /profile/videos
 * POST /profile/download-job
 * POST /profile/job-run
 * GET  /profile/job-status
 * GET  /profile/zip
 * GET  /profile       → UI page
 */

use App\Services\ProfileService;

// ── GET /profile → Trang Profile Downloader UI ────────────────────────────────
$router->get('/profile', function () { global $config;
    $pageTitle  = 'KaiTiktok · Profile Downloader';
    $activePage = 'profile';
    renderTemplate('header');
    renderTemplate('profile');
    renderTemplate('footer');
});

// ── POST /profile/resolve → Lấy info profile ─────────────────────────────────
$router->post('/profile/resolve', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    $input    = readInput();
    $inputUrl = trim($input['url'] ?? '');

    if (empty($inputUrl)) {
        jsonResponse(['success' => false, 'error' => 'Vui lòng nhập link profile hoặc @username.'], 400);
    }

    $service = new ProfileService($config['proxy'] ?? null);
    $profile = $service->resolveProfile($inputUrl);

    if (!$profile) {
        jsonResponse(['success' => false, 'error' => 'Không tìm thấy profile. Kiểm tra lại username.'], 404);
    }
    jsonResponse(['success' => true, 'data' => $profile->toArray()]);
});

// ── POST /profile/videos → Lấy danh sách video ───────────────────────────────
$router->post('/profile/videos', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    $input   = readInput();
    $secUid  = trim($input['sec_uid'] ?? $input['uid'] ?? '');
    $cursor  = (int)($input['cursor'] ?? 0);
    $count   = (int)($input['count']  ?? 20);

    if (empty($secUid)) {
        jsonResponse(['success' => false, 'error' => 'Thiếu sec_uid.'], 400);
    }

    $service = new ProfileService($config['proxy'] ?? null);
    $result  = $service->getVideos($secUid, $cursor, $count);
    jsonResponse(['success' => true, 'data' => $result]);
});

// ── POST /profile/download-job → Tạo job tải hàng loạt ──────────────────────
$router->post('/profile/download-job', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    $input    = readInput();
    $uid      = trim($input['uid']      ?? '');
    $username = trim($input['username'] ?? '');
    $videoIds = (array)($input['video_ids'] ?? []);

    if (empty($uid) || empty($videoIds)) {
        jsonResponse(['success' => false, 'error' => 'Thiếu uid hoặc danh sách video.'], 400);
    }

    $videoIds = array_values(array_unique(array_filter(array_map('trim', $videoIds))));
    if (count($videoIds) > 100) {
        jsonResponse(['success' => false, 'error' => 'Tối đa 100 video/lần.'], 400);
    }

    $service = new ProfileService($config['proxy'] ?? null);
    $jobId   = $service->createJob($uid, $username, $videoIds);
    jsonResponse(['success' => true, 'job_id' => $jobId]);
});

// ── POST /profile/job-run → Chạy 1 batch ─────────────────────────────────────
$router->post('/profile/job-run', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    set_time_limit(120);
    $input = readInput();
    $jobId = trim($input['job_id'] ?? '');

    if (empty($jobId) || !preg_match('/^job_[a-z0-9\.]+$/i', $jobId)) {
        jsonResponse(['success' => false, 'error' => 'Job ID không hợp lệ.'], 400);
    }

    $service = new ProfileService($config['proxy'] ?? null);
    $job     = $service->runJobBatch($jobId, 5);

    if (!$job) {
        jsonResponse(['success' => false, 'error' => 'Không tìm thấy job.'], 404);
    }
    jsonResponse(['success' => true, 'job' => $job]);
});

// ── GET /profile/job-status → Kiểm tra tiến độ ───────────────────────────────
$router->get('/profile/job-status', function () { global $config;
    header('Content-Type: application/json; charset=utf-8');
    $jobId = trim($_GET['id'] ?? '');

    if (empty($jobId)) {
        jsonResponse(['success' => false, 'error' => 'Thiếu job_id.'], 400);
    }

    $service = new ProfileService($config['proxy'] ?? null);
    $job     = $service->getJob($jobId);

    if (!$job) {
        jsonResponse(['success' => false, 'error' => 'Không tìm thấy job.'], 404);
    }
    jsonResponse(['success' => true, 'job' => $job]);
});

// ── GET /profile/zip → Download ZIP ──────────────────────────────────────────
$router->get('/profile/zip', function () { global $config;
    $jobId = trim($_GET['id'] ?? '');

    if (empty($jobId)) {
        http_response_code(400);
        exit('Missing job_id.');
    }

    $service = new ProfileService($config['proxy'] ?? null);
    if (!$service->streamZip($jobId)) {
        http_response_code(404);
        exit('ZIP not ready yet.');
    }
    exit();
});
