<?php
/**
 * Gateway Controller - Handles API gateway requests
 */
class GatewayController {
    public function index() {
        return ['status' => 'success', 'message' => 'Gateway index'];
    }

    public function process() {
        $input = json_decode(file_get_contents('php://input'), true);
        return ['status' => 'success', 'data' => $input];
    }

    public function show($id) {
        return ['status' => 'success', 'id' => $id];
    }

    public function update($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        return ['status' => 'success', 'id' => $id, 'data' => $input];
    }

    public function delete($id) {
        return ['status' => 'success', 'deleted_id' => $id];
    }
}
