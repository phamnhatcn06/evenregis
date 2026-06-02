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
        'url' => 'http://localhost:5993/',
        'api_url' => 'http://localhost:6689',
        'sso_me_endpoint' => '/api/sso/me',
        'sso_permissions_endpoint' => '/api/sso/permissions/me',
        'jwt_secret' => getenv('JWT_SECRET') ?: 'B7BFCA89BF11459E898B26310C2794E6819FB0AD0565B4C3',
        'jwt_algorithm' => 'HS256',
        'portal_secret' => getenv('PORTAL_SECRET') ?: 'YOUR_PORTAL_SECRET_HERE',
    ),

    // External API
    'externalApiUrl' => 'https://dev-portal-registration.muongthanh.vn',
    'externalApiKey' => 'z8H4VaRvLtBGYUuLgasJDIJIWXYgquFgUIy426pkaFKi7Q0PAC44oz2Jy4KLB5Mz',

    // Session Configuration
    'session' => array(
        'timeout' => 1800, // 30 minutes
        'refresh_interval' => 900, // 15 minutes
    ),

);
