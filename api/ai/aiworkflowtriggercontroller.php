<?php

class AIWorkflowTriggerController {
    private $triggerService;
    
    public function __construct() {
        $this->triggerService = new AIWorkflowTriggerService();
    }
    
    public function create($request) {
        $trigger = $request['trigger'];
        $validationErrors = AIWorkflowTriggerValidator::validate($trigger);
        
        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }
        
        $id = $this->triggerService->createTrigger($trigger);
        return ['success' => true, 'id' => $id];
    }
    
    public function update($id, $request) {
        $trigger = $request['trigger'];
        $validationErrors = AIWorkflowTriggerValidator::validate($trigger);
        
        if (!empty($validationErrors)) {
            return ['success' => false, 'errors' => $validationErrors];
        }
        
        $success = $this->triggerService->updateTrigger($id, $trigger);
        return ['success' => $success];
    }
    
    public function delete($id) {
        $success = $this->triggerService->deleteTrigger($id);
        return ['success' => $success];
    }
    
    public function list() {
        $triggers = $this->triggerService->listTriggers();
        return ['success' => true, 'triggers' => $triggers];
    }
    
    public function get($id) {
        $trigger = $this->triggerService->getTrigger($id);
        return ['success' => $trigger !== null, 'trigger' => $trigger];
    }
}
