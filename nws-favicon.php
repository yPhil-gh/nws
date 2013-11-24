<?php
/*
  nws-favicon : Get site's favicon using various strategies

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/


function image_exists($imgUrl) {
    return ((@GetImageSize($imgUrl)) ? true : false);
}


$fallback_favicon = "img/nws.png";

function get_favicon_cached($url, $force_update = false, $max_age = 604800) {
    global $fallback_favicon;
    $favicon_cache_dir = "cache/favicon/";
    $cache_ok = false;
    
    // check if cache directory exists
    if (file_exists($favicon_cache_dir)) {
        $cache_ok = true;
    } else {
        $cache_ok = @mkdir($favicon_cache_dir);
    }
    
    if (!$cache_ok) { // directory missing and unable to create it => abort cache feature
        return get_favicon($url);
    }

    $u = parse_url($url);
    $cache_file = $u['host']; // http://www.example.com/test_dir/test_page.html => www.example.com
    // no need to reencode the cache file, there's no forbidden char in the domain name
    $cache_file = $favicon_cache_dir.$cache_file;
    
    $favicon = false;
    if (!$force_update && file_exists($cache_file)) {
        $age = time() - filemtime($cache_file);
        $favicon = file_get_contents($cache_file);

        if ($age >= $max_age) { // too old : check if icon is still reachable
            if (($favicon != $fallback_favicon) && (image_exists($favicon))) {
                @touch($cache_file);
            } else {
                $favicon = false;
            }
        }
    }

    if ($favicon === false) { // either unreadable cache file, or cache file is too old
        $favicon = get_favicon($url);
        $fh = fopen($cache_file, 'w');
        if ($fh !== false) {
            fwrite($fh, $favicon);
            fclose($fh);
        }
    }
    return $favicon;
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
