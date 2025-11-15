<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace controllers;

class AdminController {
    public function index($request, $response) {
        $response->json(['message' => 'Admin dashboard']);
    }

    public function create($request, $response) {
        $response->json(['message' => 'Create admin']);
    }

    public function store($request, $response) {
        $response->json(['message' => 'Store admin']);
    }

    public function edit($request, $response, $id) {
        $response->json(['message' => "Edit admin $id"]);
    }

    public function update($request, $response, $id) {
        $response->json(['message' => "Update admin $id"]);
    }

    public function delete($request, $response, $id) {
        $response->json(['message' => "Delete admin $id"]);
    }
}
