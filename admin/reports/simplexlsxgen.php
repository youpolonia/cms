<?php
// Ensure this is the only copy being modified - duplicate found in VS Code installation dir
// Renamed addWorksheet() to generateWorksheet() to resolve naming conflict
/**
 * SimpleXLSXGen - Lightweight Excel XLSX generator
 * 
 * Pure PHP implementation without external dependencies
 * Based on https://github.com/shuchkin/simplexlsxgen
 */
class SimpleXLSXGen
{
    private $rows = [];
    private $worksheets = [];
    private $tempDir = '';
    private $zip;

    public static function fromArray(array $array): self
    {
        $xlsx = new self();
        $xlsx->rows = $array;
        return $xlsx;
    }

    public function addWorksheet(array $data, string $name = 'Sheet1'): void
    {
        $this->worksheets[$name] = $data;
    }

    public function download(string $filename = 'export.xlsx'): void
    {
        require_once __DIR__ . '/../../core/tmp_sandbox.php';
        $this->tempDir = cms_tmp_path('xlsx_' . uniqid());
        mkdir($this->tempDir);

        $this->createXlsxFile();
        $this->sendFile($filename);
        $this->cleanup();
    }

    private function createXlsxFile(): void
    {
        $this->zip = new ZipArchive();
        $this->zip->open($this->tempDir . '/temp.xlsx', ZipArchive::CREATE);

        $this->addCoreFiles();
        $this->generateWorksheet();
        $this->addRelationships();

        $this->zip->close();
    }

    private function addCoreFiles(): void
    {
        $this->zip->addFromString('[Content_Types].xml', 
            '<?xml version="1.0" encoding="UTF-8"
            <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
                <Default Extension="xml" ContentType="application/xml"/>
                <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
                <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
                <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
            </Types>'
        );
    }

    private function generateWorksheet(): void
    {
        $worksheet = '<?xml version="1.0" encoding="UTF-8"?>
        <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
            <sheetData>';

        foreach ($this->rows as $row) {
            $worksheet .= '<row>';
            foreach ($row as $cell) {
                $worksheet .= '<c><v>' . htmlspecialchars($cell, ENT_XML1) . '</v></c>';
            }
            $worksheet .= '</row>';
        }
        $worksheet .= '</sheetData></worksheet>';

        $this->zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);
    }

    private function addRelationships(): void
    {
        $this->zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8"?>
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
            </Relationships>'
        );
        $this->zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8"?>
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
                <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
            </Relationships>'
        );
        $this->zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8"?>
            <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
                <sheets>
                    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
                </sheets>
            </workbook>'
        );
    }

    private function sendFile(string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        readfile($this->tempDir . '/temp.xlsx');
    }

    private function cleanup(): void
    {
        unlink($this->tempDir . '/temp.xlsx');
        rmdir($this->tempDir);
    }
}
