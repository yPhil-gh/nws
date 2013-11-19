<?php
/*
  nws-favicon : Get site's favicon using various strategies

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/


function image_exists($imgUrl) {
    return ((@GetImageSize($imgUrl)) ? true : false);
}

function get_favicon ($url) {

    $fallback_favicon = "img/nws.png";

    $u = parse_url($url);
    $subs = explode( '.', $u['host']);
    $full_url = "http://".implode('.', $subs);
    $short_url = "http://".$subs[count($subs) -2].'.'.$subs[count($subs) -1];

    $full_url_favicon = $full_url."/favicon.ico";
    $base_domain_favicon = $short_url."/favicon.ico";

    $doc = new DOMDocument();
    $doc->strictErrorChecking = FALSE;
    $doc->loadHTML(file_get_contents($full_url));
    $xml = simplexml_import_dom($doc);

    if (!image_exists($full_url_favicon)) {

        if (image_exists($base_domain_favicon)) {

            $arr = $xml->xpath('//link[@rel="icon" or @rel="shortcut icon"]');
            $favicon = $arr[0]['href'];

            if (image_exists($favicon)) {
                $favicon = $favicon;
            } else {
                $favicon = ((image_exists($full_url.$favicon)) ? $full_url.$favicon : $base_domain_favicon);
            }
        } else {

            $arr = $xml->xpath('//link[@rel="icon" or @rel="shortcut icon"]');
            $favicon = $arr[0]['href'];

            if (image_exists($favicon)) {
                $favicon = $favicon;
            } else {
                $favicon = ((image_exists($full_url.$favicon)) ? $full_url.$favicon : $fallback_favicon);
            }
        }

    } else {
        $favicon = $full_url_favicon;
    }

    /* echo $url."<hr />"; */
    /* echo $full_url."<hr />"; */
    /* echo $short_url."<hr />"; */
    /* echo $favicon."<hr />"; */

    return $favicon;
}

?>
