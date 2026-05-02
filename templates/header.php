<?php
/**
 * templates/header.php
 * ---------------------------------------------------------------
 * Shared HTML header dùng chung cho TẤT CẢ các trang.
 * Chỉ chứa: <html>, <head>, assets, navbar.
 * KHÔNG chứa nội dung riêng của bất kỳ trang nào.
 *
 * Biến inject trước khi include:
 *   $config      — array từ config/config.php  (bắt buộc)
 *   $pageTitle   — string, tiêu đề tab          (tuỳ chọn)
 *   $activePage  — 'downloader' | 'booster'     (tuỳ chọn)
 *   $extraHead   — HTML string thêm vào <head>  (tuỳ chọn)
 * ---------------------------------------------------------------
 */

$pageTitle  = $pageTitle  ?? ($config['app_name'] ?? 'KaiTiktok');
$activePage = $activePage ?? '';
$base       = rtrim($config['base_url'], '/'); // luôn không có trailing slash
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Shared CSS -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/layout.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body>
    <div class="bg-mesh"></div>

    <!-- ====== SHARED NAVBAR ====== -->
    <header class="main-header">
        <div class="header-container">
            <a href="<?php echo $base; ?>/" class="brand-title">
                <i class="fa-brands fa-tiktok brand-icon"></i> KaiTiktok
            </a>
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo $base; ?>/"
                           class="nav-link <?php echo $activePage === 'downloader' ? 'nav-active' : ''; ?>">
                            <i class="fa-solid fa-download"></i> Downloader
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base; ?>/boost"
                           class="nav-link <?php echo $activePage === 'booster' ? 'nav-active' : ''; ?>">
                            <i class="fa-solid fa-rocket"></i> Boost
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $base; ?>/profile"
                           class="nav-link <?php echo $activePage === 'profile' ? 'nav-active' : ''; ?>">
                            <i class="fa-solid fa-user-group"></i> Profile DL
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="fa-solid fa-headset"></i> Liên hệ
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <!-- ====== END NAVBAR ====== -->

    <div class="app-wrapper">
        <main class="app-main">
