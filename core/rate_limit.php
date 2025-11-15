<?php
declare(strict_types=1);

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
if (!defined('RL_DIR'))   { define('RL_DIR', CMS_ROOT . '/logs/ratelimit'); }

function rl_key(string $id): string { return preg_replace('/[^a-z0-9:_\-]/i', '_', $id); }
function rl_path(string $id): string {
    if (!is_dir(RL_DIR)) @mkdir(RL_DIR, 0755, true);
    return RL_DIR . '/' . sha1($id) . '.json';
}

/** Returns [allowed(bool), remaining(int), reset(int unix)] */
function rl_allow(string $id, int $limit, int $window): array {
    $id  = rl_key($id); $p = rl_path($id); $now = time();
    $data = ['start'=>$now, 'count'=>0];
    if (is_file($p) && ($raw=@file_get_contents($p))!==false) {
        $j = @json_decode($raw, true);
        if (is_array($j) && isset($j['start'],$j['count'])) $data=$j;
    }
    if (($now - (int)$data['start']) >= $window) $data=['start'=>$now,'count'=>0];
    $allowed = ($data['count'] < $limit);
    if ($allowed) $data['count']++;
    $tmp=$p.'.tmp.'.uniqid('',true);
    if (@file_put_contents($tmp, json_encode($data), LOCK_EX)!==false) @rename($tmp,$p); else @unlink($tmp);
    $reset=(int)$data['start']+$window; $remaining=max(0,$limit-(int)$data['count']);
    return [$allowed,$remaining,$reset];
}

function rl_headers(string $prefix, int $limit, int $remaining, int $reset): void {
    if (headers_sent()) return;
    header("$prefix-Limit: $limit");
    header("$prefix-Remaining: $remaining");
    header("$prefix-Reset: $reset");
}
