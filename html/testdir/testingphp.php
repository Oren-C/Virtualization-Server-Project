<?php
/*
$a3[] = array("id" => 100,'poo1');
$a3[] = array("id" => 101,'poo2');

$a4[] = array("id" => 100,'poo1');
$a4[] = array("id" => 101,'poo2');
$a4[] = array("id" => 99,'poo99');

echo var_dump($a3);

echo "<br>";

echo var_dump($a4);

echo "<br>";

function udiffCompare($a, $b){
    return $a["id"] - $b["id"];
}

$result = array_udiff($a4, $a3, 'udiffCompare');
echo var_dump($result);
$recpt_no = 'RN426762';

*/
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
         $url = "https://";
    else
         $url = "http://";
    // Append the host(domain name, ip) to the URL.
    $url.= $_SERVER['HTTP_HOST'];

    // Append the requested resource location to the URL
    $url.= $_SERVER['REQUEST_URI'];

    echo $url;

    $url_components = parse_url($url);
    parse_str($url_components['query'], $params);

    echo "<br>";
    echo ' hi '.$params['name'];

?>