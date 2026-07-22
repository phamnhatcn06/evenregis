<?php

/**
 * Application parameters
 */
return array(
    // Legacy params
    'adminEmail' => 'webmaster@example.com',
    'onCache' => 0,

    // Portal SSO Configuration
    'portal' => array(
        'url' => 'https://portal.muongthanh.vn',
        'api_url' => 'https://api.portal.muongthanh.vn',
        'sso_me_endpoint' => '/api/sso/me',
        'sso_permissions_endpoint' => '/api/sso/permissions/me',
        'jwt_secret' => getenv('JWT_SECRET') ?: 'B7BFCA89BF11459E898B26310C2794E6819FB0AD0565B4C3',
        'jwt_algorithm' => 'HS256',
        'portal_secret' => getenv('PORTAL_SECRET') ?: 'YOUR_PORTAL_SECRET_HERE',
    ),

    // External API
    // Test
    // 'externalApiUrl' => 'http://evenapi.local:8080',
    // 'externalApiKey' => 'z8H4VaRvLtBGYUuLgasJDIJIWXYgquFgUIy426pkaFKi7Q0PAC44oz2Jy4KLB5Mz',

    // Product
    'externalApiUrl' => 'https://portal-registration.muongthanh.vn',
    'externalApiKey' => 'XkufRQtY0evNxRyG0YvEXsq5Vg8hypjWDEaYqJlfTsc9qv8r8gYT9XqOlB8YLoQp',

    // Session Configuration
    'session' => array(
        'timeout' => 1800, // 30 minutes
        'refresh_interval' => 900, // 15 minutes
    ),

    // Email Configuration (SMTP)
    'mail' => array(
        'host' => 'mail.muongthanh.vn',
        'port' => 587,
        'username' => 'portal@muongthanh.vn',      // Thay bằng email thật
        'password' => 'E37mkAs=',          // Thay bằng App Password
        'encryption' => 'tls',                      // tls hoặc ssl
        'from_email' => 'portal@muongthanh.vn',
        'from_name' => 'Ban tổ chức Đại hội Mường Thanh 2026',
    ),

);
