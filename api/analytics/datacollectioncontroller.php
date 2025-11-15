<?php

require_once __DIR__ . '/../../services/analyticsservice.php';
require_once __DIR__ . '/../../Repositories/PageViewRepository.php';
require_once __DIR__ . '/../../Repositories/ClickEventRepository.php';
require_once __DIR__ . '/../../Repositories/CustomEventRepository.php';
require_once __DIR__ . '/../../middleware/SecurityHeadersMiddleware.php';
require_once __DIR__ . '/../../middleware/validationmiddleware.php';

class DataCollectionController {
    private $middlewareChain;

    public function __construct() {
        $this->initializeMiddleware();
    }

    private function initializeMiddleware(): void {
        $validationMiddleware = new ValidationMiddleware(
            fn($request) => $this->handleRequest($request)
        );
        
        $this->middlewareChain = new SecurityHeadersMiddleware(
            fn($request) => $validationMiddleware($request)
        );
    }

    public function trackPageView() {
        $request = [
            'action' => 'trackPageView',
            'data' => $this->getRequestData()
        ];
        
        return $this->middlewareChain($request);
    }

    public function trackClickEvent() {
        $request = [
            'action' => 'trackClickEvent',
            'data' => $this->getRequestData()
        ];
        
        return $this->middlewareChain($request);
    }

    public function trackCustomEvent() {
        $request = [
            'action' => 'trackCustomEvent',
            'data' => $this->getRequestData()
        ];
        
        return $this->middlewareChain($request);
    }

    private function handleRequest(array $request) {
        $tenantId = $this->getTenantId();
        $data = $request['data'];
        
        switch ($request['action']) {
            case 'trackPageView':
                $result = PageViewRepository::create($tenantId, $data);
                break;
            case 'trackClickEvent':
                $result = ClickEventRepository::create($tenantId, $data);
                break;
            case 'trackCustomEvent':
                $result = CustomEventRepository::create($tenantId, $data);
                break;
            default:
                throw new InvalidArgumentException('Invalid action');
        }

        $this->sendResponse([
            'status' => 'success',
            'data' => $result
        ]);
    }

    private function getTenantId(): string {
        return $_SERVER['HTTP_X_TENANT_ID'] ?? '';
    }

    private function getRequestData(): array {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON data');
        }
        return $data ?? [];
    }

    private function sendResponse(array $response): void {
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
