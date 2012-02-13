<?
if (isset($_GET['code'])) { die(highlight_file(__FILE__, 1)); }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>nws</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="favicon.png" />
    <link href="jquery-ui.css" rel="stylesheet" type="text/css"/>
    <style type="text/css" media="screen">@import "nws.css";</style>
    <script src="jquery.min.js"></script>
    <script src="jquery-ui.min.js"></script>
  </head>

<script>
    $(document).ready(function() {
    	$.ajaxSetup ({
    	  cache: false
    	      });

    	var ajax_load = '<img src="loading.gif" class="loading" alt="loading..." />';
    	var loadUrl = 'nws-reload-feed.php';

	$('.reload').click(function(){
	    var DivToReload = $(this).parent()
	      var myUrl = DivToReload.attr('title')
	      DivToReload.children('div.innerContainer')
	      .html(ajax_load)
	      .load(loadUrl, "z="+myUrl);
    	  });

	$( "#tabs" ).tabs().find( ".ui-tabs-nav" ).sortable({ axis: "x" });
	// $( ".feed" ).tooltip();
	$('.reload').trigger('click');

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
<div class="outerContainer" style="" title ="'.$u.'">
<span class="reload" title="Reload '.$feedRss->channel->title.'">&phi;</span>
<div class="innerContainer"></div>
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
