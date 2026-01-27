<?php

interface CdnAdapter {
    /**
     * Uploads a file to CDN
     * @param string $localPath Local file path
     * @param string $cdnPath Destination path in CDN
     * @return bool True on success
     */
    public function upload(string $localPath, string $cdnPath): bool;

    /**
     * Deletes a file from CDN
     * @param string $cdnPath Path in CDN
     * @return bool True on success
     */
    public function delete(string $cdnPath): bool;

    /**
     * Checks if file exists in CDN
     * @param string $cdnPath Path in CDN
     * @return bool True if exists
     */
    public function exists(string $cdnPath): bool;

    /**
     * Gets public URL for a CDN file
     * @param string $cdnPath Path in CDN
     * @return string Public URL
     */
    public function getUrl(string $cdnPath): string;
}
