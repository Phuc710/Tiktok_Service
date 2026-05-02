<?php
$secUid = 'MS4wLjABAAAAOe80EnLmVfDuuwGHIerRXVad5AmCAMnZIHm9ArOdiCfcZCQ3R4PWq-9SQnKAceJf';
$params = http_build_query([
    'secUid' => $secUid,
    'count' => 10,
    'cursor' => 0,
    'coverFormat' => 2,
    'aid' => 1988,
]);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.tiktok.com/api/post/item_list/?' . $params,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Referer: https://www.tiktok.com/',
    ],
]);
$raw = curl_exec($ch);
curl_close($ch);

$d = json_decode($raw, true);
echo "Status Code: " . ($d['statusCode'] ?? 'null') . "\n";
echo "Item Count: " . count($d['itemList'] ?? []) . "\n";

if (!empty($d['itemList'])) {
    foreach ($d['itemList'] as $item) {
        echo "- ID: " . $item['id'] . " | Desc: " . substr($item['desc'] ?? '', 0, 30) . "...\n";
    }
} else {
    echo "Raw response snippet: " . substr($raw, 0, 200) . "\n";
}
