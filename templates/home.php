<?php
/**
 * templates/home.php
 * ---------------------------------------------------------------
 * Hero section trang chủ — Downloader TikTok
 * Variables: $config
 * ---------------------------------------------------------------
 */
$base = rtrim($config['base_url'], '/');
?>
<!-- ====== HERO ====== -->
<section class="hero-section app-container">
    <div class="hero-inner">
        <div class="hero-badge">
            <i class="fa-brands fa-tiktok"></i> Free · No watermark · HD
        </div>
        <h1 class="hero-title">
            Tải TikTok<br>
            <span class="gradient-text">Không logo &mdash; Cực nhanh</span>
        </h1>
        <p class="hero-sub">Hỗ trợ Video HD, Ảnh Slideshow &amp; Âm nhạc. Không cần đăng nhập.</p>

        <form method="POST" class="search-form" id="downloadForm" action="<?php echo $base; ?>/">
            <div class="search-bar">
                <i class="fa-solid fa-link search-icon"></i>
                <input
                    type="text"
                    class="search-input"
                    placeholder="Dán liên kết TikTok vào đây..."
                    name="tiktok-url"
                    required
                    autocomplete="off"
                    id="tiktokInput"
                    value="<?php echo isset($_POST['tiktok-url']) ? htmlspecialchars($_POST['tiktok-url']) : ''; ?>">
                <button class="btn-search" type="submit" id="submitBtn">
                    <i class="fa-solid fa-cloud-arrow-down" id="defaultIcon"></i>
                    <span id="btnText">Tải ngay</span>
                    <i class="fa-solid fa-spinner fa-spin d-none" id="spinner"></i>
                </button>
            </div>
        </form>

        <!-- Feature pills -->
        <div class="feature-pills">
            <span class="pill"><i class="fa-solid fa-video"></i> Video HD</span>
            <span class="pill"><i class="fa-solid fa-images"></i> Ảnh Slideshow</span>
            <span class="pill"><i class="fa-solid fa-music"></i> Âm nhạc MP3</span>
            <span class="pill"><i class="fa-solid fa-bolt"></i> Tốc độ cao</span>
        </div>
    </div>
</section>
