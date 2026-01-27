<?php
/**
 * Excel Exporter for CMS Reports
 * 
 * Provides functionality to export data to Excel format (XLSX)
 * using pure PHP without external dependencies.
 */
class ExcelExporter
{
    /**
     * Send appropriate headers for Excel download
     * 
     * @param string $filename The download filename (without extension)
     */
    public function sendHeaders(string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
    }

    /**
     * Generate and output Excel file from array data
     * 
     * @param array $data Array of data rows (each row is an array)
     * @param array $headers Optional array of column headers
     */
    public function generateFromArray(array $data, array $headers = []): void
    {
        require_once __DIR__ . '/simplexlsxgen.php';
        
        try {
            if (!empty($headers)) {
                array_unshift($data, $headers);
            }
            
            $xlsx = SimpleXLSXGen::fromArray($data);
            $xlsx->download();
        } catch (Exception $e) {
            error_log('Excel export failed: ' . $e->getMessage());
            throw new RuntimeException('Failed to generate Excel file');
        }
    }

    /**
     * Validate file path for security
     * 
     * @param string $filePath Path to validate
     * @return bool True if valid Excel file
     */
    public function validateFile(string $filePath): bool
    {
        $allowedExtensions = ['xlsx'];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        return in_array($extension, $allowedExtensions) && 
               is_file($filePath) && 
               is_readable($filePath);
    }

    /**
     * Generate Excel file with multiple worksheets
     *
     * @param array $worksheets Associative array of [sheetName => sheetData]
     * @param array $headers Optional array of column headers (applied to all sheets)
     */
    public function generateFromMultiArray(array $worksheets, array $headers = []): void
    {
        require_once __DIR__ . '/simplexlsxgen.php';
        
        try {
            $xlsx = new SimpleXLSXGen();
            
            foreach ($worksheets as $sheetName => $data) {
                if (!empty($headers)) {
                    array_unshift($data, $headers);
                }
                $xlsx->addWorksheet($data, $sheetName);
            }
            
            $xlsx->download();
        } catch (Exception $e) {
            error_log('Multi-sheet Excel export failed: ' . $e->getMessage());
            throw new RuntimeException('Failed to generate multi-sheet Excel file');
        }
    }
}
