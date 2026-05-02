<?php

namespace App\Services;

use App\Core\HttpClient;
use App\Models\VideoData;

class TikTokService {
    private string $apiUrl;
    private ?string $proxy;

    /**
     * @param string $apiUrl TikWM API base URL
     * @param string|null $proxy Proxy tuỳ chọn (ip:port hoặc user:pass@ip:port)
     */
    public function __construct(string $apiUrl = 'https://www.tikwm.com/api/', ?string $proxy = null) {
        $this->apiUrl = rtrim($apiUrl, '/') . '/';
        $this->proxy  = $proxy;
    }

    /**
     * Lấy thông tin một video từ TikTok URL → trả về VideoData hoặc null
     */
    public function getVideoInfo(string $tiktokUrl): ?VideoData {
        $queryUrl = $this->apiUrl . '?url=' . urlencode($tiktokUrl) . '&hd=1';
        $response = HttpClient::get($queryUrl, false, $this->proxy);
        $data     = json_decode($response, true);

        // Accept code 0 (success) OR code 1 ("Transform success") as long as data exists
        if ($data && !empty($data['data']) && in_array((int)($data['code'] ?? -1), [0, 1], true)) {
            return new VideoData($data['data']);
        }

        return null;
    }

    /**
     * Tải thông tin NHIỀU video cùng lúc (multi-thread)
     * @param string[] $urls Danh sách TikTok URL
     * @param int $concurrency Số luồng đồng thời (khuyên: 5-10)
     * @return array<string, VideoData|null> key = URL ban đầu, value = VideoData hoặc null nếu lỗi
     */
    public function getMultiVideoInfo(array $urls, int $concurrency = 8): array {
        // Tạo danh sách API URLs tương ứng
        $apiUrls = [];
        $urlMap  = [];
        foreach ($urls as $tiktokUrl) {
            $apiUrl = $this->apiUrl . '?url=' . urlencode(trim($tiktokUrl)) . '&hd=1';
            $apiUrls[]           = $apiUrl;
            $urlMap[$apiUrl]     = trim($tiktokUrl);
        }

        // Gửi tất cả request song song
        $responses = HttpClient::getMulti($apiUrls, $concurrency);

        // Parse kết quả, map về TikTok URL ban đầu
        $results = [];
        foreach ($responses as $apiUrl => $body) {
            $tiktokUrl = $urlMap[$apiUrl] ?? $apiUrl;
            $data      = json_decode($body, true);

            // Accept code 0 OR code 1 ("Transform success") as long as data exists
            if ($data && !empty($data['data']) && in_array((int)($data['code'] ?? -1), [0, 1], true)) {
                $results[$tiktokUrl] = new VideoData($data['data']);
            } else {
                $results[$tiktokUrl] = null;
            }
        }

        return $results;
    }
}
