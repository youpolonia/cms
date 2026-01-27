<?php
require_once __DIR__ . '/../services/contentimportservice.php';

class XmlImportHandler implements ContentImportHandlerInterface {
    public function parse(string $content): array {
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            throw new RuntimeException("Failed to parse XML");
        }
        return json_decode(json_encode($xml), true);
    }
}

class JsonImportHandler implements ContentImportHandlerInterface {
    public function parse(string $content): array {
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("JSON parse error: " . json_last_error_msg());
        }
        return $data;
    }
}
