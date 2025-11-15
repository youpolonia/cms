<?php

class AIProviderConfigController {
    private $providerService;
    
    public function __construct() {
        $this->providerService = new AIProviderService();
    }
    
    public function create($request) {
        $config = $request['config'];
        $validationErrors = AIProviderValidator::validate($config);
        
        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }
        
        $id = $this->providerService->createProviderConfig($config);
        return ['success' => true, 'id' => $id];
    }
    
    public function update($id, $request) {
        $config = $request['config'];
        $validationErrors = AIProviderValidator::validate($config);
        
        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }
        
        $success = $this->providerService->updateProviderConfig($id, $config);
        return ['success' => $success];
    }
    
    public function delete($id) {
        $success = $this->providerService->deleteProviderConfig($id);
        return ['success' => $success];
    }
    
    public function list() {
        $providers = $this->providerService->listProviderConfigs();
        return ['success' => true, 'providers' => $providers];
    }
    
    public function get($id) {
        $provider = $this->providerService->getProviderConfig($id);
        return ['success' => $provider !== null, 'provider' => $provider];
    }
}
