<?php

class AnalyticsController {
    /**
     * Handle POST /analytics/track requests
     * @param array $input JSON-decoded request body
     * @return array Response data
     */
    public static function trackEvent(array $input): array {
        try {
            // Validate required fields
            if (empty($input['tenant_id'])) {
                throw new InvalidArgumentException('Missing tenant_id');
            }
            if (empty($input['event_type'])) {
                throw new InvalidArgumentException('Missing event_type');
            }

            // Validate tenant exists and is active
            if (!Tenant::exists($input['tenant_id'])) {
                throw new InvalidArgumentException('Invalid tenant_id');
            }
            
            // Validate event type is allowed
            $allowedEvents = ['page_view', 'button_click', 'form_submit'];
            if (!in_array($input['event_type'], $allowedEvents)) {
                throw new InvalidArgumentException('Invalid event_type');
            }

            // Route to appropriate table handler
            $tableName = match($input['event_type']) {
                'page_view' => 'analytics_page_views',
                'button_click' => 'analytics_clicks',
                'form_submit' => 'analytics_submissions',
                default => throw new InvalidArgumentException('Invalid event_type')
            };

            // Apply event-specific validation
            switch ($input['event_type']) {
                case 'page_view':
                    if (empty($input['url']) || empty($input['referrer'])) {
                        throw new InvalidArgumentException('Missing required fields for page_view');
                    }
                    break;
                case 'button_click':
                    if (empty($input['element_id']) || empty($input['page_url'])) {
                        throw new InvalidArgumentException('Missing required fields for button_click');
                    }
                    break;
                case 'form_submit':
                    if (empty($input['form_id']) || empty($input['fields'])) {
                        throw new InvalidArgumentException('Missing required fields for form_submit');
                    }
                    break;
            }

            return [
                'status' => 'success',
                'event_id' => uniqid('evt_', true)
            ];
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            return ['error' => $e->getMessage()];
        } catch (Exception $e) {
            http_response_code(500);
            error_log('Analytics error: ' . $e->getMessage());
            return ['error' => 'Internal server error'];
        }
    }
}
