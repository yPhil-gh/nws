<?php

function debug ($var) {
error_reporting(E_ALL);
ini_set('display_errors', 1);
  echo "<pre>";
  var_dump($var);
  echo "</pre>";
}

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
  $limit="12";
  $feedRss=simplexml_load_file($u);
  $i=0;
  $url = parse_url($u);
  $subs = explode( '.', $url['host']);
  $domain = $subs[count($subs) -2].'.'.$subs[count($subs) -1];
  $favicon = (getimagesize($url['scheme'].'://'.$domain.'/favicon.ico') ? $url['scheme'].'://'.$domain.'/favicon.ico' : 'favicon.png');

  if($feedRss) {
    $feedTitle = $feedRss->channel->title;
    if (isset($feedRss->channel->item)) {
      $items = $feedRss->channel->item;
    }
    else {
      if (isset($feedRss->item)) {
	$items = $feedRss->item; // rss
      } else {			 // atom
	$items = $feedRss->entry;
	$feedTitle = $feedRss->title;
      }
    }

    echo '
<div class="feed" title ="'.$u.'">
<div class="feedTitle">
<span class="favicon"><img src="'.$favicon.'" /></span> <a href="'.$u.'" title="">'.$feedTitle.'</a><span class="reload" title="Reload feed">&phi;</span>
</div>
<ul>';

    foreach($items as $item) {
      if ($i++ < $limit) {
	/* unset($imgSrc); */
	$link = htmlspecialchars($item->link);
	$imgSrc = str_img_src(strip_tags($item->description, "<img>"));
	list($width, $height) = getimagesize($imgSrc);
	$atomImg = $item->enclosure['url'];
	if (!empty($atomImg)) {
	  $img = '<img alt="At0m" src="'.$atomImg.'" />&nbsp';
	  $txtClass = "txt";
	} elseif (isset($imgSrc) && $width > 2) {
	  $img = '<img alt="no0Atom" src="'.$imgSrc.'" />&nbsp';
	  $txtClass = "txt";
	} else {
	  $img = '';
	  $txtClass = "txt-noImg";
	}

	if (empty($link)) $link = htmlspecialchars($item->link['href']);
	$fullDescription = strip_tags($item->description, "<img>, <p>");
	$description = strip_tags($item->description);

	echo '
<li><a title="'.$description.'" target="_blank" href="'.$link.'">'.$img.'<div class="'.$txtClass.'">'.strip_tags($item->title).'</a></div></li>';
      }
    }
    echo '
</ul>
</div>';
  }
}

reparse($z);
?>