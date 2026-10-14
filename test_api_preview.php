<?php
$ch = curl_init('http://127.0.0.1:8000/api/sport-draw/preview/6');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: z8H4VaRvLtBGYUuLgasJDIJIWXYgquFgUIy426pkaFKi7Q0PAC44oz2Jy4KLB5Mz',
    'Accept: application/json'
]);
$res = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
echo "ERR: $err\n";
echo "RES: " . substr($res, 0, 500) . "\n";
