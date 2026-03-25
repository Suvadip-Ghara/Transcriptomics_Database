<?php
/**
 * CORS and Headers Configuration
 * Sets appropriate headers for API responses
 */

// Allow cross-origin requests (adjust for production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Set content type to JSON
header("Content-Type: application/json; charset=utf-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Cache control for API responses (no-cache for dynamic data)
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
