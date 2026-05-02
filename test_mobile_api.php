<?php
// require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Device;
use App\Core\Signature;
use App\Core\HttpClient;

// Mock autoloader if not working
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// numeric uid for kai_01s
$uid = '7586225256143356936';

$device = new Device();
$params = $device->buildQueryParams("max_cursor=0&count=10&user_id=$uid");
$sig = new Signature($params);
$headers = $sig->generate();
$baseHeaders = $device->getBaseHeaders();
$allHeaders = array_merge($baseHeaders, $headers);

// Convert allHeaders to curl format
$curlHeaders = [];
foreach ($allHeaders as $k => $v) $curlHeaders[] = "$k: $v";

$url = "https://api16-normal-c-alisg.tiktokv.com/aweme/v1/aweme/post/?" . $params;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => $curlHeaders,
]);
$raw = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Code: " . $info['http_code'] . "\n";
$d = json_decode($raw, true);
echo "Status Code: " . ($d['status_code'] ?? 'null') . "\n";
echo "Aweme Count: " . count($d['aweme_list'] ?? []) . "\n";

if (!empty($d['aweme_list'])) {
    foreach ($d['aweme_list'] as $v) {
        echo "- ID: " . $v['aweme_id'] . " | Desc: " . substr($v['desc'] ?? '', 0, 30) . "...\n";
    }
} else {
    echo "Raw response: " . substr($raw, 0, 500) . "\n";
}
