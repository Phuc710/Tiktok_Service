# 🚀 KaiTiktok — Roadmap & Service Architecture
> **Mục tiêu:** Biến KaiTiktok thành một nền tảng phân tích & khai thác TikTok đa dịch vụ,  
> bao gồm: Download · Buff tương tác · Profile scraping · Trend intelligence · Product matching.

---

## 📋 Danh sách Private API đang khai thác

### 1. tikwm.com (Đã tích hợp)
```
Base: https://www.tikwm.com/api/
- POST /             → info video đơn (play_url, cover, music, author, stats)
- POST /user/posts/  → danh sách video theo @username (cursor pagination)
- POST /user/info/   → thông tin profile (avatar, follower, following, likes)
- POST /feed/search/ → tìm video theo keyword/hashtag
```

### 2. TikTok Mobile API — api16-core-c-alisg (Đang khai thác Buff)
```
Base: https://api16-core-c-alisg.tiktokv.com
Auth: X-Gorgon + X-Khronos (tự ký, không cần OAuth)

BUFF:
- POST /aweme/v1/aweme/stats/          → view_delta, share_delta, download_delta
- POST /aweme/v1/commit/item/digg/     → like / unlike
- POST /aweme/v1/commit/follow/user/   → follow / unfollow
- POST /aweme/v1/comment/publish/      → đăng comment

DATA:
- GET  /aweme/v2/user/profile/         → profile đầy đủ (uid, nickname, stats)
- GET  /aweme/v1/aweme/post/           → video list của user (max_cursor pagination)
- GET  /aweme/v1/aweme/detail/         → chi tiết 1 video
- GET  /aweme/v1/trending/aweme/list/  → video trending toàn cầu
- GET  /aweme/v1/challenge/aweme/      → video theo hashtag
- GET  /aweme/v1/search/user/          → tìm kiếm user
```

### 3. TikTok Web API (cần cookie msToken + s_v_web_id)
```
Base: https://www.tiktok.com/api/
- GET /post/item_list/?secUid=... → danh sách video của profile (web)
- GET /user/detail/?uniqueId=...  → thông tin user (web)
- GET /recommend/item_list/       → For You feed
- GET /explore/item_list/         → Explore feed
```

---

## 🗺️ Services Roadmap (Thứ tự ưu tiên)

```
Phase 1 — Download & Scrape  [ĐANG LÀM]
  ✅ Single Video Download (video, ảnh, nhạc)
  ⏳ Profile Downloader — tải hàng loạt video theo @username

Phase 2 — Buff & Tương tác  [ĐANG LÀM]
  ✅ Buff View (play_delta)
  ✅ Buff Share (share_delta)
  ✅ Buff Download (download_delta)
  ✅ Buff Like (digg endpoint)
  ⏳ Buff Follow (follow endpoint)
  ⏳ Buff Comment (comment publish)

Phase 3 — Trend Intelligence  [TIẾP THEO]
  ⏳ Trend Scanner (trending videos)
  ⏳ Hashtag Tracker (theo dõi hashtag)
  ⏳ Creator Monitor (theo dõi đối thủ)
  ⏳ Sound Trend (âm thanh đang viral)

Phase 4 — Product Intelligence  [TƯƠNG LAI]
  ⏳ Product Radar (sản phẩm hot)
  ⏳ Source Matcher (tìm nguồn 1688/Taobao)
  ⏳ Shop Analytics (phân tích shop đối thủ)
  ⏳ Revenue Estimator (ước tính doanh thu)
```

---

## 🔧 Phase 1 — Profile Downloader (Làm ngay)

### API Call Flow

```
1. User nhập: https://www.tiktok.com/@kai_01s
2. POST /profile/resolve
   → gọi tikwm.com/api/user/info/?unique_id=kai_01s
   → trả về: uid, nickname, avatar, video_count, follower_count
3. POST /profile/videos  { uid, cursor: 0 }
   → gọi tikwm.com/api/user/posts/?uid=...&count=20&cursor=0
   → trả về: danh sách video + has_more + next_cursor
4. User chọn video → POST /profile/download-job  { uid, video_ids: [...] }
   → tạo job file: storage/jobs/{job_id}.json
   → worker download từng video → lưu vào storage/downloads/{job_id}/
   → khi xong → tạo storage/downloads/{job_id}.zip
5. GET /profile/job/{job_id}      → kiểm tra tiến độ (polling)
6. GET /profile/job/{job_id}/zip  → download file ZIP
```

### Models cần tạo
```
src/Models/ProfileData.php
  + username, nickname, avatar, uid
  + follower_count, following_count, video_count, like_count
  + toArray()

src/Models/ProfileVideoData.php
  + id, title, cover, play_url, music_url
  + view_count, like_count, comment_count, share_count
  + created_at
  + toArray()
```

### Services cần tạo
```
src/Services/ProfileService.php
  + resolveUsername(string $url): ProfileData
  + getVideos(string $uid, int $cursor, int $count): array
  + createDownloadJob(string $uid, array $videoIds): string  // returns job_id
  + getJobStatus(string $jobId): array
  + runJobBatch(string $jobId): void  // xử lý từng batch không timeout
```

### Routes cần thêm vào index.php
```
POST /profile/resolve         → resolve username → ProfileData JSON
POST /profile/videos          → list videos of uid (paginated)
POST /profile/download-job    → tạo download job
GET  /profile/job/{id}        → job status JSON
GET  /profile/job/{id}/zip    → stream ZIP file
```

### Storage structure
```
storage/
  jobs/
    {job_id}.json   → { status, total, done, failed, video_ids, uid }
  downloads/
    {job_id}/
      tiktok_video_123.mp4
      tiktok_video_456.mp4
    {job_id}.zip    → file ZIP hoàn chỉnh sau khi done
  cache/
    profile_{username}.json   → cache 30 phút tránh spam API
    user_videos_{uid}_{cursor}.json
```

---

## ⚡ Phase 2 — Buff Mở Rộng (Buff Comment)

### API đã biết
```php
// Endpoint comment
POST https://api16-core-c-alisg.tiktokv.com/aweme/v1/comment/publish/

// Body
aweme_id  = {video_id}
text      = {nội dung comment}
text_extra = []

// Cần: sessionid thật (account hợp lệ)
// Kỹ thuật: rotate qua pool session + random comment từ text pool
```

### Text pool tự động (tránh spam detection)
```
.env: COMMENT_POOL=Hay quá!,Nội dung chất lượng!,❤️,🔥🔥🔥,Tuyệt vời,Cực đỉnh
```

---

## 📊 Phase 3 — Trend Intelligence

### API Trending Video
```
GET https://api16-core-c-alisg.tiktokv.com/aweme/v1/trending/aweme/list/
Params: count=20, device_id=..., (signed)

Response: list aweme_list[] với đầy đủ stats
```

### API Hashtag (Challenge)
```
GET https://api16-core-c-alisg.tiktokv.com/aweme/v1/challenge/aweme/
Params: ch_id={hashtag_id}, count=20, cursor=0

// Cần resolve hashtag name → id trước
GET /aweme/v1/challenge/detail/?ch_name=viral
```

### Services cần tạo
```
src/Services/TrendService.php
  + getTrending(int $count): array         // video trending toàn cầu
  + getHashtagVideos(string $tag): array   // video theo hashtag
  + getCreatorVideos(string $uid): array   // video của 1 creator
  + computeTrendScore(array $video): float // tính trend score

src/Services/CacheService.php
  + get(string $key): ?array
  + set(string $key, array $data, int $ttl): void
  + flush(string $prefix): void
```

### Trend Score Formula
```php
// Dựa trên: velocity + engagement
$hoursSincePost = (time() - $created_at) / 3600;
$viewVelocity   = $view_count / max(1, $hoursSincePost);
$engagementRate = ($like_count + $comment_count + $share_count) / max(1, $view_count);

$trendScore = ($viewVelocity * 0.6) + ($engagementRate * 1000 * 0.4);
$label = match(true) {
    $trendScore > 5000 => 'HOT 🔥',
    $trendScore > 1000 => 'RISING ⬆️',
    $trendScore > 100  => 'STABLE ✅',
    default            => 'DEAD 💀',
};
```

---

## 🛍️ Phase 4 — Product Intelligence

### Flow
```
1. User nhập link video TikTok hoặc keyword
2. Hệ thống extract: caption, hashtag, tên sản phẩm (NER hoặc keyword match)
3. Gọi API 1688/Taobao để tìm hàng tương tự
4. Trả về: ảnh, giá nhập, MOQ, link, ước tính margin
```

### API nguồn hàng
```
ElimAPI (elim.asia):
  POST /api/search/1688     → tìm theo keyword
  POST /api/search/taobao   → tìm Taobao
  POST /api/product/detail  → chi tiết sản phẩm

DajiAPI (dajiapi.cn):
  POST /product/list        → tìm sản phẩm
  POST /product/shipping    → tính phí ship
```

### Revenue Estimator
```php
$buyPrice   = $product['price_cny'] * 3500; // VND
$shipFee    = 30_000; // VND/kg estimate
$tiktokFee  = 0.05;   // 5% TikTok Shop commission
$sellPrice  = $buyPrice * 3 + $shipFee;
$profit     = $sellPrice * (1 - $tiktokFee) - $buyPrice - $shipFee;
$margin     = round($profit / $sellPrice * 100, 1) . '%';
```

---

## 💰 Gói dịch vụ đề xuất

| Gói | Giá | Giới hạn |
|-----|-----|----------|
| **Trial** | Miễn phí | 5 download, 3 profile scan, 500 buff/ngày |
| **Starter** | 99k/tuần | 50 download/ngày, 10 profile, 5k buff/ngày |
| **Pro** | 699k/tháng | Không giới hạn download, trend dashboard, source matching |
| **Agency** | 2.5tr/tháng | Multi-user, API riêng, báo cáo tự động, webhook |

---

## 🗓️ Timeline code thực tế

```
Tuần 1:
  [x] Profile Downloader backend (ProfileService, routes)
  [x] Profile Downloader UI (tab mới)
  [x] Job system (storage/jobs + download worker)
  [x] ZIP download endpoint

Tuần 2:
  [ ] Buff Comment
  [ ] Trend Scanner (trending + hashtag)
  [ ] Caching system

Tuần 3:
  [ ] Trend dashboard UI
  [ ] Creator monitor
  [ ] Export CSV

Tuần 4:
  [ ] Product matching (ElimAPI)
  [ ] Revenue estimator
  [ ] SaaS billing (quota/rate limit theo plan)
```

---

## 🔒 Bảo mật & Anti-detection

```
1. Session rotation: pool 20+ sessionid thật, random per request
2. IP rotation: proxy list trong .env, random per batch
3. Rate limiting: không spam quá 100 req/giây/IP
4. User-Agent rotation: 10+ UA thật từ device pool
5. Adaptive delay: giảm tốc khi rate < 50%
6. X-Gorgon: tự ký, không cần service bên thứ 3
7. Cookie pool: rotate openudid, device_id, install_id
8. Server-side job: không expose raw TikTok URL ra client
```

---

*Tài liệu này mô tả kiến trúc mục tiêu của KaiTiktok Platform.*  
*Mỗi Phase nên được implement độc lập, test kỹ trước khi sang phase tiếp theo.*
