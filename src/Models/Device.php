<?php

namespace App\Models;

use Random\RandomException;

/**
 * Virtual Device Identity — giả lập thiết bị Android thật
 */
class Device {

    private const DEVICE_POOL = [
        ['model' => 'Pixel 7',            'os' => '13', 'api' => 33, 'brand' => 'Google'],
        ['model' => 'Pixel 8',            'os' => '14', 'api' => 34, 'brand' => 'Google'],
        ['model' => 'SM-S918B',           'os' => '14', 'api' => 34, 'brand' => 'Samsung'],
        ['model' => 'SM-A546B',           'os' => '13', 'api' => 33, 'brand' => 'Samsung'],
        ['model' => 'CPH2525',            'os' => '13', 'api' => 33, 'brand' => 'OPPO'],
        ['model' => '23049RAD8G',         'os' => '13', 'api' => 33, 'brand' => 'Xiaomi'],
        ['model' => 'RMX3686',            'os' => '13', 'api' => 33, 'brand' => 'Realme'],
        ['model' => 'V2309',              'os' => '13', 'api' => 33, 'brand' => 'vivo'],
        ['model' => 'CPH2609',            'os' => '14', 'api' => 34, 'brand' => 'OPPO'],
        ['model' => '24036RA7EC',         'os' => '14', 'api' => 34, 'brand' => 'Xiaomi'],
    ];

    public readonly string $model;
    public readonly string $brand;
    public readonly string $osVersion;
    public readonly int    $apiLevel;
    public readonly string $deviceId;
    public readonly string $installId;
    public readonly string $openUdid;
    public readonly string $sessionId;

    public function __construct() {
        $pool = self::DEVICE_POOL[array_rand(self::DEVICE_POOL)];

        $this->model      = $pool['model'];
        $this->brand      = $pool['brand'];
        $this->osVersion  = $pool['os'];
        $this->apiLevel   = $pool['api'];
        $this->deviceId   = (string)random_int(600_000_000_000_000, 699_999_999_999_999);
        $this->installId  = (string)random_int(100_000_000_000_000, 199_999_999_999_999);
        $this->openUdid   = bin2hex(random_bytes(8));
        $this->sessionId  = bin2hex(random_bytes(16));
    }

    /**
     * Tạo query params chuẩn Android TikTok
     */
    public function buildQueryParams(string $extra = ''): string {
        $base = http_build_query([
            'channel'         => 'googleplay',
            'aid'             => 1233,
            'app_name'        => 'musical_ly',
            'version_code'    => 380000,   // TikTok ~38.0.0 - stable
            'version_name'    => '38.0.0',
            'device_platform' => 'android',
            'device_type'     => $this->model,
            'os_version'      => $this->osVersion,
            'device_id'       => $this->deviceId,
            'openudid'        => $this->openUdid,
            'os_api'          => $this->apiLevel,
            'app_language'    => 'vi',
            'region'          => 'VN',
            'sys_region'      => 'VN',
            'tz_name'         => 'Asia/Ho_Chi_Minh',
            'tz_offset'       => 25200,
            'carrier_region'  => 'VN',
        ]);
        return $extra ? $base . '&' . $extra : $base;
    }

    /**
     * Lấy session pool từ .env TIKTOK_SESSIONS (cách nhau bằng dấu phẩy)
     * Fallback về session random nếu chưa set.
     */
    private static function pickSession(): string {
        $raw = \App\Core\EnvLoader::get('TIKTOK_SESSIONS', '');
        if (!empty($raw)) {
            $sessions = array_filter(array_map('trim', explode(',', $raw)));
            if (!empty($sessions)) {
                return $sessions[array_rand($sessions)];
            }
        }
        // Fallback: session random (thấp hơn rate)
        return bin2hex(random_bytes(16));
    }

    public function getCookies(): array {
        return [
            'sessionid'          => self::pickSession(),
            'device_id'          => $this->deviceId,
            'install_id'         => $this->installId,
            'openudid'           => $this->openUdid,
            'store-idc'          => 'alisg',
            'store-country-code' => 'vn',
        ];
    }

    public function getBaseHeaders(): array {
        $ua = 'com.ss.android.ugc.trill/380000 (Linux; U; Android ' . $this->osVersion
            . '; vi_VN; ' . $this->model . '; Build/TP1A.220624.014; Cronet/TTNetVersion:d8b72f22 2023-01-19 QuicVersion:9de21c6e 2023-01-10)';
        return [
            'Content-Type'    => 'application/x-www-form-urlencoded; charset=UTF-8',
            'User-Agent'      => $ua,
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'vi-VN,vi;q=0.9',
            'Connection'      => 'keep-alive',
            'sdk-version'     => '2',
            'X-SS-DP'         => '1233',
        ];
    }
}
