<?
if (isset($_GET['code'])) { die(highlight_file(__FILE__, 1)); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>nws</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.png" />
    <link href="jquery-ui.css" rel="stylesheet" type="text/css"/>
    <style type="text/css" media="screen">@import "nws.css";</style>
    <script src="jquery.min.js"></script>
    <script src="jquery-ui.min.js"></script>
    <script src="jquery.qtip.js"></script>
  </head>

<script>
    $(document).ready(function() {
    	$.ajaxSetup ({
    	  cache: false
    	      });

	$('#foo').bind('click', function() {
	    alert($(this).text());
	  });
	$('#foo').trigger('click');

    	var ajax_load = '<img src="loading.gif" class="loading" alt="loading..." />';
    	var loadUrl = 'nws-reload-feed.php';
	$('.feedContainer').click(function(){
    		var myFeed = $(this).attr('title')
		  $(this).children('div.anotherDiv')
    		  .html(ajax_load)
    		  .load(loadUrl, "z="+myFeed);

    	  });

	$( "#tabs" ).tabs().find( ".ui-tabs-nav" ).sortable({ axis: "x" });
	// $( ".feed" ).tooltip();
	$('.feedContainer').trigger('click');
	$('.tip').qtip();
	$('.feed a[href][title]').qtip({
	    content: {
	      text: false // Use each elements title attribute
		},
	      style: 'cream' // Give it some style
	      });
      });
</script>
<body>
<div id="tabs">
<?php
  $conf = "feeds.xml";
$urls = simplexml_load_file($conf);

$z = 0;

/* limite d'items par feed */
if (isset($_GET['limit'])) $feedsByTab = $_GET['limit'];
$feedsByTab="8";

function parse($u) {
  $limit="8";
  $feedRss=simplexml_load_file($u);
  $chars = array(" ", ".", ":");
  $i=0;

  if($feedRss)
    {
      $items = $feedRss->channel->item;
      $idiv = str_replace($chars, "", $feedRss->channel->title);
      echo '
<div class="feedContainer" style="width:24%;display:inline-block;vertical-align:top;" title ="'.$u.'" id="'.$idiv.'">
<div class="anotherDiv"></div>
</div>
';
    }
}

foreach ($urls->url as $url) {
  $myUrls[] = $url;
    foreach($url->attributes() as $attr => $val) {
      if ($attr == 'tab') {
	$myTabs[] = array('tab'=> (string) $val, 'url'=> (string) $url);
      }
    }
}

foreach($myTabs as $aRow){
    $tabGroups[$aRow['tab']][] = $aRow['url'];
}

echo '
<ul>
';

foreach (array_keys($tabGroups) as $tabName) {
    echo '
<li><a title="'.$tabName.', Drag to re-order" href="#tab-'.$tabName.'"><span>'.$tabName.'</span></a></li>';
}

echo '
</ul>
';


foreach (array_keys($tabGroups) as $tabName) {
  echo '<div id="tab-'.$tabName.'">';
  foreach ($tabGroups[$tabName] as $tabUrl) {
    parse($tabUrl);
  }
    echo '</div>';
}

?>

    </div>
    <a href="./mgmt.php">mgmt</a>
</body>
</html>
