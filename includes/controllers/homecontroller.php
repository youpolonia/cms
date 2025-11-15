<?php
namespace Includes\Controllers;

use Core\View;
use Core\Request;
use Core\Response;

class HomeController {
    protected $view;
    protected $contentModel;

    public function __construct() {
        $this->view = new View(__DIR__ . '/../../templates');
        $this->view->setLayout('layouts/main');
        $this->contentModel = new ContentPageModel();
    }

    public function handle(Request $request): Response {
        try {
            $config = require __DIR__ . '/../../config/content.php';
            
            if (empty($config['home_page']['page_id'])) {
                throw new \RuntimeException('Missing home page configuration');
            }

            // Debug log the config and query
            error_log("HomeController config: " . print_r($config['home_page'], true));
            
            // Load home page content from database
            $page = $this->contentModel->getPublishedPage($config['home_page']['page_id']);
            
            if ($page) {
                error_log("HomeController: Loaded published page: " . print_r($page, true));
                $content = $this->view->render('home/index', [
                    'title' => $page['title'],
                    'content' => $this->processMarkdown($page['content'])
                ]);
            } else {
                error_log("HomeController: No published page found, using fallback");
                $content = $this->view->render(
                    $config['home_page']['fallback_template'] ?? 'home/fallback',
                    [
                        'title' => 'CMS Home',
                        'content' => 'Welcome to our CMS'
                    ]
                );
            }

            return new Response($content);
            
        } catch (Exception $e) {
            error_log('HomeController Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            // Robust fallback response
            return new Response(
                $this->view->render('errors/500', [
                    'message' => 'Unable to load home page content',
                    'error' => $e->getMessage()
                ]),
                500
            );
        }
    }

    protected function processMarkdown(string $content): string {
        try {
            $config = require __DIR__ . '/../../config/content.php';
            $md = new MarkdownExtra();
            $md->html5 = true;
            $md->safeMode = $config['markdown']['safe_mode'] ?? true;
            $md->setUrlsLinked($config['markdown']['auto_links'] ?? true);
            
            $result = $md->text($content);
            if (empty($result)) {
                throw new \RuntimeException('Markdown processing returned empty result');
            }
            return $result;
            
        } catch (Exception $e) {
            error_log('Markdown processing error: ' . $e->getMessage());
            return nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'));
        }
    }

    public function test(): Response {
        return new Response(json_encode([
            'status' => 'success',
            'message' => 'Test route working'
        ]), 200, ['Content-Type' => 'application/json']);
    }
}
