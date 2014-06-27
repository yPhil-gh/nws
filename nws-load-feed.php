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
    "penguinpetes.com",
    "awkwardstockphotos.com"
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
    $res_score = 0;
    $res_link = '';
    if (count($links) > 1) {
        foreach($links as $link) {
            $myAttributes = $link->attributes();
            $current_score = 0;
            $current_link = '';

            if (isset($myAttributes['href'])) {
                $current_link = $myAttributes['href'];
                $current_score = 1;
            }
            elseif (strlen($link)>0) {
                $current_link = $link;
                $current_score = 1;
            }
            if ($current_link != '') {
                if (isset($myAttributes['type']) && ($myAttributes['type'] == 'text/html'))
                    $current_score = $current_score + 1;
                if (isset($myAttributes['rel']) && ($myAttributes['rel'] == 'alternate'))
                    $current_score = $current_score + 1;

                if ($current_score > $res_score) {
                    $res_link = $current_link;
                    $res_score = $current_score;
                }
            }
        }
        return $res_link;
    }
    else {
        $myAttributes = $links->attributes();
        if (isset($myAttributes['href']))
            return $myAttributes['href'];
        else
            return $links;
    }
}

function reparse($u, $numItems, $imgMode, $photoblog) {

    $rssfeed = file_get_contents($u) or die("File Load / read error");

    $feedRss = simplexml_load_string($rssfeed) or die("Feed Load / read error");

    $i = 0;
    $url = parse_url($u);
    $subs = explode( '.', $url['host']);
    $domain = $subs[0].'.'.$subs[count($subs) -2].'.'.$subs[count($subs) -1];

    if($feedRss) {
        if (isset($feedRss->channel->item)) {
            $items = $feedRss->channel->item;
            $feedTitle = $feedRss->channel->title;
            if (isset($feedRss->channel->link)) {
                $feedLink = get_link($feedRss->channel->link);
            }
        } else {
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

        if (empty($feedLink)) $feedLink = $u;
        $favicon = get_favicon_cached($feedLink);

        $items_total = count($items);

        if ($items_total > $numItems)
            $display_items = $numItems;
        else
            $display_items = $items_total;

        $title_link = 'Displaying '.$display_items.' / '.$items_total.' items from '.$feedTitle;
        $title_class= 'feedTitle';

        echo '
             <div class="feed" title ="'.$feedLink.'">
                 <div class="'.$title_class.'">
                     <span class="favicon">
                         <a href="'.$u.'"><img src="'.$favicon.'" /></a>&nbsp;<a href="'.$feedLink.'" title="'.$title_link.'">'.$feedTitle.'</a>
                     </span>

                 </div>
                 <ul>';


        foreach($items as $item) {
            if ($i++ < $numItems) {
                $link = htmlspecialchars(get_link($item->link));
                $title = strip_tags($item->title);
                $imgSrc = str_img_src($item->description);

                // Images
                $atomImg = (isset($item->enclosure['url']) ? $item->enclosure['url'] : $item->enclosure['src']);

                $elseSrc = str_img_src(strip_tags($item->content, "<img>"));

                //Use that namespace
                $namespaces = $item->getNameSpaces(true);

                //Relative
                if (isset($namespaces['media']) && $item->children($namespaces['media']))
                    $media = $item->children($namespaces['media']);

                if (isset($media)) {
                    if (isset($media->thumbnail))
                        $mediaImg = $media->thumbnail->attributes()->url;
                    elseif (    isset($media->content)
                    &&  isset($media->content->attributes()->medium)
                    && ($media->content->attributes()->medium == 'image'))
                        $mediaImg = $media->content->attributes()->url;
                }

                if (!empty($elseSrc)) {
                    if (substr($elseSrc, 0, strlen('//')) == '//') {
                        $elseSrc = 'http:'.$elseSrc;
                    }
                    elseif ((substr($elseSrc, 0, strlen('http://')) != 'http://')
                    &&  (substr($elseSrc, 0, strlen('https://')) != 'https://')
                    ) {
                        $elseSrc = 'http://'.$domain.$elseSrc;
                    }
                }

                if (empty($imgSrc) && !empty($elseSrc))
                    $imgSrc = $elseSrc;

                if ($imgMode == 'none' || ($imgMode == 'first' && $i > 1)) {
                    $img = '';
                }

                $img_class = ($photoblog ? "full" : "feed");

                $description = (isset($item->description) ? $item->description : $item->content);
                $description = htmlspecialchars_decode(trim(htmlspecialchars(strip_tags($description))), ENT_NOQUOTES);

                if (!empty($atomImg)) {
                    $ext = pathinfo($atomImg, PATHINFO_EXTENSION);
                    if ($ext == "mp3")
                        $img = '<a href="'.$atomImg.'"><span class="audio-note" title="Audio content">♫</span></a>';
                    else
                        $img = '<a href="'.$atomImg.'"><img class="'.$img_class.'" data-link="'.$link.'" alt="'.$title.'" src="'.$atomImg.'" /></a>';
                } elseif (!empty($mediaImg)) {
                    $img = '<a href="'.$mediaImg.'"><img class="'.$img_class.'" data-link="'.$link.'" alt="'.$title.'" src="'.$mediaImg.'" /></a>';
                } elseif (!empty($imgSrc)) {
                    list($width, $height) = getimagesize($imgSrc);
                    if (isset($width) && $width > 2) {
                        $img = '<a href="'.$imgSrc.'"><img class="'.$img_class.'" data-link="'.$link.'" alt="'.$title.'" src="'.$imgSrc.'" /></a>';
                    }
                    else $img = '';
                } else {
                    $img = '';
                }

                if (empty($link)) // should not be usefull anymore
                    $link = htmlspecialchars($item->link['href']);


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

$photoblog = false;
foreach ($photoblog_domains as $photoblog_domain)
    if (strstr($_GET['z'], $photoblog_domain)) $photoblog = true;

if (isset($_GET['p'])) {
    if ($_GET['p'] == "true") $photoblog = true;
    elseif ($_GET['p'] == "false") $photoblog = false;
}

reparse($_GET['z'], $numItems, $imgMode, $photoblog);

?>
