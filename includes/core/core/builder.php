<?php

namespace Core;

class Builder
{
    protected $pagesDir;

    public function __construct()
    {
        $this->pagesDir = __DIR__ . '/../../storage/pages';
        if (!file_exists($this->pagesDir)) {
            mkdir($this->pagesDir, 0755, true);
        }
    }

    public function editor($pageId = null)
    {
        $pageId = $pageId ?? uniqid('page_');
        return [
            'pageId' => $pageId
        ];
    }

    public function save($input)
    {
        if (empty($input['pageId']) || empty($input['content'])) {
            return $this->jsonResponse('error', 'Missing required fields', 400);
        }

        $filename = $this->pagesDir . '/' . $input['pageId'] . '.html';
        if (file_put_contents($filename, $input['content']) === false) {
            return $this->jsonResponse('error', 'Failed to save page', 500);
        }

        return $this->jsonResponse('success', 'Page saved successfully');
    }

    public function getContent($pageId)
    {
        $filename = $this->pagesDir . '/' . $pageId . '.html';
        if (!file_exists($filename)) {
            return $this->jsonResponse('error', 'Page not found', 404);
        }

        $content = file_get_contents($filename);
        if ($content === false) {
            return $this->jsonResponse('error', 'Failed to read page', 500);
        }

        return $this->jsonResponse('success', null, 200, ['content' => $content]);
    }

    protected function jsonResponse($status, $message = null, $code = 200, $data = [])
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = ['status' => $status];
        if ($message) {
            $response['message'] = $message;
        }
        
        return json_encode(array_merge($response, $data));
    }
}
