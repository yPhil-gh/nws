<?php
/*
  clean-cache : remove outdated files from cache

  normally cache files are updated everytime the feed is accessed
  however if a feed get removed from configuration (feed.xml), cache files
  are not anymore updated but not removed.

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/

$cache_dir = "cache/";
$favicon_cache_dir = "cache/favicon/";
$max_age = 1;
?>
<!DOCTYPE html>
<html>
<head>
<title>nws</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="img/nws.png" />
    <style type="text/css" media="screen">@import "nws-style.css";</style>
</head>
<body>
<?php
    if (isset($_GET['max_age']))
        $max_age=$_GET['max_age'];

    echo "<h1>Cleaning cache directory</h1>\n";
    $nb_erase = 0;
	if($handle=opendir($cache_dir)) {
		while (false !== ($file = readdir($handle))) {
		    if ($file != '.' && $file != '..' && !is_dir($file)) {
		        $age = time() - filemtime($cache_dir.$file);
		        if ($age > $max_age) {
		            if ($age < 60) {
		                $age_str = $age.' seconds';
		            } elseif ($age < 3600) {
		                $age_sec = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_min = (int) $age % 60;
		                $age_str = $age_min.':'.$age_sec;
		            } elseif ($age < 86400) {
		                $age_sec = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_min = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_hour = (int) $age % 60;
		                $age_str = $age_hour.' hours '.$age_min.' minutes';
		            } else {
		                $age_sec = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_min = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_hour = (int) $age % 24;
		                $age = (int) $age / 24;
		                $age_days = (int) $age;
		                $age_str = $age_days.' days '.$age_hour.' hours '.$age_min.' minutes';
		            }
		            echo '<span class="monospace">Deleting&nbsp;</span>'.urldecode($file).' (age = '.$age_str.")<br />";
		            unlink($cache_dir.$file);
		            $nb_erase = $nb_erase + 1;
		        } else {
		            echo '<span class="monospace">keeping&nbsp;&nbsp;</span>'.urldecode($file).'<br />';
		        }
		    }
		}
		echo "<p class='notif'>$nb_erase file(s) erased</p>";
	} else {
	    echo "<p class='notif'>Can't read cache directory</p>";
	}
    echo "<h1>Cleaning Favicon's cache directory</h1>\n";
    $nb_erase = 0;
	if($handle=opendir($favicon_cache_dir)) {
		while (false !== ($file = readdir($handle))) {
		    if ($file != '.' && $file != '..') {
		        $age = time() - filemtime($favicon_cache_dir.$file);
		        if ($age > $max_age) {
		            if ($age < 60) {
		                $age_str = $age.' seconds';
		            } elseif ($age < 3600) {
		                $age_sec = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_min = (int) $age % 60;
		                $age_str = $age_min.':'.$age_sec;
		            } elseif ($age < 86400) {
		                $age_sec = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_min = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_hour = (int) $age % 60;
		                $age_str = $age_hour.' hours '.$age_min.' minutes';
		            } else {
		                $age_sec = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_min = (int) $age % 60;
		                $age = (int) $age / 60;
		                $age_hour = (int) $age % 24;
		                $age = (int) $age / 24;
		                $age_days = (int) $age;
		                $age_str = $age_days.' days '.$age_hour.' hours '.$age_min.' minutes';
		            }
		            echo '<span class="monospace">Deleting&nbsp;</span>'.urldecode($file).' (age = '.$age_str.")<br />";
		            unlink($favicon_cache_dir.$file);
		            $nb_erase = $nb_erase + 1;
		        } else {
		            echo '<span class="monospace">keeping&nbsp;&nbsp;</span>'.urldecode($file).'<br />';
		        }
		    }
		}
		echo "<p class='notif'>$nb_erase file(s) erased </p>";
	} else {
	    echo "<p class='notif'>Can't read favicon cache directory</p>";
	}
?>
<a href="./"><img src="img/nws.png" alt="NWS" style="margin-top:.5em" /> NWS</a> | <a href="./nws-manage.php">Manage feeds</a>
</body>
</html>
