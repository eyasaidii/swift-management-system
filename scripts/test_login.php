<?php

// Simple login test script: GET /login to obtain CSRF, then POST credentials.
$base = 'http://127.0.0.1:8000';
$cookie = sys_get_temp_dir().'/laravel_test_cookie.txt';
@unlink($cookie);

$ch = curl_init("$base/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$res = curl_exec($ch);
if ($res === false) {
    echo 'GET /login failed: '.curl_error($ch)."\n";
    exit(1);
}

// extract csrf token from meta tag or hidden input
$token = null;
if (preg_match('/name="csrf-token" content="([^"]+)"/', $res, $m)) {
    $token = $m[1];
}
if (! $token && preg_match('/name="_token" value="([^"]+)"/', $res, $m)) {
    $token = $m[1];
}
if (! $token) {
    // try to find in any input
    if (preg_match('/<input[^>]+name="_token"[^>]*value="([^"]+)"/i', $res, $m)) {
        $token = $m[1];
    }
}

if (! $token) {
    echo "CSRF token not found in /login response.\n";
    exit(1);
}

echo 'CSRF token found: '.substr($token, 0, 10)."...\n";

$post = [
    '_token' => $token,
    'email' => 'admin@example.com',
    'password' => 'password',
    'remember' => 'on',
];

curl_setopt($ch, CURLOPT_URL, "$base/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res2 = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($res2 === false) {
    echo "POST /login failed.\n";
    exit(1);
}

echo 'Final HTTP code: '.($info['http_code'] ?? 'unknown')."\n";
echo 'Effective URL: '.($info['url'] ?? 'unknown')."\n";

// check if redirected to admin dashboard
if (strpos($info['url'] ?? '', '/admin/dashboard') !== false) {
    echo "Login test succeeded: redirected to /admin/dashboard\n";
    exit(0);
}

// show a snippet of response to assist debugging
echo "Response snippet:\n";
echo substr($res2, 0, 1000)."\n";

exit(1);
