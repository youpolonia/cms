<?php
/**
 * Email Marketing API — /api/emailmarketing/*
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-auth.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-credits.php';
require_once CMS_ROOT . '/plugins/jessie-saas-core/includes/class-saas-api-gateway.php';
require_once __DIR__ . '/../includes/class-email-core.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim(preg_replace('#^/api/emailmarketing/?#', '', $uri), '/');
header('Content-Type: application/json; charset=utf-8');

use Plugins\JessieSaasCore\{SaasApiGateway, SaasCredits};
use Plugins\JessieEmailMarketing\EmailCore;

try {
    $gw = new SaasApiGateway(); $auth = $gw->authenticate();
    if (!$auth['success']) { http_response_code($auth['code'] ?? 401); echo json_encode($auth); exit; }
    $userId = $gw->getUserId();
    $core = new EmailCore($userId);
    $d = null;
    if (in_array($method, ['POST','PUT','PATCH'])) $d = json_decode(file_get_contents('php://input'), true) ?: $_POST;

    if ($method==='GET' && $path==='stats') { echo json_encode(['success'=>true,'stats'=>$core->getStats()]); exit; }

    // Lists
    if ($method==='GET' && $path==='lists') { echo json_encode(['success'=>true,'lists'=>$core->getLists()]); exit; }
    if ($method==='POST' && $path==='lists') { echo json_encode(['success'=>true,'id'=>$core->createList($d['name']??'', $d['description']??'')]); exit; }
    if ($method==='DELETE' && preg_match('#^lists/(\d+)$#',$path,$m)) { echo json_encode(['success'=>$core->deleteList((int)$m[1])]); exit; }

    // Subscribers
    if ($method==='GET' && preg_match('#^subscribers/(\d+)$#',$path,$m)) { echo json_encode(['success'=>true,'subscribers'=>$core->getSubscribers((int)$m[1])]); exit; }
    if ($method==='POST' && $path==='subscribers') { echo json_encode($core->addSubscriber((int)($d['list_id']??0), $d['email']??'', $d['name']??'', $d['tags']??'')); exit; }
    if ($method==='POST' && $path==='subscribers/import') { echo json_encode(['success'=>true,'result'=>$core->importSubscribers((int)($d['list_id']??0), $d['rows']??[])]); exit; }
    if ($method==='DELETE' && preg_match('#^subscribers/(\d+)$#',$path,$m)) { echo json_encode(['success'=>$core->removeSubscriber((int)$m[1])]); exit; }

    // Campaigns
    if ($method==='GET' && $path==='campaigns') { echo json_encode(['success'=>true,'campaigns'=>$core->getCampaigns()]); exit; }
    if ($method==='GET' && preg_match('#^campaigns/(\d+)$#',$path,$m)) { $c=$core->getCampaign((int)$m[1]); echo json_encode($c?['success'=>true,'campaign'=>$c]:['success'=>false,'error'=>'Not found']); exit; }
    if ($method==='POST' && $path==='campaigns') { echo json_encode(['success'=>true,'id'=>$core->saveCampaign($d)]); exit; }
    if ($method==='GET' && preg_match('#^campaigns/(\d+)/stats$#',$path,$m)) { echo json_encode(['success'=>true,'stats'=>$core->getCampaignStats((int)$m[1])]); exit; }

    // Templates
    if ($method==='GET' && $path==='templates') { echo json_encode(['success'=>true,'templates'=>$core->getTemplates()]); exit; }
    if ($method==='POST' && $path==='templates') { echo json_encode(['success'=>true,'id'=>$core->saveTemplate($d)]); exit; }

    // AI
    if ($method==='POST' && $path==='generate') {
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId,'emailcreator',3)) { http_response_code(402); echo json_encode(['success'=>false,'error'=>'Insufficient credits']); exit; }
        $result = $core->generateEmail($d['topic']??'', $d['tone']??'professional', $d['type']??'newsletter');
        if ($result['success']) $credits->consume($userId,'emailcreator',3,'Generate email');
        echo json_encode($result); exit;
    }
    if ($method==='POST' && $path==='generate-subjects') {
        $credits = new SaasCredits();
        if (!$credits->hasCredits($userId,'emailcreator',1)) { http_response_code(402); echo json_encode(['success'=>false,'error'=>'Insufficient credits']); exit; }
        $result = $core->generateSubjectLines($d['topic']??'', (int)($d['count']??5));
        if ($result['success']) $credits->consume($userId,'emailcreator',1,'Generate subjects');
        echo json_encode($result); exit;
    }

    http_response_code(404); echo json_encode(['success'=>false,'error'=>'Not found: '.$path]);
} catch (\Throwable $e) {
    error_log('[EmailMarketing API] '.$e->getMessage());
    http_response_code(500); echo json_encode(['success'=>false,'error'=>'Internal server error']);
}
