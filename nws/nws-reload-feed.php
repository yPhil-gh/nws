<?php

/*
  reload-feed : Reload one feed

  This script is part of NWS
  https://github.com/xaccrocheur/nws/


  NWS is free software: you can redistribute it and/or modify it under
  the terms of the GNU General Public License as published by the Free
  Software Foundation, either version 3 of the License, or (at your
  option) any later version.

  This program is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
  General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Searches for the first occurence of an html <img> element in a string
 * and extracts the src if it finds it. Returns boolean false if
 * <img> element is not found.
 * @param    string  $str    An HTML string
 * @return   mixed           The contents of the src attribute in the
 *                           found <img> or boolean false if no <img>
 *                           is found
 */
function str_img_src($html) {
    if (stripos($html, '<img') !== false) {
        $imgsrc_regex = '#<\s*img [^\>]*src\s*=\s*(["\'])(.*?)\1#im';
        preg_match($imgsrc_regex, $html, $matches);
        unset($imgsrc_regex);
        unset($html);
        if (is_array($matches) && !empty($matches)) {
            return $matches[2];
        } else {
            return false;
        }
    } else {
        return false;
    }
}

$z = $_GET['z'];

function reparse($u) {
    $limit="18";
    $feedRss=simplexml_load_file($u);
    $i=0;
    $url = parse_url($u);
    $subs = explode( '.', $url['host']);
    $domain = $subs[count($subs) -2].'.'.$subs[count($subs) -1];
    /* $favicon = (getimagesize($url['scheme'].'://'.$domain.'/favicon.ico') ? $url['scheme'].'://'.$domain.'/favicon.ico' : 'favicon.png'); */

    $favicon = 'http://g.etfv.co/'.$u;

    $tumb = $feedRss->tumblelog->attributes()->name;

    if($feedRss) {
        if (isset($feedRss->channel->item)) {
            $items = $feedRss->channel->item;
            $feedTitle = $feedRss->channel->title;
        }
        else {
            if (isset($feedRss->item)) {
                $items = $feedRss->item;         // rss of some sort
            } elseif (isset($tumb)) {		 // tumblr
                $items = $feedRss->posts->post;
                $feedTitle = $tumb;
            } else {		         	 // Atom
                $items = $feedRss->entry;
                $feedTitle = $feedRss->title;
            }
        }

        echo '
             <div class="feed" title ="'.$u.'">
                 <div class="feedTitle">
                     <span class="favicon"><img src="'.$favicon.'" /></span> <a href="'.$u.'" title=""></span>'.$feedTitle.'</a>
                 </div>
                 <ul>';

        foreach($items as $item) {
            if ($i++ < $limit) {
                $link = htmlspecialchars($item->link);
                $title = strip_tags($item->title);
                $imgSrc = str_img_src($item->description);
                list($width, $height) = getimagesize($imgSrc);
                $atomImg = $item->enclosure['url'];
                $elseSrc = str_img_src(strip_tags($item->content, "<img>"));
                $elseSrx = htmlspecialchars_decode($item->description);

                //Use that namespace
                $namespaces = $item->getNameSpaces(true);

                //Relative
                $media = $item->children($namespaces['media']);

                // Must call attributes()
                $mediaImg = $media->thumbnail->attributes()->url;

                //This looks like the favicon
                $media2Img = $media->content->attributes()->url;

                // Images.
                if (!empty($atomImg)) {
                    $ext = pathinfo($atomImg, PATHINFO_EXTENSION);
                    if ($ext == "mp3") {
                        $img = '<a href="'.$atomImg.'"><img class="feed audio" alt="Audio content" src="snd.png" /></a>';
                    } else {
                        $img = '<a href="'.$atomImg.'"><img class="feed" alt="'.$ext.' - atomImg" src="'.$atomImg.'" /></a>';
                    }
                } elseif (!empty($mediaImg)) {
                    $img = '<a href="'.$mediaImg.'"><img class="feed" title="plup" alt="media" src="'.$mediaImg.'" /></a>';
                } elseif (strstr($imgSrc, ".tumblr.")) {
                    $img = '<a href="'.$imgSrc.'"><img class="full" title="'.$title.'" alt="Photo" src="'.$imgSrc.'" /></a>';
                    $title = 'post';
                } elseif (!empty($elseSrc)) {
                    $img = '<a href="'.$elseSrc.'"><img class="feed" title="plip" alt="else" src="'.$elseSrc.'" /></a>';
                    $description = $item->content;
                } else {
                    $img = '';
                }

                if (empty($link)) $link = htmlspecialchars($item->link['href']);
                $fullDescription = strip_tags($item->description, "<img>, <p>");

                $description = (isset($item->description) ? $item->description : $item->content);
                $description = htmlspecialchars(htmlspecialchars_decode(trim(htmlspecialchars(strip_tags($description)))));

                echo '
                          <li title="'.$description.'">
                              <div class="all">'.$img.'<a target="_blank" href="'.$link.'">'.$title.'</a>
                                  <hr />
                              </div>
                          </li>';
            }
        }
        echo '
                      </ul>
                      </div>';
    }
}

reparse($z);
?>