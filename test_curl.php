<?php
$ch = curl_init('http://127.0.0.1:8080/admin/registrations/downloadImportTemplate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
// We might need authentication or we can just see the 500 error page contents
$response = curl_exec($ch);
echo $response;
