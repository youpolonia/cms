<?php
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}
function sites_config_path(): string
{
    return CMS_ROOT . '/config/sites.json';
}
function sites_config_load(): array
{
    $path = sites_config_path();
    if (!file_exists($path) || !is_readable($path)) {
        return ['sites' => []];
    }
    $contents = file_get_contents($path);
    if ($contents === false) {
        return ['sites' => []];
    }
    $data = json_decode($contents, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        return ['sites' => []];
    }
    if (!isset($data['sites']) || !is_array($data['sites'])) {
        return ['sites' => []];
    }
    $normalized = [];
    foreach ($data['sites'] as $site) {
        if (!is_array($site)) {
            continue;
        }
        if (!isset($site['id']) || !is_string($site['id']) || trim($site['id']) === '') {
            continue;
        }
        $normalized[] = [
            'id' => (string)$site['id'],
            'name' => isset($site['name']) && is_string($site['name']) ? $site['name'] : '',
            'domain' => isset($site['domain']) && is_string($site['domain']) ? $site['domain'] : '*',
            'locale' => isset($site['locale']) && is_string($site['locale']) ? $site['locale'] : 'en_GB',
            'active' => isset($site['active']) ? (bool)$site['active'] : true
        ];
    }
    return ['sites' => $normalized];
}
function sites_get_all(): array
{
    $config = sites_config_load();
    return $config['sites'];
}
function sites_get_by_id(string $id): ?array
{
    $sites = sites_get_all();
    foreach ($sites as $site) {
        if ($site['id'] === $id) {
            return $site;
        }
    }
    return null;
}
function sites_get_default(): ?array
{
    $sites = sites_get_all();
    if (empty($sites)) {
        return null;
    }
    foreach ($sites as $site) {
        if ($site['active'] === true) {
            return $site;
        }
    }
    return $sites[0];
}
function sites_normalize_host(string $host): string
{
    $normalized = trim($host);
    $normalized = strtolower($normalized);
    $normalized = rtrim($normalized, '.');
    if ($normalized === '') {
        return '';
    }
    return $normalized;
}
function sites_match_domain(string $siteDomain, string $host): bool
{
    $siteDomain = trim(strtolower($siteDomain));
    $host = sites_normalize_host($host);
    if ($siteDomain === '*') {
        return true;
    }
    if ($siteDomain === '' || $host === '') {
        return false;
    }
    if ($siteDomain === $host) {
        return true;
    }
    if (str_starts_with($siteDomain, '*.')) {
        $base = substr($siteDomain, 2);
        if ($host === $base) {
            return true;
        }
        if (str_ends_with($host, '.' . $base)) {
            return true;
        }
    }
    return false;
}
function sites_resolve_for_host(string $host): ?array
{
    $sites = sites_get_all();
    $host = sites_normalize_host($host);
    if (empty($sites)) {
        return null;
    }
    if ($host === '') {
        return sites_get_default();
    }
    $activeMatches = [];
    foreach ($sites as $site) {
        if ($site['active'] === true && sites_match_domain($site['domain'], $host)) {
            $activeMatches[] = $site;
        }
    }
    if (!empty($activeMatches)) {
        return $activeMatches[0];
    }
    $allMatches = [];
    foreach ($sites as $site) {
        if (sites_match_domain($site['domain'], $host)) {
            $allMatches[] = $site;
        }
    }
    if (!empty($allMatches)) {
        return $allMatches[0];
    }
    return sites_get_default();
}
function sites_resolve_current(): ?array
{
    $host = '';
    if (isset($_SERVER['HTTP_HOST'])) {
        $host = (string)$_SERVER['HTTP_HOST'];
    } elseif (isset($_SERVER['SERVER_NAME'])) {
        $host = (string)$_SERVER['SERVER_NAME'];
    }
    return sites_resolve_for_host($host);
}
