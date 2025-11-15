<?php

class AIProviderValidator {
    public static function validate($config) {
        $errors = [];
        
        if (empty($config['provider_name'])) {
            $errors['provider_name'] = 'Provider name is required';
        } elseif (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $config['provider_name'])) {
            $errors['provider_name'] = 'Invalid provider name format';
        }
        
        if (empty($config['api_key'])) {
            $errors['api_key'] = 'API key is required';
        }
        
        if (isset($config['base_url']) && !filter_var($config['base_url'], FILTER_VALIDATE_URL)) {
            $errors['base_url'] = 'Invalid base URL format';
        }
        
        if (isset($config['is_active']) && !is_bool($config['is_active'])) {
            $errors['is_active'] = 'is_active must be boolean';
        }
        
        return $errors;
    }
}
