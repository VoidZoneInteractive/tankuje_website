<?php

require_once('import.php');
require_once('config.php');

$import = new Import();

# uslugistacji
# adrestacji

$file = 'http://www.stacjebenzynowe.pl/stacja.php?gs=TJG9pgAK3Omjs8Ppk03A';

$params = array(
'mod' => 'uslugistacji',
'gs' => 'TJG9pgAK3Omjs8Ppk03A',
);

$file = 'http://www.stacjebenzynowe.pl/ajaxfile.php';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $file);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);



curl_setopt($ch, CURLOPT_HTTPHEADER, explode("\n", 'Connection: keep-alive
Origin: http://www.stacjebenzynowe.pl
User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36
Content-Type: application/x-www-form-urlencoded
Accept: */*
Referer: http://www.stacjebenzynowe.pl/
Accept-Language: pl-PL,pl;q=0.8,en-US;q=0.6,en;q=0.4'));

curl_setopt($ch, CURLINFO_HEADER_OUT, true);

$data = curl_exec($ch);

curl_close ($ch);



$doc = new DOMDocument();
@$doc->loadHTML($data);
$data = $doc->saveHTML();

$doc = new DOMDocument();
@$doc->loadHTML($data);


$xpath = new DOMXpath($doc);

echo '<pre>';

$elements = $xpath->query("*/table/tr/td/div/img");

echo print_r($elements,1);

if (!is_null($elements)) {
    foreach ($elements as $element) {
        echo "<br/>[". $element->nodeName. "]";
        echo $element->getAttribute('alt');
        echo $element->getAttribute('src');


        $nodes = $element->childNodes;
        foreach ($nodes as $node) {
            echo $node->nodeValue. "\n";
        }
    }
}

echo "\n" . print_r(htmlspecialchars($doc->saveHTML()));