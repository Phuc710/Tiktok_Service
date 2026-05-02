<?php
/** @var \App\Models\VideoData $video */
/** @var array $config */
// Filename base
$videoId = $video->id ?: time();
$dlBase  = $config['base_url'] . '/download';
?>

<section class="app-container result-section" id="result">
    <div class="result-card">
        <div class="result-layout">
            <!-- Left: Media -->
            <div class="result-media">
                <div class="thumb-wrapper" style="background-color: #000;">
                    <?php if ($video->isSlideshow()) { ?>
                        <img src="<?php echo htmlspecialchars($video->cover); ?>" alt="TikTok Thumbnail"
                            style="object-fit: contain; background-color: #000;">
                    <?php } else { ?>
                        <video controls poster="<?php echo htmlspecialchars($video->cover); ?>"
                            style="width: 100%; height: 100%; object-fit: contain; outline: none;">
                            <source src="<?php echo htmlspecialchars($video->playUrl); ?>" type="video/mp4">
                            Trình duyệt của bạn không hỗ trợ thẻ video.
                        </video>
                    <?php } ?>
                </div>
            </div>

            <!-- Right: Content & Actions -->
            <div class="result-details">
                <div class="meta-section">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 1rem;">
                        <?php if (!empty($video->authorAvatar)) { ?>
                            <img src="<?php echo htmlspecialchars($video->authorAvatar); ?>" alt="Avatar"
                                style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid var(--card-border); object-fit: cover;">
                        <?php } ?>
                        <div>
                            <div class="nickname"><?php echo htmlspecialchars($video->authorNickname); ?></div>
                            <div class="username">@<?php echo htmlspecialchars($video->authorName); ?></div>
                        </div>
                    </div>

                    <?php if (!empty($video->title)) { ?>
                        <div class="video-desc">
                            <?php echo htmlspecialchars($video->title); ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fa-solid fa-play"></i>
                        <div class="stat-value"><?php echo number_format($video->viewCount); ?></div>
                        <div class="stat-label">Views</div>
                    </div>
                    <div class="stat-item">
                        <i class="fa-solid fa-heart"></i>
                        <div class="stat-value"><?php echo number_format($video->likeCount); ?></div>
                        <div class="stat-label">Likes</div>
                    </div>
                    <div class="stat-item">
                        <i class="fa-solid fa-comment"></i>
                        <div class="stat-value"><?php echo number_format($video->commentCount); ?></div>
                        <div class="stat-label">Comments</div>
                    </div>
                    <div class="stat-item">
                        <i class="fa-solid fa-share"></i>
                        <div class="stat-value"><?php echo number_format($video->shareCount); ?></div>
                        <div class="stat-label">Shares</div>
                    </div>
                </div>

                <div class="action-section">
                    <?php if ($video->isSlideshow()) { ?>
                        <h6 class="slideshow-title">
                            <i class="fa-solid fa-images slideshow-icon"></i> Photo Slideshow
                        </h6>
                        <div class="slideshow-grid">
                            <?php foreach ($video->images as $index => $imgUrl) { ?>
                                <a href="<?php echo $dlBase; ?>?url=<?php echo urlencode($imgUrl); ?>&filename=tiktok_img_<?php echo $videoId; ?>_<?php echo $index + 1; ?>.jpeg"
                                    class="btn-action btn-music slideshow-btn">
                                    <i class="fa-solid fa-download slideshow-btn-icon"></i> Ảnh <?php echo $index + 1; ?>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <a href="<?php echo $dlBase; ?>?url=<?php echo urlencode($video->playUrl); ?>&filename=tiktok_video_<?php echo $videoId; ?>.mp4"
                            class="btn-action btn-no-watermark">
                            <i class="fa-solid fa-download"></i> Tải Video
                        </a>
                    <?php } ?>

                    <?php if (!empty($video->musicUrl)) { ?>
                        <a href="<?php echo $dlBase; ?>?url=<?php echo urlencode($video->musicUrl); ?>&filename=tiktok_audio_<?php echo $videoId; ?>.mp3"
                            class="btn-action btn-music action-music-btn">
                            <i class="fa-solid fa-music"></i> Tải MP3
                        </a>
                    <?php } ?>
                </div>


            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        $('html, body').animate({
            scrollTop: ($('#result').offset().top - 50)
        }, 800);
    });
</script>