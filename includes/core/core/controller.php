<?php
namespace Core;

require_once __DIR__ . '/request.php';
require_once __DIR__ . '/response.php';

use Core\Request;
use Core\Response;
use Services\PathResolver;

interface ControllerInterface
{
    public function handle(Request $request): Response;
}

abstract class BaseController implements ControllerInterface
{
    protected Request $request;
    protected array $dependencies;

    public function __construct(Request $request, array $dependencies = [])
    {
        $this->request = $request;
        $this->dependencies = $dependencies;
    }

    protected function getDependency(string $key)
    {
        if (!isset($this->dependencies[$key])) {
            throw new \RuntimeException("Dependency {$key} not found");
        }
        return $this->dependencies[$key];
    }

    protected function view(string $template, array $data = []): Response
    {
        ob_start();
        extract($data);
        require_once PathResolver::templates("{$template}.php");
        $content = ob_get_clean();
        
        return new Response($content);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    protected function redirect(string $url): Response
    {
        return new Response('', 302, ['Location' => $url]);
    }
}
