<?php
use Exception;

class SemanticVersionComparator {
    public function compare($version1, $version2) {
        $v1 = $this->parseVersion($version1);
        $v2 = $this->parseVersion($version2);

        $result = $this->compareParsedVersions($v1, $v2);

        return $result;
    }

    private function parseVersion($version) {
        if (!preg_match('/^(\d+)(\.(\d+))?(\.(\d+))?(-([0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*))?(\+([0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*))?$/i', $version, $matches)) {
            throw new Exception("Invalid semantic version format: $version");
        }

        list($major, $minor, $patch, $prerelease, $build) = $this->extractParts($matches);

        return compact('major', 'minor', 'patch', 'prerelease', 'build');
    }

    private function extractParts($matches) {
        $major = (int)($matches[1] ?? 0);
        $minor = (int)($matches[3] ?? 0);
        $patch = (int)($matches[5] ?? 0);
        $prerelease = $matches[7] ?? '';
        $build = $matches[9] ?? '';

        return [$major, $minor, $patch, $prerelease, $build];
    }

    private function compareParsedVersions($v1, $v2) {
        $this->compareNumericParts($v1, $v2);

        $prerelease1 = $v1['prerelease'];
        $prerelease2 = $v2['prerelease'];

        if ($prerelease1 && !$prerelease2) {
            return -1;
        } elseif (!$prerelease1 && $prerelease2) {
            return 1;
        } elseif ($prerelease1 && $prerelease2) {
            return $this->comparePrereleases($prerelease1, $prerelease2);
        }

        return 0;
    }

    private function compareNumericParts($v1, $v2) {
        foreach (['major', 'minor', 'patch'] as $part) {
            if ($v1[$part] < $v2[$part]) {
                return -1;
            } elseif ($v1[$part] > $v2[$part]) {
                return 1;
            }
        }
    }

    private function comparePrereleases($prerelease1, $prerelease2) {
        $parts1 = explode('.', $prerelease1);
        $parts2 = explode('.', $prerelease2);

        $maxLength = max(count($parts1), count($parts2));

        for ($i = 0; $i < $maxLength; $i++) {
            $part1 = $this->to_numeric($parts1[$i] ?? '');
            $part2 = $this->to_numeric($parts2[$i] ?? '');

            if ($part1 < $part2) {
                return -1;
            } elseif ($part1 > $part2) {
                return 1;
            }
        }

        return 0;
    }

    private function to_numeric($part) {
        if (is_numeric($part)) {
            return [(int)$part, 0];
        } else {
            return ['x', $part];
        }
    }
}
