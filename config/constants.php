<?php
/*
 Application Constants
 */

// Application settings
define('APP_NAME', 'Sohati+');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/kayan/public');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_PATIENT', 'patient');
//incomplete 
define('ROLE_DONOR', 'donor');

// Session timeout
define('SESSION_TIMEOUT', 3600); // 1 hour

// Upload directories
define('UPLOAD_AVATAR', '../public/uploads/avatars/');
define('UPLOAD_DOCUMENTS', '../public/uploads/documents/');

// API Response codes
define('API_SUCCESS', 200);
define('API_CREATED', 201);
define('API_BAD_REQUEST', 400);
define('API_UNAUTHORIZED', 401);
define('API_FORBIDDEN', 403);
define('API_NOT_FOUND', 404);
define('API_SERVER_ERROR', 500);
?>