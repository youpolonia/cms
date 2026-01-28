<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class VersionControlController
{
    public function __construct()
    {
        require_once CMS_CORE . '/versioncontrol.php';
    }

    public function index(Request $request): void
    {
        $contentType = $request->get('type', '');
        $contentId = $request->get('content_id', '');

        // Get storage stats
        $stats = \VersionControl::getStorageStats();

        // Get recent versions if content specified
        $versions = [];
        if ($contentType && $contentId) {
            $versions = \VersionControl::listVersions($contentType, $contentId);
            // Sort by created_at descending
            usort($versions, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }

        // Get all content types with versions
        $contentTypes = $this->getContentTypesWithVersions();

        render('admin/versioncontrol/index', [
            'stats' => $stats,
            'versions' => $versions,
            'contentTypes' => $contentTypes,
            'currentType' => $contentType,
            'currentContentId' => $contentId,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function view(Request $request): void
    {
        $contentType = $request->get('type', '');
        $contentId = $request->get('content_id', '');
        $versionId = $request->get('version_id', '');

        if (!$contentType || !$contentId || !$versionId) {
            Session::flash('error', 'Missing required parameters.');
            Response::redirect('/admin/version-control');
            return;
        }

        $version = \VersionControl::getVersion($contentType, $contentId, $versionId);

        if (!$version) {
            Session::flash('error', 'Version not found.');
            Response::redirect('/admin/version-control');
            return;
        }

        render('admin/versioncontrol/view', [
            'version' => $version,
            'versionId' => $versionId,
            'contentType' => $contentType,
            'contentId' => $contentId,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function compare(Request $request): void
    {
        $contentType = $request->get('type', '');
        $contentId = $request->get('content_id', '');
        $version1 = $request->get('version1', '');
        $version2 = $request->get('version2', '');

        if (!$contentType || !$contentId || !$version1 || !$version2) {
            Session::flash('error', 'Missing required parameters for comparison.');
            Response::redirect('/admin/version-control');
            return;
        }

        $v1Data = \VersionControl::getVersion($contentType, $contentId, $version1);
        $v2Data = \VersionControl::getVersion($contentType, $contentId, $version2);

        if (!$v1Data || !$v2Data) {
            Session::flash('error', 'One or both versions not found.');
            Response::redirect('/admin/version-control?type=' . urlencode($contentType) . '&content_id=' . urlencode($contentId));
            return;
        }

        try {
            $diff = \VersionControl::diffVersions($contentType, $contentId, $version1, $version2);
        } catch (\Exception $e) {
            $diff = [];
        }

        render('admin/versioncontrol/compare', [
            'version1' => $v1Data,
            'version2' => $v2Data,
            'version1Id' => $version1,
            'version2Id' => $version2,
            'diff' => $diff,
            'contentType' => $contentType,
            'contentId' => $contentId,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function restore(Request $request): void
    {
        $contentType = $request->post('type', '');
        $contentId = $request->post('content_id', '');
        $versionId = $request->post('version_id', '');

        if (!$contentType || !$contentId || !$versionId) {
            Session::flash('error', 'Missing required parameters.');
            Response::redirect('/admin/version-control');
            return;
        }

        $version = \VersionControl::getVersion($contentType, $contentId, $versionId);

        if (!$version) {
            Session::flash('error', 'Version not found.');
            Response::redirect('/admin/version-control');
            return;
        }

        // Create a new version with restored data (backup current state)
        \VersionControl::createVersion(
            $contentType,
            $contentId,
            $version['data'],
            "Restored from version {$versionId}"
        );

        Session::flash('success', "Version {$versionId} restored successfully. A new version was created with the restored data.");
        Response::redirect('/admin/version-control?type=' . urlencode($contentType) . '&content_id=' . urlencode($contentId));
    }

    public function delete(Request $request): void
    {
        $contentType = $request->post('type', '');
        $contentId = $request->post('content_id', '');
        $versionId = $request->post('version_id', '');

        if (!$contentType || !$contentId || !$versionId) {
            Session::flash('error', 'Missing required parameters.');
            Response::redirect('/admin/version-control');
            return;
        }

        if (\VersionControl::deleteVersion($contentType, $contentId, $versionId)) {
            Session::flash('success', 'Version deleted successfully.');
        } else {
            Session::flash('error', 'Failed to delete version.');
        }

        Response::redirect('/admin/version-control?type=' . urlencode($contentType) . '&content_id=' . urlencode($contentId));
    }

    public function purge(Request $request): void
    {
        $contentType = $request->post('type', '');
        $contentId = $request->post('content_id', '');
        $days = (int)$request->post('days', 30);

        if (!$contentType || !$contentId) {
            Session::flash('error', 'Missing required parameters.');
            Response::redirect('/admin/version-control');
            return;
        }

        $deleted = \VersionControl::purgeOldVersions($contentType, $contentId, $days);
        Session::flash('success', "Purged {$deleted} versions older than {$days} days.");
        Response::redirect('/admin/version-control?type=' . urlencode($contentType) . '&content_id=' . urlencode($contentId));
    }

    private function getContentTypesWithVersions(): array
    {
        $versionDir = \CMS_ROOT . '/cms_storage/versions/';
        $types = [];

        if (!is_dir($versionDir)) {
            return $types;
        }

        $dirs = scandir($versionDir);
        if ($dirs === false) {
            return $types;
        }

        foreach ($dirs as $type) {
            if ($type === '.' || $type === '..') continue;
            $typePath = $versionDir . $type;
            if (is_dir($typePath)) {
                $contentIds = [];
                $contentDirs = scandir($typePath);
                if ($contentDirs === false) continue;

                foreach ($contentDirs as $contentId) {
                    if ($contentId === '.' || $contentId === '..') continue;
                    if (is_dir($typePath . '/' . $contentId)) {
                        $jsonFiles = glob($typePath . '/' . $contentId . '/*.json');
                        $versionCount = is_array($jsonFiles) ? count($jsonFiles) : 0;
                        // Subtract 1 for _history.json if it exists
                        if (file_exists($typePath . '/' . $contentId . '/_history.json')) {
                            $versionCount--;
                        }
                        if ($versionCount > 0) {
                            $contentIds[] = ['id' => $contentId, 'versions' => $versionCount];
                        }
                    }
                }
                if (!empty($contentIds)) {
                    $types[$type] = $contentIds;
                }
            }
        }

        return $types;
    }
}
