<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'protected/extensions/jwt/JWT.php';

$token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9zaWQiOiI1MTI1IiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwNS8wNS9pZGVudGl0eS9jbGFpbXMvZW1haWxhZGRyZXNzIjoiY3N3bUBtdW9uZ3RoYW5oLnZuIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwNS8wNS9pZGVudGl0eS9jbGFpbXMvbmFtZSI6IlBo4bqhbSBWxINuIE5o4bqldCIsInNvZnR3YXJlX2lkIjoiMTAzMiIsImV4cCI6MTc4MDM4Mjc2NCwiaXNzIjoibXVvbmd0aGFuaC1zc28iLCJhdWQiOiIxMDMyIn0.T9KcOhyHlXOQ6qIl8eUSovcX-kVvfaP1mu3OKlTq1mQ';
$secret = 'B7BFCA89BF11459E898B26310C2794E6819FB0AD0565B4C3';

echo "<h2>JWT Decode Test</h2>";
echo "<pre>";

echo "1. Token parts:\n";
$parts = explode('.', $token);
echo "Header: " . base64_decode(strtr($parts[0], '-_', '+/')) . "\n";
echo "Payload: " . base64_decode(strtr($parts[1], '-_', '+/')) . "\n\n";

echo "2. JWT::decode result:\n";
$result = JWT::decode($token, $secret, 'HS256');
var_dump($result);

echo "</pre>";
