<?php
// JWT Configuration - MUST be kept secure
// Recommended permissions: 0640 (rw-r-----)
// Owner: webserver user, Group: restricted access

return [
    'secret' => bin2hex(random_bytes(32)), // 64-character hex string
    'algorithm' => 'HS256',
    'issuer' => 'your-domain.com',
    'expiration' => 3600 // 1 hour in seconds
];
