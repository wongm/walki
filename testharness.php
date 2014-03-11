<?php
require_once('util.php');

$date = gmdate('Y-m-d\TH:i:s\Z');
$healthcheckurl = "/v2/healthcheck?timestamp=" . $date;
$nearmeurl = "/v2/nearme/latitude/-37.82392124423254/longitude/144.9462017431463";
$stopsurl = "/v2/mode/2/line/787/stops-for-line";

?>
<pre>
<? 

$finalurl = generateURLWithDevIDAndKey($healthcheckurl);

# Connect to the Web API using cURL.
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $finalurl); 
curl_setopt($ch, CURLOPT_TIMEOUT, '3'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

echo $xmlstr = curl_exec($ch); 
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

?>
</pre>