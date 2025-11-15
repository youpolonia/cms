<?php
/**
 * TemplateProcessor - Handles notification template processing
 * 
 * Responsibilities:
 * - Parse template variables
 * - Apply channel-specific formatting
 * - Merge with notification data
 */
class TemplateProcessor {
    /**
     * Process template with given data
     * 
     * @param string $template Raw template content
     * @param array $data Key-value pairs for template variables
     * @param string $channel Notification channel (email/sms/web)
     * @return string Processed template
     * @throws InvalidArgumentException On invalid input
     */
    public static function process(string $template, array $data, string $channel): string {
        // Validate inputs
        if (empty($template)) {
            throw new InvalidArgumentException('Template cannot be empty');
        }
        
        if (!in_array($channel, ['email', 'sms', 'web'])) {
            throw new InvalidArgumentException('Invalid channel specified');
        }

        // Merge variables into template
        $processed = self::mergeVariables($template, $data);

        // Apply channel-specific formatting
        switch ($channel) {
            case 'email':
                $processed = self::formatEmail($processed);
                break;
            case 'sms':
                $processed = self::formatSms($processed);
                break;
            case 'web':
                $processed = self::formatWeb($processed);
                break;
        }

        return $processed;
    }

    /**
     * Merge variables into template
     */
    private static function mergeVariables(string $template, array $data): string {
        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", htmlspecialchars($value, ENT_QUOTES), $template);
        }
        return $template;
    }

    /**
     * Format for email channel
     */
    private static function formatEmail(string $content): string {
        // Basic HTML email formatting
        return "
<!DOCTYPE html>
<html>
<body>
{$content}
</body>
</html>";
    }

    /**
     * Format for SMS channel
     */
    private static function formatSms(string $content): string {
        // Strip HTML tags and limit length
        $content = strip_tags($content);
        return substr($content, 0, 160);
    }

    /**
     * Format for web notifications
     */
    private static function formatWeb(string $content): string {
        // Basic HTML formatting for web display
        return nl2br(htmlspecialchars($content));
    }
}
