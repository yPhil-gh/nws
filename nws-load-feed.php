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

function reparse($u, $numItems, $imgMode, $photoblog) {

    $feedRss = simplexml_load_file(urlencode($u)) or die("feed not loading");

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
        }

        $items_total = count($items);

        if ($items_total > $numItems)
            $display_items = $numItems;
        else
            $display_items = $items_total;

        echo '
             <div class="feed" title ="'.$u.'">
                 <div class="feedTitle">
                     <span class="favicon">
                         <img src="'.$favicon.'" />
                     </span>
                     <a href="'.$u.'" title="Displaying '.$display_items.' / '.$items_total.' items from '.$feedTitle.'">'.$feedTitle.'</a>
                 </div>
                 <ul>';

        foreach($items as $item) {
            if ($i++ < $numItems) {
                $link = htmlspecialchars($item->link);
                $title = strip_tags($item->title);
                $imgSrc = str_img_src($item->description);

                // Image

                if (isset($imgSrc) && $imgSrc != "")
                    list($width, $height) = getimagesize($imgSrc);

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
                } elseif (!empty($imgSrc) && isset($width) && $width > 2 && $title != "Photo") {
                    $img = '<a href="'.$imgSrc.'"><img class="feed" alt="regexp" src="'.$imgSrc.'" /></a>';
                } elseif (!empty($elseSrc)) {

                    if (!CheckImageExists($elseSrc))
                        $elseSrc = "http://".$domain.$elseSrc;

                    $img = '<a href="'.$elseSrc.'"><img class="feed" alt="else" src="'.$elseSrc.'" /></a>';
                    $description = $item->content;
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

reparse($_GET['z'],$numItems,$imgMode,$photoblog);

?>
