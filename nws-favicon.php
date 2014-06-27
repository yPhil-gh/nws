<?php
/*
  nws-favicon : Get site's favicon (and cache it) using various strategies

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/


function image_exists($imgUrl) {
    return ((@GetImageSize($imgUrl)) ? true : false);
}

function removeslashes($string) {
    $string=implode("",explode("\/",$string));
    return stripslashes(trim($string));
}

$fallback_favicon = "img/nws.png";

// 7889400 secs is a quarter

function get_favicon_cached($url, $max_age = 7889400) {
    $favicon_cache_dir = "cache/favicon/";
    $cache_ok = false;

    $u = parse_url($url);
    $cache_file = $favicon_cache_dir . $u['host'] . ".ico";

    function create_cache_file ($url, $cache_file) {
        $favicon = get_favicon($url);
        $content = file_get_contents($favicon);
        file_put_contents($cache_file, $content);
    }

    if (file_exists($favicon_cache_dir)) {
        $cache_ok = true;
    } else {
        $cache_ok = @mkdir($favicon_cache_dir);
    }

    if (!$cache_ok) {
        return get_favicon($url);
    }

    if (file_exists($cache_file)) {
        $age = time() - filemtime($cache_file);
        if ($age >= $max_age) {
            create_cache_file($url, $cache_file);
        }
        return $cache_file;
    } else {
        create_cache_file($url, $cache_file);
        return $cache_file;
    }
}

function get_favicon ($url) {

    global $fallback_favicon;

    $u = parse_url($url);
    $subs = explode( '.', $u['host']);
    $full_url = "http://".implode('.', $subs);
    $short_url = "http://".$subs[count($subs) -2].'.'.$subs[count($subs) -1];

    $full_url_favicon = $full_url."/favicon.ico";
    $base_domain_favicon = $short_url."/favicon.ico";

    $doc = new DOMDocument();
    $doc->strictErrorChecking = FALSE;
    @$doc->loadHTML(file_get_contents($full_url));
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
