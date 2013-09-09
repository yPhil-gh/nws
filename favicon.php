<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>nws</title>
</head>
<body>

<?php

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

if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 NOT FOUND' || $file_headers[0] == 'HTTP/1.1 301 Moved Permanently') {
    $exists = false;

    $fileContent = @file_get_contents($url);

    $dom = @DOMDocument::loadHTML($fileContent);
    $xpath = new DOMXpath($dom);

    $elements = $xpath->query("head/link//@href");

    foreach ($elements as $link) {
        $hrefs[] = $link->value;
    }

    $found_favicon = array();
    foreach ( $hrefs as $key => $value ) {
        if( substr_count($value, 'favicon.ico') > 0 ) {
            $found_favicon[] = $value;
            $icon_key = $key;
        }
    }

    $found_http = array();
    foreach ( $found_favicon as $key => $value ) {
        if( substr_count($value, 'http') > 0 ) {
            $found_http[] = $value;
            $favicon = $hrefs[$icon_key];
        } else {
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

echo "<br />favicon : ".$favicon;
/* echo "<br />key : ".$icon_key; */
/* echo "<br />domain : ".$domain; */

?>