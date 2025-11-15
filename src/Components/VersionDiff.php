<?php

namespace App\Components;

class VersionDiff
{
    public static function compareText(string $oldText, string $newText, bool $htmlAware = false): array
    {
        return \App\Helpers\VersionHelpers::text_diff($oldText, $newText, $htmlAware);
    }

    /**
     * @deprecated Use VersionHelpers::text_diff() instead
     */
    private static function textDiff(string $oldText, string $newText): array
    {
        return \App\Helpers\VersionHelpers::text_diff($oldText, $newText, false);
    }

    /**
     * @deprecated Use VersionHelpers::text_diff() with htmlAware=true instead
     */
    private static function htmlDiff(string $oldHtml, string $newHtml): array
    {
        return \App\Helpers\VersionHelpers::text_diff($oldHtml, $newHtml, true);
    }
}
