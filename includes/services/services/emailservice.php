<?php
require_once __DIR__ . '/../../core/config.php';

if (!class_exists('Swift_SmtpTransport')) {
    throw new RuntimeException('SwiftMailer is required for email functionality');
}

class EmailService {
    private static $config;
    
    public static function init() {
        self::$config = require_once __DIR__ . '/../../core/config.php';
    }
    
    public static function send($to, $subject, $message) {
        if (!isset(self::$config['smtp'])) {
            throw new RuntimeException('SMTP configuration missing');
        }
        
        $config = self::$config['smtp'];
        
        $headers = [
            'From: ' . $config['from_name'] . ' <' . $config['from_address'] . '>',
            'Reply-To: ' . $config['from_address'],
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8'
        ];
        
        $transport = (new Swift_SmtpTransport($config['host'], $config['port'], $config['encryption']))
            ->setUsername($config['username'])
            ->setPassword($config['password']);
            
        $mailer = new Swift_Mailer($transport);
        
        $message = (new Swift_Message($subject))
            ->setFrom([$config['from_address'] => $config['from_name']])
            ->setTo([$to])
            ->setBody($message, 'text/html');
            
        try {
            return $mailer->send($message);
        } catch (Exception $e) {
            error_log('Email send failed: ' . $e->getMessage());
            return false;
        }
    }
}
