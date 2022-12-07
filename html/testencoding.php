<?php

$encoded = "MQBjAG15c3Fs";

//var_dump(base64_decode($encoded));
$decoded = base64_decode($encoded);
echo "Decoded ".$decoded." ".strlen($decoded);
echo "<br>";
//echo $decoded.length
var_dump(urldecode($encoded));

var_dump(base64_encode($decoded));

echo "<br>";
$regformat = "1\0c\0mysql";
var_dump(base64_encode($regformat));

echo "<br>";

var_dump(urlencode($regformat));

echo "<br>";

var_dump(base64url_encode($regformat));


function base64url_encode($plainText)
{
    $base64 = base64_encode($plainText);
    $base64url = strtr($base64, '+/', '-_');
    return ($base64url);
}
?>



