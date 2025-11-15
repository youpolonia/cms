<?php
class WorkflowValidator {
    public function validateCreate(array $data): void {
        $this->validateCommonFields($data);
        
        if (empty($data['created_by'])) {
            throw new Exception("Created by user is required");
        }
    }
    
    public function validateUpdate(array $data): void {
        $this->validateCommonFields($data);
    }
    
    private function validateCommonFields(array $data): void {
        if (empty($data['name'])) {
            throw new Exception("Workflow name is required");
        }
        
        if (strlen($data['name']) > 100) {
            throw new Exception("Workflow name must be 100 characters or less");
        }
        
        if (empty($data['steps']) || !is_array($data['steps'])) {
            throw new Exception("Workflow steps must be an array");
        }
        
        foreach ($data['steps'] as $step) {
            if (empty($step['name']) || empty($step['approvers']) || !is_array($step['approvers'])) {
                throw new Exception("Each step must have a name and array of approvers");
            }
        }
    }
}
