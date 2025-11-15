<?php

return [
    'allowed_ips' => getenv('ADMIN_ALLOWED_IPS')
        ? explode(',', getenv('ADMIN_ALLOWED_IPS'))
        : [],
];
