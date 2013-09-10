<?
/*
  nws-favicon : Get site's favicon using various strategies

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>nws</title>
</head>
<body>

<?php

function CheckImageExists($imgUrl) {
    if (@GetImageSize($imgUrl)) {
        return true;
    } else {
        return false;
    };
};

function getFavicon ($url) {

    $u = parse_url($url);

    $subs = explode( '.', $u['host']);
    $domain = $subs[count($subs) -2].'.'.$subs[count($subs) -1];

    $file = "http://".$domain."/favicon.ico";
    $file_headers = @get_headers($file);

    if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 NOT FOUND' || $file_headers[0] == 'HTTP/1.1 301 Moved Permanently') {
        $exists = false;

        $fileContent = @file_get_contents("http://".$domain);

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
                $method = "xpath";
            } else {
                $favicon = $domain.$hrefs[$icon_key];
                if (substr($favicon, 0, 4) != 'http') {
                    $favicon = 'http://' . $favicon;
                    $method = "xpath+http";
                }
            }
        }

        if (isset($favicon)) {
            if (!CheckImageExists($favicon)) {
                $favicon = 'http://opensimo.org/philippe/nws/img/nws.png';
                $method = "fallback";
            }
        } else {
            $favicon = 'http://opensimo.org/philippe/nws/img/nws.png';
            $method = "fallback";
        }

    } else {
        $exists = true;
        $favicon = $file;
        $method = "classic";

        if (!CheckImageExists($file)) {
            $favicon = 'http://opensimo.org/philippe/nws/img/nws.png';
            $method = "fallback";
        }

        $key = '';
        $founded = array();
        $founded[] = '';
    }
    return $favicon;
}

?>