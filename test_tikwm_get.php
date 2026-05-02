<?php
$url = 'https://www.tikwm.com/api/user/posts/?unique_id=kai_01s&count=10&cursor=0';
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
]);
$raw = curl_exec($ch);
curl_close($ch);

$d = json_decode($raw, true);
echo "Code: " . ($d['code'] ?? 'null') . "\n";
echo "Count: " . count($d['data']['videos'] ?? []) . "\n";
if (empty($d['data']['videos'])) {
    echo "Raw response snippet: " . substr($raw, 0, 200) . "\n";
}
