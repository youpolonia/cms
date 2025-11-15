<?php

return [
    'auth_user' => getenv('COMMAND_AUTH_USER') ?: 'admin',
    'auth_password' => getenv('COMMAND_AUTH_PASSWORD') ?: 'secret',
];
