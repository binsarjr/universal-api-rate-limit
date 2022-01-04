<?php

include_once __DIR__.'/helpers.php';
include_once __DIR__.'/rate_limiter.php';
include_once __DIR__.'/config.php';
include_once __DIR__.'/Postgres.php';

use ApiRateLimit\Driver\Postgres;
use ApiRateLimit\Helper;

date_default_timezone_set('Asia/Jakarta');

if (!defined('APIRATELIMIT_CLIENT_ID')) {
    define('APIRATELIMIT_CLIENT_ID', 'default');
}

$ip = Helper\ip();
$currentUrl = Helper\currentURL();
$curl = (new Helper\Php2Curl())->doAll();
$datetime = date('Y-m-d H:i:s');

$db = new Postgres();
if (!Postgres::$connected) {
    http_response_code(500);
    exit('ApiRateLimit: Database connection failed');
}

$db->query('INSERT INTO public.apiratelimit (url, curl, ip, client_id, created_at) VALUES ($1, $2, $3, $4, $5)', [$currentUrl, $curl, $ip, APIRATELIMIT_CLIENT_ID, $datetime]);

$isWhitelist = false;

if ($stream = fopen('whitelist.txt', 'r')) {
    $line = stream_get_contents($stream);

    if ((bool) preg_match("/^{$ip}$/m", $line)) {
        $isWhitelist = true;
    }

    fclose($stream);
}

if (!$isWhitelist) {
    if (!ApiRateLimit\check_within_rate_limit(md5($currentUrl), $ip, RATE_PER_PERIOD, PERIOD_IN_SECOND, 1)) {
        http_response_code(429);
        exit("Your IP has been restricted because of too many attempts. Please try again later.\n");
    }
}