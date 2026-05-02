<?php
/**
 * routes/web.php — Downloader & Page Routes
 * GET|POST /
 */

use App\Services\TikTokService;

// ── GET / → Trang chủ Downloader ─────────────────────────────────────────────
$router->get('/', function () { global $config;
    $pageTitle  = $config['app_title'] ?? 'KaiTiktok';
    $activePage = 'downloader';
    renderTemplate('header');
    renderTemplate('home');
    renderTemplate('footer');
});

// ── POST / → Xử lý tải video ─────────────────────────────────────────────────
$router->post('/', function () { global $config;
    $url     = trim($_POST['tiktok-url'] ?? '');
    $service = new TikTokService($config['api_url'], $config['proxy'] ?? null);
    $video   = null;
    $error   = null;

    if (empty($url)) {
        $error = 'Vui lòng nhập link TikTok.';
    } else {
        $video = $service->getVideoInfo($url);
        if (!$video) {
            $error = 'Không tìm thấy video. Video có thể bị private hoặc link không hợp lệ.';
        }
    }

    $pageTitle  = $config['app_title'] ?? 'KaiTiktok';
    $activePage = 'downloader';
    renderTemplate('header');

    if ($error) {
        ?>
        <section class="app-container">
            <div class="error-alert">
                <i class="fa-solid fa-circle-exclamation error-icon"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        </section>
        <?php
        renderTemplate('home');
    } else {
        renderTemplate('results', ['video' => $video]);
    }

    renderTemplate('footer');
});
