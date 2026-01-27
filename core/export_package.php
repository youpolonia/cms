<?php

if (!defined('CMS_ROOT')) {
    die('Access denied');
}

define('EXPORT_PACKAGE_OUTPUT_DIR', CMS_ROOT . '/temp/exports');

function export_build_package(): array
{
    try {
        if (!class_exists('ZipArchive')) {
            return [
                'ok' => false,
                'error' => 'ZipArchive extension is not available on this server.'
            ];
        }

        $outputDir = EXPORT_PACKAGE_OUTPUT_DIR;

        if (!is_dir($outputDir)) {
            if (!@mkdir($outputDir, 0775, true)) {
                return [
                    'ok' => false,
                    'error' => 'Unable to create export directory.'
                ];
            }
        }

        if (!is_writable($outputDir)) {
            return [
                'ok' => false,
                'error' => 'Export directory is not writable.'
            ];
        }

        $filename = 'cms-package-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.zip';
        $zipPath = $outputDir . '/' . $filename;
        $webPath = '/temp/exports/' . $filename;

        $zip = new ZipArchive();
        $openResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($openResult !== true) {
            return [
                'ok' => false,
                'error' => 'Unable to create ZIP archive.'
            ];
        }

        $roots = ['public', 'core', 'modules', 'config'];

        foreach ($roots as $rel) {
            $abs = CMS_ROOT . '/' . $rel;

            if (!is_dir($abs)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($abs, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $item) {
                $absolutePath = (string)$item;
                $relativePath = substr($absolutePath, strlen(CMS_ROOT) + 1);
                $localPath = str_replace('\\', '/', $relativePath);

                if ($item->isDir()) {
                    $zip->addEmptyDir($localPath);
                } else {
                    $zip->addFile($absolutePath, $localPath);
                }
            }
        }

        if (!$zip->close()) {
            return [
                'ok' => false,
                'error' => 'Failed to finalize ZIP archive.'
            ];
        }

        $size = @filesize($zipPath);
        if ($size === false) {
            $size = null;
        }

        return [
            'ok' => true,
            'file' => $zipPath,
            'url' => $webPath,
            'size' => $size
        ];

    } catch (Exception $e) {
        error_log('Export package error: ' . get_class($e) . ': ' . $e->getMessage());
        return [
            'ok' => false,
            'error' => 'Unexpected error while building export package.'
        ];
    }
}
