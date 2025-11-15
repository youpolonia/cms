<?php

namespace Includes\Controllers;

use Includes\Routing\Request;
use Includes\Auth\Auth;
use Includes\Validator;

abstract class Controller
{
    protected Request $request;
    protected Auth $auth;

    public function __construct(Request $request, Auth $auth)
    {
        $this->request = $request;
        $this->auth = $auth;
    }

    protected function view(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        require_once __DIR__ . "/../views/{$view}.php";
        return ob_get_clean();
    }

    protected function redirect(string $url, int $status = 302): void
    {
        header("Location: {$url}", true, $status);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function validate(array $rules): array
    {
        return Validator::validate($this->request->all(), $rules);
    }
}
