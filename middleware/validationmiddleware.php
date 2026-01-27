<?php

class ValidationMiddleware {
    private $next;
    private $rules = [
        'trackPageView' => [
            'url' => 'required|string|max:2048',
            'referrer' => 'nullable|string|max:2048',
            'tenant_id' => 'required|string|max:36'
        ],
        'trackClickEvent' => [
            'element_id' => 'required|string|max:255',
            'page_url' => 'required|string|max:2048',
            'tenant_id' => 'required|string|max:36'
        ],
        'trackCustomEvent' => [
            'event_name' => 'required|string|max:255',
            'event_data' => 'nullable|array',
            'tenant_id' => 'required|string|max:36'
        ]
    ];

    public function __construct(callable $next) {
        $this->next = $next;
    }

    public function __invoke(array $request) {
        $action = $request['action'] ?? '';
        
        if (!isset($this->rules[$action])) {
            throw new InvalidArgumentException("Invalid action: $action");
        }

        $errors = $this->validate($request['data'] ?? [], $this->rules[$action]);
        
        if (!empty($errors)) {
            return [
                'status' => 'error',
                'code' => 400,
                'errors' => $errors
            ];
        }

        return ($this->next)($request);
    }

    private function validate(array $data, array $rules): array {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $rules = explode('|', $rule);
            
            foreach ($rules as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    $errors[$field][] = "$field is required";
                }
                
                if (str_starts_with($r, 'max:') && isset($data[$field])) {
                    $max = (int) substr($r, 4);
                    if (strlen($data[$field]) > $max) {
                        $errors[$field][] = "$field must be less than $max characters";
                    }
                }
                
                // Add more validation rules as needed
            }
        }
        
        return $errors;
    }
}
