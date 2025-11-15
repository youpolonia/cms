<?php

class AIWorkflowTriggerValidator {
    public static function validate(array $trigger): array {
        $errors = [];
        
        // TODO: Implement validation rules
        if (empty($trigger['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($trigger['type'])) {
            $errors['type'] = 'Type is required';
        }
        
        return $errors;
    }
}
