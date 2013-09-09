<!DOCTYPE html>
<html>
<head>
<title>nws</title>
</head>
<body>

<?php
// Report all PHP errors (see changelog)
error_reporting(E_ALL);


/* $url = 'https://ariejan.net/'; // Weird two-part favicon */
/* $url = 'http://rezo.net/'; // half-std favicon */
/* $url = 'http://www.bbc.co.uk/'; // std favicon */
/* $url = 'http://herveryssen.hautetfort.com/'; // weird redirect case */
/* $url = 'http://www.lyrics.com/'; // fucking redirection */
/* $url = 'http://www.acrimed.org/'; // std */
/* $url = 'http://lelibrepenseur.org.trafficvisualize.com//'; // non-std favicon found OK */
$url = 'http://jaimelinfo.fr/'; // non-std favicon

$u = parse_url($url);
$subs = explode( '.', $u['host']);
$domain = $subs[count($subs) -2].'.'.$subs[count($subs) -1];

function CheckImageExists($imgUrl) {
    if (@GetImageSize($imgUrl)) {
        return true;
    } else {
        return false;
    };
};

$file = $url.'/favicon.ico';
$file_headers = @get_headers($file);

/* var_dump($file_headers); */

echo "<br />Checking ".$url."</br /";

if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 NOT FOUND') {
    $exists = false;
    echo "<br />std ico not found<br />";

    $oDOMDocument = new DOMDocument();
    $oDOMDocument->formatOutput = true;
    @$oDOMDocument->loadHTML(file_get_contents($url));
    $oDOMDocument = DOMDocument::loadXML($oDOMDocument->saveXML());
    $oDOMDocument = utf8_decode($oDOMDocument);
    $oXPath = new DOMXPath($oDOMDocument);
    $links = $oXPath->query('head/link//@href');

//link[@rel="shortcut icon"]

    var_dump($links);

    $hrefs = array();

    $links = array();
    foreach($oDOMDocument->getElementsByTagName('link') as $link) {
        $links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
    }

    var_dump($links);

    foreach ($links as $link) {
        $hrefs[] = $link->value;
    }
    /* $key = array_search('/images/favicon.ico', $hrefs, FALSE); // $key = 2; */

    if (count($hrefs) <= 0) {
        echo "hrefs empty<br />";
        $links = $oXPath->query('//link[@rel="shortcut icon"]');
        $hrefs = array();
        foreach ($links as $link) {
            $hrefs[] = $link->value;
        }
        /* $key = array_search('/images/favicon.ico', $hrefs, FALSE); // $key = 2; */

    } else {
        echo "hrefs full<br />";
    }

    $found_favicon = array();
    foreach ( $hrefs as $key => $value ) {
        if( substr_count($value, 'favicon.ico') > 0 ) {
            $found_favicon[] = $value;
            $icon_key = $key;
            echo "favicon found<br />";
        } else {
            echo "favicon not found in array<br />";
        }
    }

    $found_http = array();
    foreach ( $found_favicon as $key => $value ) {
        if( substr_count($value, 'http') > 0 ) {
            echo "http found<br />";
            $found_http[] = $value;
            $favicon = $hrefs[$icon_key];
        } else {
            echo "http not found<br />";
            $favicon = $domain.$hrefs[$icon_key];

            if (substr($favicon, 0, 4) != 'http') {
                $favicon = 'http://' . $favicon;
            }

        }
    }


    if (!CheckImageExists($favicon)) {
        echo 'File DOES NOT EXIST<br />';
        $favicon = 'http://opensimo.org/philippe/nws/img/nws.png';
    } else {
        echo 'File DOES EXIST<br />';
    };


    /* print_r($founded); */

} else {
    $exists = true;
    echo "std ico found<br />";
    /* $hrefs[] = $file; */
    $favicon = $file;

    if (!CheckImageExists($file)) {
        echo 'DOES NOT EXIST';
        $favicon = 'http://opensimo.org/philippe/nws/img/nws.png';
    } else {
        echo 'DOES EXIST';
    };

    $key = '';
    $founded = array();
    $founded[] = '';
    }

print_r($hrefs);
echo "<br />favicon : ".$favicon;
echo "<br />key : ".$icon_key;
echo "<br />domain : ".$domain;

/*
  Array
  (
  [0] => http://news.google.com/news?ie=UTF-8&oe=utf8&q=sam+hastings&hl=en&gl=us&nolr=1&output=rss
  [1] => http://news.google.com/news?ie=UTF-8&oe=utf8&q=sam+hastings&hl=en&gl=us&nolr=1&output=atom
  )
*/
?>