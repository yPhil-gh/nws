<?php
/*
  nws-favicon : Get site's favicon using various strategies

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/


function CheckImageExists($imgUrl) {
    if (@GetImageSize($imgUrl))
        return true;
    else
        return false;
}

function getFavicon ($url) {

$fallback_favicon = "img/nws.png";

    $dom = new DOMDocument();
    @$dom->loadHTML($url);
    $links = $dom->getElementsByTagName('link');
    $l = $links->length;
    $favicon = "/favicon.ico";
    for( $i=0; $i<$l; $i++) {
        $item = $links->item($i);
        if( strcasecmp($item->getAttribute("rel"),"shortcut icon") === 0) {
            $favicon = $item->getAttribute("href");
            break;
        }
    }

    $u = parse_url($url);

    $subs = explode( '.', $u['host']);
    $domain = $subs[count($subs) -2].'.'.$subs[count($subs) -1];

    $file = "http://".$domain."/favicon.ico";
    $file_headers = @get_headers($file);

    if($file_headers[0] == 'HTTP/1.1 404 Not Found' || $file_headers[0] == 'HTTP/1.1 404 NOT FOUND' || $file_headers[0] == 'HTTP/1.1 301 Moved Permanently') {

        $fileContent = @file_get_contents("http://".$domain);
        if ($fileContent === false) 
            return $fallback_favicon; // unable to read file (domain name without explicit page)

        $dom = @DOMDocument::loadHTML($fileContent);
        if ($dom === false)
            return $fallback_favicon; // file was empty ?

        $xpath = new DOMXpath($dom);

        $elements = $xpath->query("head/link//@href");

        $hrefs = array();

        foreach ($elements as $link) $hrefs[] = $link->value;

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
                $favicon = $fallback_favicon;
                $method = "fallback";
            }
        } else {
            $favicon = $fallback_favicon;
            $method = "fallback";
        }

    } else {
        $favicon = $file;
        $method = "classic";

        if (!CheckImageExists($file)) {
            $favicon = $fallback_favicon;
            $method = "fallback";
        }

    }
    return $favicon;
}

?>
