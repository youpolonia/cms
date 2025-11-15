<?php
namespace CMS\Export;

class ExcelExporter {
    public static function export(array $data, array $headers = []) {
        $output = '';
        
        // Add headers if provided
        if (!empty($headers)) {
            $output .= implode("\t", $headers) . "\n";
        }

        // Add data rows
        foreach ($data as $row) {
            $output .= implode("\t", $row) . "\n";
        }

        return $output;
    }
}
