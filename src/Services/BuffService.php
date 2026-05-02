<?php

namespace App\Services;

use App\Core\Signature;
use App\Models\Device;
use App\Models\BoostStats;

/**
 * BuffService — Điều phối tăng tương tác TikTok
 *
 * Hỗ trợ các action:
 *   'view'     → play_delta      (endpoint stats)
 *   'share'    → share_delta     (endpoint stats)
 *   'download' → download_delta  (endpoint stats)
 *   'like'     → digg endpoint   (cần sessionid thật)
 *   'follow'   → follow endpoint (cần sessionid thật + user_id)
 */
class BuffService {

    // ── Endpoints ─────────────────────────────────────────────────────────────
    private const ENDPOINT_STATS  = 'https://api16-core-c-alisg.tiktokv.com/aweme/v1/aweme/stats/';
    private const ENDPOINT_LIKE   = 'https://api16-core-c-alisg.tiktokv.com/aweme/v1/commit/item/digg/';
    private const ENDPOINT_FOLLOW = 'https://api16-core-c-alisg.tiktokv.com/aweme/v1/commit/follow/user/';

    // Các action dùng endpoint STATS (không cần session thật)
    private const STATS_ACTIONS = ['view', 'share', 'download'];

    private int    $batchSize;
    private int    $maxBatches;
    private float  $delayBetweenBatches;
    private string $action; // 'view' | 'share' | 'download' | 'like' | 'follow'

    private BoostStats $stats;

    public function __construct(
        int    $batchSize  = 100,
        int    $maxBatches = 50,
        float  $delay      = 0.05,
        string $action     = 'view'
    ) {
        $this->batchSize           = $batchSize;
        $this->maxBatches          = $maxBatches;
        $this->delayBetweenBatches = $delay;
        $this->action              = in_array($action, ['view','share','download','like','follow'])
                                     ? $action : 'view';
        $this->stats               = new BoostStats();
    }

    public function getStats(): BoostStats { return $this->stats; }

    // ─────────────────────────────────────────────────────────────────────────
    // Build payload theo action type
    // ─────────────────────────────────────────────────────────────────────────
    private function buildPostFields(string $videoId): string {
        return match ($this->action) {
            'view'     => http_build_query(['item_id' => $videoId, 'play_delta'     => 1, 'action_time' => time()]),
            'share'    => http_build_query(['item_id' => $videoId, 'share_delta'    => 1, 'action_time' => time()]),
            'download' => http_build_query(['item_id' => $videoId, 'download_delta' => 1, 'action_time' => time()]),
            'like'     => http_build_query(['aweme_id' => $videoId, 'type' => 1]),
            'follow'   => http_build_query(['user_id'  => $videoId, 'type' => 1]), // videoId = userId khi follow
            default    => http_build_query(['item_id' => $videoId, 'play_delta' => 1, 'action_time' => time()]),
        };
    }

    private function getEndpointUrl(Device $device): string {
        $base = match ($this->action) {
            'like'   => self::ENDPOINT_LIKE,
            'follow' => self::ENDPOINT_FOLLOW,
            default  => self::ENDPOINT_STATS,
        };
        return $base . '?' . $device->buildQueryParams();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Build one signed curl handle
    // ─────────────────────────────────────────────────────────────────────────
    private function buildHandle(string $videoId): \CurlHandle {
        $device     = new Device();
        $postFields = $this->buildPostFields($videoId);
        $url        = $this->getEndpointUrl($device);

        $cookies    = $device->getCookies();
        $cookieStr  = http_build_query($cookies);
        $params     = $device->buildQueryParams();
        $sigHeaders = (new Signature($params, $postFields, $cookieStr))->generate();

        $allHeaders = array_merge($device->getBaseHeaders(), $sigHeaders);
        $curlHeaders = [];
        foreach ($allHeaders as $k => $v) {
            $curlHeaders[] = "$k: $v";
        }

        $cookieHeader = '';
        foreach ($cookies as $k => $v) {
            $cookieHeader .= "$k=$v; ";
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $curlHeaders,
            CURLOPT_COOKIE         => rtrim($cookieHeader, '; '),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING       => 'gzip',
        ]);

        return $ch;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Run một batch song song
    // ─────────────────────────────────────────────────────────────────────────
    private function runBatch(string $videoId): void {
        $mh      = curl_multi_init();
        $handles = [];

        for ($i = 0; $i < $this->batchSize; $i++) {
            $ch        = $this->buildHandle($videoId);
            $handles[] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        $running = null;
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh, 0.01);
        } while ($running > 0 && $status === CURLM_OK);

        foreach ($handles as $ch) {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);

            if ($httpCode === 200 && empty($error)) {
                $this->stats->recordSuccess();
            } else {
                $this->stats->recordFailure();
            }

            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }

        curl_multi_close($mh);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Adaptive delay theo success rate
    // ─────────────────────────────────────────────────────────────────────────
    private function adaptiveDelay(): void {
        $rate = $this->stats->successRate();
        if ($rate < 40) {
            usleep((int)(($this->delayBetweenBatches * 4) * 1_000_000));
        } elseif ($rate < 70) {
            usleep((int)(($this->delayBetweenBatches * 2) * 1_000_000));
        } else {
            usleep((int)($this->delayBetweenBatches * 1_000_000));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Trích xuất video ID từ URL TikTok
    // ─────────────────────────────────────────────────────────────────────────
    public static function extractVideoId(string $url): ?string {
        if (preg_match('#/video/(\d{10,20})#', $url, $m)) return $m[1];
        if (preg_match('#/photo/(\d{10,20})#', $url, $m)) return $m[1];
        if (preg_match('#(\d{15,20})#',         $url, $m)) return $m[1];
        $resolved = @get_headers($url, true)['Location'] ?? null;
        if ($resolved && preg_match('#/video/(\d{10,20})#', $resolved, $m)) return $m[1];
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Chạy TOÀN BỘ chiến dịch (blocking)
    // ─────────────────────────────────────────────────────────────────────────
    public function boost(string $videoId): BoostStats {
        for ($i = 0; $i < $this->maxBatches; $i++) {
            $this->runBatch($videoId);
            $this->adaptiveDelay();
        }
        return $this->stats;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public: Chạy 1 đợt (AJAX real-time)
    // ─────────────────────────────────────────────────────────────────────────
    public function boostOneBatch(string $videoId): BoostStats {
        $this->runBatch($videoId);
        return $this->stats;
    }
}
