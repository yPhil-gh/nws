<?php
/*
  nws-reload-feed : Reload one feed

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/

ini_set('display_errors', 'Off');

// If the feed's URL contains one of those, it will be treated as a Photoblog feed (full img width) 
// Unless the call forces photoblog mode to true or false
$photoblog_domains = array(
    ".tumblr.",
    "cabinporn",
    "bigpicture",
    "xkcd.com",
    "yahoo.com",
    "penguinpetes.com"
);

// Number of items / feed
$items_limit = "16";
$cache_dir = "cache/";

include('nws-favicon.php');

function str_img_src($html) {
    if (stripos($html, '<img') !== false) {
        $imgsrc_regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';
        preg_match($imgsrc_regex, $html, $matches);
        unset($imgsrc_regex);
        unset($html);
        if (is_array($matches) && !empty($matches))
            return $matches[2];
        else
            return false;
    } else
        return false;
}

function get_link($links) {
    if (count($links) > 1) {
        foreach($links as $link) {
            $myAttributes = $link->attributes();
            if (isset($myAttributes['href']))
                $res = $myAttributes['href'];
            if (strlen($link)>0) 
                $res = $link;
            if (isset($myAttributes['type']) && ($myAttributes['type'] == 'text/html')) // bingo
                return $res;
        }
        if (isset($res)) return $res;
        else return '';
    }
    else {
        $myAttributes = $links->attributes();
        if (isset($myAttributes['href']))
            return $myAttributes['href'];
        else
            return $links;
    }
}

// getfile() : 
//      $url        : string, should already be urlencoded
//      $max_age    : integer, number of seconds
function get_file($url, $max_age) {
    global $cache_dir;
    
    if (file_exists($cache_dir)) {
        $cache_ok = true;
    }
    else {
        $cache_ok = @mkdir($cache_dir);
    }

    // cache file should not contain '://' otherwise it will be considered as an url
    // simplifiying by looking for 'http://' or 'https://'
    $url_decoded = urldecode($url);
    if ($cache_ok && (substr($url_decoded, 0, strlen('http://')) == 'http://')) {
        $cache_file = substr($url_decoded, strlen('http://'));
    } 
    elseif ($cache_ok && (substr($url_decoded, 0, strlen('https://')) == 'https://')) {
        $cache_file = substr($url_decoded, strlen('https://'));
    }
    else {
        $cache_ok = false;
    }
    
    if (!$cache_ok) { // abort cache feature
        $rssfeed = file_get_contents($url) or die("feed cannot be read");
        return $rssfeed;
    }
    // reencod to avoid specials symbols (like '/')
    $cache_file = urlencode($cache_file);
    $cache_file = $cache_dir.$cache_file;
    
    if (file_exists($cache_file) && (filemtime($cache_file) > (time() - $max_age)) ) {
        $rssfeed = file_get_contents($cache_file) or die("feed cannot be read from cache");
    }
    else {
        $rssfeed = file_get_contents($url) or die("feed cannot be read");
        $fh = fopen($cache_file, 'w');
        if ($fh !== false) {
            fwrite($fh, $rssfeed);
            fclose($fh);
        }
    }
    return $rssfeed;
    
}

function reparse($u, $numItems, $imgMode, $photoblog, $max_age) {
    // $u is already urlencoded (comming from a GET request)
    $xmlrss = get_file($u, $max_age);
    $feedRss = simplexml_load_string($xmlrss) or die("feed not loading");

    // var_dump($http_response_header);

    $i = 0;
    $url = parse_url($u);
    $subs = explode( '.', $url['host']);
    $domain = $subs[0].'.'.$subs[count($subs) -2].'.'.$subs[count($subs) -1];

    $favicon = getFavicon('http://'.$domain);

    if($feedRss) {
        if (isset($feedRss->channel->item)) {
            $items = $feedRss->channel->item;
            $feedTitle = $feedRss->channel->title;
            if (isset($feedRss->channel->link)) {
                $feedLink = get_link($feedRss->channel->link);
            }
        }
        else {
            if (isset($feedRss->item)) {
                $items = $feedRss->item;         // rss of some sort
            } elseif (isset($tumb)) {	    	 // tumblr
                $items = $feedRss->posts->post;
                $feedTitle = $tumb;
            } else {		         	         // Atom
                $items = $feedRss->entry;
                $feedTitle = $feedRss->title;
            }
            if (isset($feedRss->link)) {
                $feedLink = get_link($feedRss->link);
            }
        }
//        echo '<!--
//feedLink = '.print_r($feedLink,true).'
//-->';
        if (empty($feedLink))  $feedLink = $u;

        $items_total = count($items);

        if ($items_total > $numItems)
            $display_items = $numItems;
        else
            $display_items = $items_total;

        echo '
             <div class="feed" title ="'.$feedLink.'">
                 <div class="feedTitle">
                     <span class="favicon">
                         <img src="'.$favicon.'" />
                     </span>
                     <a href="'.$feedLink.'" title="Displaying '.$display_items.' / '.$items_total.' items from '.$feedTitle.'">'.$feedTitle.'</a>
                 </div>
                 <ul>';

        foreach($items as $item) {
            if ($i++ < $numItems) {
                $link = htmlspecialchars($item->link);
                $title = strip_tags($item->title);
                $imgSrc = str_img_src($item->description);

                // Image

                $atomImg = $item->enclosure['url'];
                $elseSrc = str_img_src(strip_tags($item->content, "<img>"));
                $elseSrx = htmlspecialchars_decode($item->description);

                //Use that namespace
                $namespaces = $item->getNameSpaces(true);

                // foreach ($item->children($namespaces['media']) as $name => $value)
                //     echo '<br />namespace: ' . $name.' value: ' . $value;

                //Relative
                if (isset($namespaces['media']) && $item->children($namespaces['media']))
                    $media = $item->children($namespaces['media']);

                // if (isset($media)) {
                //     echo "media is set!";
                //     /\* $mediaImg = $media->thumbnail->attributes()->url; *\/
                //     $mediaImg = $media->attributes()->url;
                // }

                if (isset($media) && isset($media->thumbnail))
                    $mediaImg = $media->thumbnail->attributes()->url;

                if (!empty($elseSrc))
                    $imgSrc = "http://".$domain.$elseSrc;

                if ($imgMode == 'none' || ($imgMode == 'first' && $i > 1)) {
                    $img = '';
                }
                elseif ($photoblog || $title == "Photo") {
                    $img = '<a href="'.$imgSrc.'"><img class="full" alt="'.$title.'" src="'.$imgSrc.'" /></a>';
                    $title = $title;
                } elseif (!empty($atomImg)) {
                    $ext = pathinfo($atomImg, PATHINFO_EXTENSION);
                    if ($ext == "mp3")
                        $img = '<a href="'.$atomImg.'"><span class="audio-note" title="Audio content">♫</span></a>';
                    else
                        $img = '<a href="'.$atomImg.'"><img class="feed" alt="'.$ext.' - atomImg" src="'.$atomImg.'" /></a>';
                } elseif (!empty($mediaImg)) {
                    $img = '<a href="'.$mediaImg.'"><img class="feed" alt="media" src="'.$mediaImg.'" /></a>';
                } elseif (!empty($elseSrc)) {
                    list($width, $height) = getimagesize($imgSrc);
                    if (isset($width) && $width > 2 && $title != "Photo") {
                        $img = '<a href="'.$imgSrc.'"><img class="feed" alt="regexp" src="'.$imgSrc.'" /></a>';
                    } else {
                        $img = '<a href="'.$elseSrc.'"><img class="feed" alt="else" src="'.$elseSrc.'" /></a>';
                        $description = $item->content;
                    }
                } else {
                    $img = '';
                }

                if (empty($link))
                    $link = htmlspecialchars($item->link['href']);

                $description = (isset($item->description) ? $item->description : $item->content);
                $description = htmlspecialchars_decode(trim(htmlspecialchars(strip_tags($description))), ENT_NOQUOTES);

                echo '
                          <li title="'.$description.'">
                              <div>'.$img.'<a target="_blank" href="'.$link.'">'.$title.'</a>
                                  <br style="clear:both;"/>
                              </div>
                          </li>';
            }
            else break;
        }
        echo '
                      </ul>
                      </div>';
    }
}

if (isset($_GET['n']))
    $numItems=$_GET['n'];
else
    $numItems=$items_limit;

if (isset($_GET['i']))
    $imgMode=$_GET['i'];
else
    $imgMode='all';

if (isset($_GET['age']))
    $max_age = (int) $_GET['age'];
else
    $max_age = 3600;

$photoblog = false;
foreach ($photoblog_domains as $photoblog_domain)
    if (strstr($_GET['z'], $photoblog_domain)) $photoblog = true;

if (isset($_GET['p'])) {
    if ($_GET['p'] == "true") $photoblog = true;
    elseif ($_GET['p'] == "false") $photoblog = false;
}

reparse($_GET['z'],$numItems,$imgMode,$photoblog, $max_age);

?>
