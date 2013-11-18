<?php
/*
  nws-favicon : Get site's favicon using various strategies

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/


function image_exists($imgUrl) {
    if (@GetImageSize($imgUrl))
        return true;
    else
        return false;
}

function get_favicon ($url) {

    $fallback_favicon = "img/nws.png";

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

        $doc = new DOMDocument();
        $doc->strictErrorChecking = FALSE;
        $doc->loadHTML(file_get_contents($url));
        $xml = simplexml_import_dom($doc);
        $query = $xml->xpath('//link[@rel="shortcut icon"]');
        $arr = (empty($query) ? $xml->xpath('//link[@rel="icon"]') : $query);

        $favicon = $arr[0]['href'];

        if (isset($favicon)) {
            if (!image_exists($favicon)) {
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

        if (!image_exists($file)) {
            $favicon = $fallback_favicon;
            $method = "fallback";
        }

    }
    return $favicon;
    echo $method;
}

?>
