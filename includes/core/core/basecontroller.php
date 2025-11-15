<?php
namespace Includes\Core;

class BaseController {
    protected $request;
    protected $response;

    public function __construct() {
        $this->request = new class {
            public function getMethod() { return 'GET'; }
            public function getPath() { return '/'; }
        };
        $this->response = new class {
            public function setStatusCode($code) {}
            public function json($data) { return $data; }
        };
    }

    protected function initSession() {
        // Mock session initialization
        $_SESSION = [];
    }
}
