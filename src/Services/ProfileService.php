<?php

namespace App\Services;

use App\Core\HttpClient;
use App\Models\ProfileData;
use App\Models\VideoData;

/**
 * ProfileService — Scrape danh sách video từ profile TikTok công khai
 *
 * API backend: tikwm.com (không cần OAuth, không cần account TikTok)
 * Hỗ trợ: @username, https://www.tiktok.com/@username
 */
class ProfileService {

    private const API_BASE   = 'https://www.tikwm.com/api/';
    private const PAGE_SIZE  = 20;  // số video mỗi lần call
    private const CACHE_TTL  = 1800; // 30 phút

    private ?string $proxy;

    public function __construct(?string $proxy = null) {
        $this->proxy = $proxy;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Normalize: trích username từ URL hoặc @handle
    // ─────────────────────────────────────────────────────────────────────────
    public static function extractUsername(string $input): ?string {
        $input = trim($input);

        // https://www.tiktok.com/@username hoặc @username
        if (preg_match('#(?:tiktok\.com/@|^@)([A-Za-z0-9_\.]+)#i', $input, $m)) {
            return $m[1];
        }
        // bare username
        if (preg_match('#^[A-Za-z0-9_\.]{2,24}$#', $input)) {
            return $input;
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Resolve profile: lấy thông tin user
    // ─────────────────────────────────────────────────────────────────────────
    public function resolveProfile(string $usernameOrUrl): ?ProfileData {
        $username = self::extractUsername($usernameOrUrl);
        if (!$username) return null;

        $cacheKey  = 'profile_' . $username;
        $cached    = $this->cacheGet($cacheKey);
        if ($cached) return new ProfileData($cached);

        $response = $this->apiPost('user/info/', ['unique_id' => $username]);

        if (!$response || ($response['code'] ?? -1) !== 0) return null;

        $data  = $response['data'] ?? [];
        // tikwm.com tách user + stats riêng — merge lại để ProfileData đọc 1 lần
        $user  = $data['user']  ?? $data;
        $stats = $data['stats'] ?? [];
        $merged = array_merge($stats, $user); // user fields thắng nếu trùng key

        $this->cacheSet($cacheKey, $merged, self::CACHE_TTL);
        return new ProfileData($merged);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Lấy danh sách video (có phân trang qua cursor)
    // ─────────────────────────────────────────────────────────────────────────
    public function getVideos(string $uid, int $cursor = 0, int $count = 20): array {
        $count = min(max($count, 1), 35);

        // TikTok web API — public endpoint, không cần login
        $params = http_build_query([
            'secUid'  => $uid,  // uid field chứa secUid sau khi resolve
            'count'   => $count,
            'cursor'  => $cursor,
            'coverFormat' => 2,
            'aid'     => 1988,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://www.tiktok.com/api/post/item_list/?' . $params,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Referer: https://www.tiktok.com/',
            ],
        ]);
        if ($this->proxy) curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        $raw = curl_exec($ch);
        curl_close($ch);

        $response = $raw ? json_decode($raw, true) : null;

        if (!$response || empty($response['itemList'])) {
            // Fallback: tikwm.com user/posts (khi không bị block)
            return $this->getVideosFallback($uid, $cursor, $count);
        }

        $videos = [];
        foreach ($response['itemList'] as $item) {
            $v = $item['video'] ?? [];
            $a = $item['author'] ?? [];
            $s = $item['stats']  ?? [];
            $videos[] = [
                'id'            => (string)($item['id'] ?? ''),
                'title'         => (string)($item['desc'] ?? ''),
                'cover'         => (string)($v['cover'] ?? $v['originCover'] ?? ''),
                'play_url'      => (string)($v['playAddr'] ?? $v['downloadAddr'] ?? ''),
                'music_url'     => (string)($item['music']['playUrl'] ?? ''),
                'play_count'    => (int)($s['playCount']    ?? 0),
                'like_count'    => (int)($s['diggCount']    ?? 0),
                'comment_count' => (int)($s['commentCount'] ?? 0),
                'share_count'   => (int)($s['shareCount']   ?? 0),
                'duration'      => (int)($v['duration']     ?? 0),
            ];
        }

        return [
            'videos'   => $videos,
            'has_more' => (bool)($response['hasMore']    ?? false),
            'cursor'   => (int) ($response['cursor']     ?? 0),
        ];
    }

    // Fallback dùng tikwm.com nếu TikTok web bị chặn
    private function getVideosFallback(string $uid, int $cursor, int $count): array {
        $response = $this->apiPost('user/posts/', [
            'id'     => $uid,
            'count'  => $count,
            'cursor' => $cursor,
        ]);

        if (!$response || ($response['code'] ?? -1) !== 0) {
            return ['videos' => [], 'has_more' => false, 'cursor' => 0];
        }

        $data   = $response['data'] ?? [];
        $videos = [];
        foreach ($data['videos'] ?? [] as $v) {
            $videos[] = [
                'id'            => (string)($v['video_id']     ?? $v['id']           ?? ''),
                'title'         => (string)($v['title']        ?? $v['desc']         ?? ''),
                'cover'         => (string)($v['cover']        ?? $v['origin_cover'] ?? ''),
                'play_url'      => (string)($v['play']         ?? $v['wmplay']       ?? ''),
                'music_url'     => (string)($v['music']        ?? ''),
                'play_count'    => (int)   ($v['play_count']   ?? $v['playCount']    ?? 0),
                'like_count'    => (int)   ($v['digg_count']   ?? $v['diggCount']    ?? 0),
                'comment_count' => (int)   ($v['comment_count'] ?? 0),
                'share_count'   => (int)   ($v['share_count']  ?? 0),
                'duration'      => (int)   ($v['duration']     ?? 0),
            ];
        }

        return [
            'videos'   => $videos,
            'has_more' => (bool)($data['hasMore'] ?? $data['has_more'] ?? false),
            'cursor'   => (int) ($data['cursor']  ?? 0),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tạo download job (lưu vào storage/jobs/)
    // ─────────────────────────────────────────────────────────────────────────
    public function createJob(string $uid, string $username, array $videoIds): string {
        $jobId   = uniqid('job_', true);
        $jobFile = $this->jobPath($jobId);

        $job = [
            'id'         => $jobId,
            'uid'        => $uid,
            'username'   => $username,
            'status'     => 'pending',   // pending | running | done | failed
            'video_ids'  => $videoIds,
            'total'      => count($videoIds),
            'done'       => 0,
            'failed'     => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ];

        $this->ensureDir(dirname($jobFile));
        file_put_contents($jobFile, json_encode($job, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $jobId;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Đọc trạng thái job
    // ─────────────────────────────────────────────────────────────────────────
    public function getJob(string $jobId): ?array {
        $jobFile = $this->jobPath($jobId);
        if (!file_exists($jobFile)) return null;
        return json_decode(file_get_contents($jobFile), true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Chạy 1 batch (gọi từ AJAX polling — không timeout)
    // ─────────────────────────────────────────────────────────────────────────
    public function runJobBatch(string $jobId, int $batchSize = 5): ?array {
        $job = $this->getJob($jobId);
        if (!$job || $job['status'] === 'done' || $job['status'] === 'failed') return $job;

        $job['status']     = 'running';
        $job['updated_at'] = time();

        // Tìm video chưa tải
        $pending = [];
        $dlDir   = $this->downloadDir($jobId);
        $this->ensureDir($dlDir);

        foreach ($job['video_ids'] as $vid) {
            $destVideo = $dlDir . '/tiktok_video_' . $vid . '.mp4';
            $destAudio = $dlDir . '/tiktok_audio_' . $vid . '.mp3';
            if (!file_exists($destVideo) && !file_exists($destAudio)) {
                $pending[] = $vid;
            }
        }

        // Lấy video info + tải batch
        $batch = array_slice($pending, 0, $batchSize);

        foreach ($batch as $vid) {
            try {
                $info = $this->getVideoInfoById($vid);
                if ($info && !empty($info['play'])) {
                    $dest = $dlDir . '/tiktok_video_' . $vid . '.mp4';
                    $this->downloadFile($info['play'], $dest);
                    $job['done']++;
                } else {
                    $job['failed']++;
                }
            } catch (\Throwable $e) {
                $job['failed']++;
            }
        }

        // Kiểm tra hoàn thành
        $processed = $job['done'] + $job['failed'];
        if ($processed >= $job['total']) {
            $job['status'] = 'done';
            // Tạo ZIP
            $this->createZip($jobId, $dlDir);
        }

        $job['updated_at'] = time();
        file_put_contents($this->jobPath($jobId), json_encode($job, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $job;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stream ZIP file ra browser
    // ─────────────────────────────────────────────────────────────────────────
    public function streamZip(string $jobId): bool {
        $job     = $this->getJob($jobId);
        $zipFile = $this->downloadDir($jobId) . '.zip';

        if (!$job || $job['status'] !== 'done' || !file_exists($zipFile)) return false;

        $username = $job['username'] ?? $jobId;
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="tiktok_' . $username . '.zip"');
        header('Content-Length: ' . filesize($zipFile));
        header('Cache-Control: no-cache');
        readfile($zipFile);
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers private
    // ─────────────────────────────────────────────────────────────────────────
    private function getVideoInfoById(string $videoId): ?array {
        $url      = 'https://www.tiktok.com/@user/video/' . $videoId;
        $response = $this->apiPost('', ['url' => $url, 'hd' => 1]);
        return ($response['code'] ?? -1) === 0 ? ($response['data'] ?? null) : null;
    }

    private function downloadFile(string $url, string $dest): void {
        $data = HttpClient::get($url, false, $this->proxy);
        if (!empty($data)) {
            file_put_contents($dest, $data);
        }
    }

    private function createZip(string $jobId, string $dir): void {
        if (!class_exists('ZipArchive')) return;

        $zipPath = $dir . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) return;

        foreach (glob($dir . '/*') as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
    }

    private function apiPost(string $endpoint, array $params): ?array {
        $url  = rtrim(self::API_BASE, '/') . '/' . ltrim($endpoint, '/');
        $body = http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        if ($this->proxy) curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        $raw = curl_exec($ch);
        curl_close($ch);

        return $raw ? json_decode($raw, true) : null;
    }

    private function cacheGet(string $key): ?array {
        $file = $this->storagePath('cache/' . preg_replace('/[^a-z0-9_]/', '_', $key) . '.json');
        if (!file_exists($file)) return null;
        $data = json_decode(file_get_contents($file), true);
        if (!$data || time() > ($data['_expires'] ?? 0)) {
            @unlink($file);
            return null;
        }
        unset($data['_expires']);
        return $data;
    }

    private function cacheSet(string $key, array $data, int $ttl): void {
        $file = $this->storagePath('cache/' . preg_replace('/[^a-z0-9_]/', '_', $key) . '.json');
        $this->ensureDir(dirname($file));
        $data['_expires'] = time() + $ttl;
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function storagePath(string $sub = ''): string {
        return dirname(__DIR__, 2) . '/storage' . ($sub ? '/' . ltrim($sub, '/') : '');
    }

    private function jobPath(string $jobId): string {
        return $this->storagePath('jobs/' . $jobId . '.json');
    }

    private function downloadDir(string $jobId): string {
        return $this->storagePath('downloads/' . $jobId);
    }

    private function ensureDir(string $dir): void {
        if (!is_dir($dir)) mkdir($dir, 0755, true);
    }
}
