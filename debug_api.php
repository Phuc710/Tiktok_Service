<?php
// Try different tikwm.com endpoints for user videos
$secUid = null; $uid = null;

// Step 1: get secUid + uid from user/info
$ch = curl_init();
curl_setopt_array($ch, [CURLOPT_URL=>'https://www.tikwm.com/api/user/info/',CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>'unique_id=kai_01s',CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'Mozilla/5.0']);
$r = curl_exec($ch); curl_close($ch);
$d = json_decode($r, true);
$user  = $d['data']['user'] ?? [];
$secUid = $user['secUid'] ?? '';
$uid    = $user['id'] ?? '';
echo "uid=$uid\nsecUid=" . substr($secUid,0,30) . "...\n\n";

// Try each endpoint
$endpoints = [
    "user/posts/?id=$uid&count=5&cursor=0",
    "user/posts/?unique_id=kai_01s&count=5&cursor=0",
    "user/posts/?secUid=" . urlencode($secUid) . "&count=5&cursor=0",
];

foreach ($endpoints as $ep) {
    $ch = curl_init();
    $url = 'https://www.tikwm.com/api/' . $ep;
    curl_setopt_array($ch, [CURLOPT_URL=>$url,CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'Mozilla/5.0']);
    $r = curl_exec($ch); curl_close($ch);
    $d2 = json_decode($r, true);
    echo "GET $ep\n";
    echo "  code=" . ($d2['code'] ?? 'null') . " videos=" . count($d2['data']['videos'] ?? []) . "\n";
}

// Try POST with secUid
$ch = curl_init();
curl_setopt_array($ch, [CURLOPT_URL=>'https://www.tikwm.com/api/user/posts/',CURLOPT_POST=>true,
    CURLOPT_POSTFIELDS=>"id=$uid&count=5&cursor=0",CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'Mozilla/5.0',
    CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded','Referer: https://www.tikwm.com/']]);
$r = curl_exec($ch); curl_close($ch);
$d3 = json_decode($r, true);
echo "\nPOST user/posts id=$uid + Referer header\n";
echo "  code=" . ($d3['code'] ?? 'null') . " raw=" . substr($r,0,100) . "\n";
