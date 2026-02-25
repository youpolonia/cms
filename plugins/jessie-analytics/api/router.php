<?php
/**
 * Analytics API — /api/analytics/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-auth.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-credits.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-api-gateway.php';
require_once __DIR__ . '/../includes/class-analytics-core.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^/api/analytics/?#', '', $uri), '/');
header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasApiGateway, SaasCredits};
use Plugins\JessieAnalytics\AnalyticsCore;

try {
    // Track endpoint is public (just needs API key in header)
    $gw = new SaasApiGateway(); $auth = $gw->authenticate();
    if (!$auth['success']) { http_response_code($auth['code'] ?? 401); echo json_encode($auth); exit; }
    $userId = $gw->getUserId();
    $core = new AnalyticsCore($userId);
    $d = null;
    if (in_array($method, ['POST','PUT'])) $d = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    $start = $_GET['start'] ?? date('Y-m-01'); $end = $_GET['end'] ?? date('Y-m-d 23:59:59');

    // Track event
    if ($method==='POST' && $path==='track') { echo json_encode(['success'=>true,'id'=>$core->trackEvent($d ?? [])]); exit; }

    // Overview
    if ($method==='GET' && $path==='overview') { echo json_encode(['success'=>true,'overview'=>$core->getOverview($start,$end)]); exit; }
    if ($method==='GET' && $path==='top-pages') { echo json_encode(['success'=>true,'pages'=>$core->getTopPages($start,$end)]); exit; }
    if ($method==='GET' && $path==='top-referrers') { echo json_encode(['success'=>true,'referrers'=>$core->getTopReferrers($start,$end)]); exit; }
    if ($method==='GET' && $path==='devices') { echo json_encode(['success'=>true,'devices'=>$core->getDeviceBreakdown($start,$end)]); exit; }
    if ($method==='GET' && $path==='trend') { echo json_encode(['success'=>true,'trend'=>$core->getDailyTrend($start,$end)]); exit; }
    if ($method==='GET' && $path==='events') { $type=$_GET['type']??null; echo json_encode(['success'=>true,'events'=>$core->getEvents($start,$end,$type)]); exit; }

    // Goals
    if ($method==='GET' && $path==='goals') { echo json_encode(['success'=>true,'goals'=>$core->getGoals()]); exit; }
    if ($method==='POST' && $path==='goals') { echo json_encode(['success'=>true,'id'=>$core->createGoal($d)]); exit; }

    // Reports
    if ($method==='GET' && $path==='reports') { echo json_encode(['success'=>true,'reports'=>$core->getReports()]); exit; }
    if ($method==='POST' && $path==='reports') { echo json_encode(['success'=>true,'id'=>$core->createReport($d)]); exit; }

    // AI Insights (costs 5 credits)
    if ($method==='POST' && $path==='insights') {
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId,'analytics',5)) { http_response_code(402); echo json_encode(['success'=>false,'error'=>'Insufficient credits']); exit; }
        $result = $core->generateInsights($d['start']??$start, $d['end']??$end);
        if ($result['success']) $credits->consume($userId,'analytics',5,'AI insights');
        echo json_encode($result); exit;
    }

    // New endpoints: funnel, utm, geo, realtime, bounce, conversion, session, heatmap, performance, export
    if ($method === 'POST' && $path === 'funnel') { echo json_encode(['success'=>true,'funnel'=>$core->analyzeFunnel($d['steps']??[], $d['start']??$start, $d['end']??$end)]); exit; }
    if ($method === 'GET' && $path === 'utm') { echo json_encode(['success'=>true,'utm'=>$core->getUTMBreakdown($start,$end)]); exit; }
    if ($method === 'GET' && $path === 'geo') { echo json_encode(['success'=>true,'geo'=>$core->getGeoBreakdown($start,$end)]); exit; }
    if ($method === 'GET' && $path === 'realtime') { $mins=(int)($_GET['minutes']??5); echo json_encode(['success'=>true,'events'=>$core->getRealtimeEvents($mins),'active_users'=>$core->getRealtimeCount($mins)]); exit; }
    if ($method === 'GET' && $path === 'bounce-rate') { echo json_encode(['success'=>true,'bounce_rate'=>$core->getBounceRate($start,$end)]); exit; }
    if ($method === 'GET' && $path === 'conversion-rate') { $goal=$_GET['goal']??'conversion'; echo json_encode(['success'=>true,'conversion_rate'=>$core->getConversionRate($start,$end,$goal)]); exit; }
    if ($method === 'GET' && preg_match('#^session/(.+)$#', $path, $m)) { echo json_encode(['success'=>true,'path'=>$core->getSessionPath($m[1])]); exit; }
    if ($method === 'GET' && $path === 'heatmap') { echo json_encode(['success'=>true,'heatmap'=>$core->getHourlyHeatmap($start,$end)]); exit; }
    if ($method === 'GET' && $path === 'performance') { echo json_encode(['success'=>true,'pages'=>$core->getPagePerformance($start,$end)]); exit; }
    if ($method === 'GET' && $path === 'export') { echo json_encode(['success'=>true]+$core->exportEvents($start,$end)); exit; }

    http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found: '.$path]);
} catch (\Throwable $e) {
    error_log('[Analytics API] '.$e->getMessage());
    http_response_code(500); echo json_encode(['success'=>false,'error'=>'Internal server error']);
}
