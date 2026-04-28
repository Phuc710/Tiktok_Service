<?php

function getContent($url, $geturl = false)
{
    $ch = curl_init();
    $options = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        CURLOPT_ENCODING => "utf-8",
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_MAXREDIRS => 10,
    );
    curl_setopt_array($ch, $options);
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($geturl === true) {
        return curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    }
    curl_close($ch);
    return strval($data);
}

function getKey($playable)
{
    $ch = curl_init();
    $headers = [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: en-US,en;q=0.9',
        'Range: bytes=0-200000'
    ];

    $options = array(
        CURLOPT_URL => $playable,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
        CURLOPT_ENCODING => "utf-8",
        CURLOPT_AUTOREFERER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_MAXREDIRS => 10,
    );
    curl_setopt_array($ch, $options);
    if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $tmp = explode("vid:", $data);
    if (count($tmp) > 1) {
        $key = trim(explode("%", $tmp[1])[0]);
    } else {
        $key = "";
    }
    return $key;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>KaiTiktok</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    
    <style type="text/css">
        :root {
            --primary: #ff0050;
            --secondary: #00f2fe;
            --bg-color: #0d1117;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #ffffff;
            --text-muted: #a1a1aa;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.2) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.2) 0, transparent 50%);
            color: var(--text-main);
            background-attachment: fixed;
            background-size: cover;
            overflow-x: hidden;
        }

        .bg-bubbles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .blob {
            position: absolute;
            filter: blur(90px);
            z-index: -1;
            opacity: 0.5;
            animation: move 10s infinite alternate cubic-bezier(0.4, 0, 0.2, 1);
        }
        .blob-1 {
            top: -10%; left: -10%;
            width: 500px; height: 500px;
            background: #ff0050;
            animation-delay: 0s;
        }
        .blob-2 {
            bottom: -10%; right: -10%;
            width: 600px; height: 600px;
            background: #00f2fe;
            animation-delay: -5s;
        }
        .blob-3 {
            top: 40%; left: 40%;
            width: 400px; height: 400px;
            background: #7000ff;
            animation-delay: -2s;
        }

        @keyframes move {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(50px, 50px) scale(1.1); }
        }

        .glass-container {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            z-index: 10;
        }


        h1.brand-title {
            font-weight: 800;
            font-size: 2.8rem;
            background: linear-gradient(90deg, #00f2fe, #4facfe, #ff0050, #f83600);
            background-size: 300%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradientText 5s ease infinite;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        @keyframes gradientText {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .search-input {
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 16px 25px;
            border-radius: 50px;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
        }

        .search-input:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--secondary);
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.3), inset 0 2px 4px rgba(0,0,0,0.2);
            outline: none;
        }
        
        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .btn-download-main {
            background: linear-gradient(45deg, #ff0050, #ff0080);
            border: none;
            color: white;
            padding: 16px 45px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 0, 80, 0.4);
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        
        .btn-download-main:active {
            transform: translateY(1px);
        }

        #result {
            margin-top: 40px;
            scroll-margin-top: 40px;
        }

        .result-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            animation: fadeInUp 0.6s ease-out forwards;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .thumb-wrapper {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            position: relative;
            aspect-ratio: 1/1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        
        .thumb-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .thumb-wrapper:hover img {
            transform: scale(1.08);
        }

        .meta-info {
            font-size: 1.2rem;
            margin-bottom: 25px;
            color: #e4e4e7;
        }
        
        .meta-info b {
            color: var(--secondary);
            font-weight: 600;
        }

        .btn-action {
            border-radius: 15px;
            padding: 14px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none !important;
        }

        .btn-watermark {
            background: rgba(255,255,255,0.05);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .btn-watermark:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateY(-2px);
            border-color: rgba(255,255,255,0.4);
        }

        .btn-no-watermark {
            background: linear-gradient(45deg, #00f2fe, #4facfe);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 242, 254, 0.4);
        }
        .btn-no-watermark:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 242, 254, 0.6);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        .stat-item {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 10px 5px;
            text-align: center;
        }
        .stat-item i {
            font-size: 1.1rem;
            color: var(--secondary);
            margin-bottom: 5px;
            display: block;
        }
        .stat-value {
            font-weight: 700;
            font-size: 0.9rem;
            color: #fff;
        }
        .stat-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .video-desc {
            font-size: 0.95rem;
            color: #d4d4d8;
            margin-top: 10px;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .info-alert {
            background: rgba(0, 242, 254, 0.1);
            border-left: 4px solid var(--secondary);
            color: #e4e4e7;
            padding: 15px;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-top: 20px;
            text-align: left;
        }

        .error-alert {
            background: rgba(255, 0, 80, 0.1);
            border-left: 4px solid var(--primary);
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            margin-top: 20px;
        }

        .footer {
            background: rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--glass-border);
            padding: 20px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 20;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .footer a {
            color: var(--secondary);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer a:hover {
            color: #fff;
        }

        .content-wrapper {
            padding-bottom: 80px;
        }

        .loading-icon {
            display: none;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        .sub-tools {
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        .sub-tools a {
            color: var(--secondary);
            text-decoration: none;
        }
        .sub-tools a:hover {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="bg-bubbles">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="content-wrapper">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10 mt-4">
                    <div class="glass-container text-center">
                        <h1 class="brand-title"><i class="fa-brands fa-tiktok mr-2"></i> KaiTiktok</h1>
                        <h5 class="mb-3">Công Cụ Tải Video TikTok Chất Lượng Cao</h5>
                        <p class="text-muted mb-4">Hỗ trợ tải video không logo chuẩn HD cực nhanh và hoàn toàn miễn phí!</p>
                        
                        <form method="POST" class="mt-4" id="downloadForm">
                            <div class="form-group mb-0 position-relative">
                                <input type="text" 
                                    class="search-input" 
                                    placeholder="Dán liên kết video TikTok vào đây..." 
                                    name="tiktok-url" 
                                    required autocomplete="off">
                            </div>
                            <button class="btn-download-main" type="submit" id="submitBtn">
                                <i class="fa-solid fa-cloud-arrow-down mr-2"></i> <span id="btnText">Tải Xuống Ngay</span>
                                <i class="fa-solid fa-circle-notch loading-icon ml-2" id="spinner"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php
            if (isset($_POST['tiktok-url']) && !empty($_POST['tiktok-url'])) {
                $url = trim($_POST['tiktok-url']);
                $apiUrl = "https://www.tikwm.com/api/?url=" . urlencode($url) . "&hd=1";
                $resp = getContent($apiUrl);
                $data = json_decode($resp, true);

                if ($data && isset($data['code']) && $data['code'] === 0) {
                    $videoData = $data['data'];
                    $thumb = isset($videoData['cover']) ? $videoData['cover'] : '';
                    $username = isset($videoData['author']['unique_id']) ? $videoData['author']['unique_id'] : 'Unknown';
                    $nickname = isset($videoData['author']['nickname']) ? $videoData['author']['nickname'] : '';
                    $title = isset($videoData['title']) ? $videoData['title'] : '';
                    
                    // Stats
                    $playCount = isset($videoData['play_count']) ? number_format($videoData['play_count']) : 0;
                    $diggCount = isset($videoData['digg_count']) ? number_format($videoData['digg_count']) : 0;
                    $commentCount = isset($videoData['comment_count']) ? number_format($videoData['comment_count']) : 0;
                    $shareCount = isset($videoData['share_count']) ? number_format($videoData['share_count']) : 0;
                    
                    // Check if it's a slideshow (images) or a regular video
                    $isImagePost = isset($videoData['images']) && is_array($videoData['images']) && count($videoData['images']) > 0;
                    
                    $cleanVideo = isset($videoData['hdplay']) ? $videoData['hdplay'] : (isset($videoData['play']) ? $videoData['play'] : '');

                    ?>
                    <script>
                        $(document).ready(function () {
                            $('html, body').animate({
                                scrollTop: ($('#result').offset().top - 30)
                            }, 800);
                        });
                    </script>
                    
                    <div class="row justify-content-center" id="result">
                        <div class="col-lg-8 col-md-10">
                            <div class="result-card">
                                <div class="row align-items-center">
                                    <div class="col-md-5 mb-4 mb-md-0">
                                        <div class="thumb-wrapper">
                                            <img src="<?php echo htmlspecialchars($thumb); ?>" alt="TikTok Thumbnail">
                                        </div>
                                    </div>
                                    <div class="col-md-7 text-center text-md-left px-md-4">
                                        <div class="meta-info mb-2">
                                            <b style="font-size: 1.3rem;"><?php echo htmlspecialchars($nickname); ?></b><br>
                                            <span style="font-size: 1rem; color: var(--text-muted);">@<?php echo htmlspecialchars($username); ?></span>
                                        </div>
                                        
                                        <?php if (!empty($title)) { ?>
                                            <div class="video-desc">
                                                <?php echo htmlspecialchars($title); ?>
                                            </div>
                                        <?php } ?>
                                        
                                        <div class="stats-grid">
                                            <div class="stat-item">
                                                <i class="fa-solid fa-play"></i>
                                                <div class="stat-value"><?php echo $playCount; ?></div>
                                                <div class="stat-label">Lượt xem</div>
                                            </div>
                                            <div class="stat-item">
                                                <i class="fa-solid fa-heart"></i>
                                                <div class="stat-value"><?php echo $diggCount; ?></div>
                                                <div class="stat-label">Lượt thích</div>
                                            </div>
                                            <div class="stat-item">
                                                <i class="fa-solid fa-comment"></i>
                                                <div class="stat-value"><?php echo $commentCount; ?></div>
                                                <div class="stat-label">Bình luận</div>
                                            </div>
                                            <div class="stat-item">
                                                <i class="fa-solid fa-share"></i>
                                                <div class="stat-value"><?php echo $shareCount; ?></div>
                                                <div class="stat-label">Chia sẻ</div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($isImagePost) { ?>
                                            <h6 class="text-white mb-3" style="font-weight: 600;"><i class="fa-solid fa-images mr-2"></i> Chế độ Ảnh (Slideshow)</h6>
                                            <div style="max-height: 250px; overflow-y: auto; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 10px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                                                <?php foreach($videoData['images'] as $index => $imgUrl) { ?>
                                                    <a href="<?php echo htmlspecialchars($imgUrl); ?>" target="_blank" class="btn btn-sm" style="background: rgba(255,255,255,0.1); color: white; border-radius: 8px; transition: 0.3s; font-size: 0.9rem;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                                                        <i class="fa-solid fa-download"></i> Ảnh <?php echo $index + 1; ?>
                                                    </a>
                                                <?php } ?>
                                            </div>
                                            <div class="info-alert mt-0">
                                                <i class="fa-solid fa-circle-info mr-1"></i> Nhấn vào nút để mở và lưu ảnh chất lượng cao.
                                            </div>
                                        <?php } else { ?>
                                            <button class="btn-action btn-no-watermark mb-2" onclick="window.location.href='<?php echo htmlspecialchars($cleanVideo); ?>'">
                                                <i class="fa-solid fa-download"></i> Tải Video (Không Logo HD)
                                            </button>
                                            
                                            <div class="info-alert">
                                                <i class="fa-solid fa-circle-info mr-1"></i> Mẹo: Nếu video tự động phát trên trình duyệt, nhấn <b>Ctrl + S</b> để lưu.
                                            </div>
                                        <?php } ?>
                                        
                                        <div class="sub-tools mt-3 text-center text-md-left">
                                            Dịch vụ code tool: <a target="_blank" href="https://www.kaishop.id.vn">Kai</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <script>
                        $(document).ready(function () {
                            $('html, body').animate({
                                scrollTop: ($('#result').offset().top - 30)
                            }, 800);
                        });
                    </script>
                    <div class="row justify-content-center" id="result">
                        <div class="col-lg-8 col-md-10">
                            <div class="glass-container error-alert text-center">
                                <i class="fa-solid fa-triangle-exclamation mb-2" style="font-size: 2rem;"></i><br>
                                <b>Không tìm thấy dữ liệu!</b><br>
                                Vui lòng kiểm tra lại liên kết TikTok (URL) của bạn và thử lại. Đảm bảo video/ảnh ở trạng thái công khai.
                                
                                <div class="sub-tools mt-3">
                                    Dịch vụ code tool: <a target="_blank" href="https://www.kaishop.id.vn" class="text-white">Kai</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0">
                &copy; <?php echo date("Y"); ?> <b>KaiTiktok</b> - Tải Video Không Logo
            </div>
            <div>
                Phát triển dựa trên <a target="_blank" href="https://www.kaishop.id.vn">Kai</a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        // Placeholder rotation
        const placeholders = [
            "https://www.tiktok.com/@username/video/123456...",
            "https://vm.tiktok.com/ZMxxxxxx/",
            "Dán liên kết video TikTok vào đây..."
        ];
        let pIndex = 0;
        
        window.setInterval(function () {
            pIndex = (pIndex + 1) % placeholders.length;
            $("input[name='tiktok-url']").attr("placeholder", placeholders[pIndex]);
        }, 3000);

        // Form submit animation
        $("#downloadForm").on('submit', function() {
            var btn = $("#submitBtn");
            btn.css("pointer-events", "none");
            btn.css("opacity", "0.8");
            $("#btnText").text("Đang xử lý...");
            $("#spinner").show();
        });
    </script>
</body>

</html>
