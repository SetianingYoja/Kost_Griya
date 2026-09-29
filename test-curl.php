<?php

$ch = curl_init('https://app.sandbox.midtrans.com');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_exec($ch);

echo 'errno='.curl_errno($ch).PHP_EOL;
echo 'error='.curl_error($ch).PHP_EOL;
echo 'http='.curl_getinfo($ch, CURLINFO_HTTP_CODE).PHP_EOL;

curl_close($ch);
