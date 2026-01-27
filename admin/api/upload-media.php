<?php
http_response_code(410);
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'This endpoint is deprecated. Use /admin/api/media-upload.php instead.'
]);
exit;
