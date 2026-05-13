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
        'jwt_secret' => getenv('JWT_SECRET') ?: 'B7BFCA89BF11459E898B26310C2794E6819FB0AD0565B4C3',
        'jwt_algorithm' => 'HS256',
    ),

    // External API
    'externalApiUrl' => 'https://portal-registration.muongthanh.vn',
    'externalApiKey' => 'XkufRQtY0evNxRyG0YvEXsq5Vg8hypjWDEaYqJlfTsc9qv8r8gYT9XqOlB8YLoQp',

    // Session Configuration
    'session' => array(
        'timeout' => 1800, // 30 minutes
        'refresh_interval' => 900, // 15 minutes
    ),

);
