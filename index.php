<?php

/**
 * Root index.php - Redirects to public directory
 * 
 * This file is used when the web root cannot be changed to point to public/
 * and .htaccess redirects are not working.
 */

// Get the current request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Remove query string for redirect
$path = parse_url($requestUri, PHP_URL_PATH);

// Build the redirect URL
$redirectUrl = '/public' . $path;

// Preserve query string if present
if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
    $redirectUrl .= '?' . $_SERVER['QUERY_STRING'];
}

// Perform redirect
header('Location: ' . $redirectUrl, true, 301);
exit;
