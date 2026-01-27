<?php
require_once __DIR__ . '/../../services/seoservice.php';

class SeoApi {
    private $seoService;
    private $feedbackManager;

    public function __construct() {
        $this->seoService = new SeoService();
        $this->feedbackManager = new FeedbackManager();
    }

    public function generateMetaTags($keywords, $userId = 0) {
        if (!$this->feedbackManager->validateUser($userId)) {
            return ['error' => 'Invalid user permissions'];
        }

        $result = $this->seoService->generateMetaTags($keywords);
        
        // Track AI-generated meta tags
        $this->feedbackManager->trackChange(
            'ai_generation',
            [],
            $result,
            $userId,
            'AI-generated meta tags'
        );
        
        if (isset($result['error'])) {
            http_response_code(500);
            return $result;
        }
        
        return $result;
    }

    public function analyzeContent($content, $userId = 0) {
        if (!$this->feedbackManager->validateUser($userId)) {
            return ['error' => 'Invalid user permissions'];
        }

        $result = $this->seoService->analyzeContent($content);
        
        // Track content analysis
        $this->feedbackManager->trackChange(
            'content_analysis',
            [],
            $result,
            $userId,
            'Content analyzed for SEO'
        );
        
        if (isset($result['error'])) {
            http_response_code(500);
            return $result;
        }
        
        return $result;
    }

    public function getSeoScore($content, $userId = 0) {
        if (!$this->feedbackManager->validateUser($userId)) {
            return ['error' => 'Invalid user permissions'];
        }

        $score = $this->seoService->getSeoScore($content);
        
        // Track score check
        $this->feedbackManager->trackChange(
            'score_check',
            [],
            ['score' => $score],
            $userId,
            'SEO score calculated'
        );

        return $score;
    }
}

// API Endpoint Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // CSRF Protection
    if (empty($_SERVER['HTTP_X_CSRF_TOKEN']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
    
    $seoApi = new SeoApi();
    $response = [];

    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($_POST['action'])) {
            throw new Exception('Action parameter is required');
        }

        $action = $_POST['action'];
        switch ($action) {
            case 'generate-meta':
                if (empty($input['keywords'])) {
                    throw new Exception('Keywords parameter is required');
                }
                if (!is_string($input['keywords']) || strlen($input['keywords']) > 255) {
                    throw new Exception('Keywords must be a string under 255 characters');
                }
                $response = $seoApi->generateMetaTags($input['keywords']);
                break;
                
            case 'analyze':
                if (empty($input['content'])) {
                    throw new Exception('Content parameter is required');
                }
                if (!is_string($input['content']) || strlen($input['content']) > 10000) {
                    throw new Exception('Content must be a string under 10,000 characters');
                }
                $response = $seoApi->analyzeContent($input['content']);
                break;
                
            case 'generate-from-content':
                if (empty($input['content'])) {
                    throw new Exception('Content parameter is required');
                }
                if (!is_string($input['content']) || strlen($input['content']) > 10000) {
                    throw new Exception('Content must be a string under 10,000 characters');
                }
                $response = $seoApi->generateFromContent($input['content']);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        http_response_code(400);
        $response = ['error' => $e->getMessage()];
    }

    echo json_encode($response);
    exit;
}
