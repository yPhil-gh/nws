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
$max_age = 3600;    
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
		    if ($file != '.' && $file != '..') {
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
		            echo 'Deleting file '.urldecode($file).' (age = '.$age_str.")<br />\n";
		            unlink($cache_dir.$file);
		            $nb_erase = $nb_erase + 1;
		        } else {
		            echo 'keeping '.urldecode($file)."<br />\n";
		        }
		    }
		}
		echo $nb_erase." file(s) erased <br />\n";
	} else {
	    echo "Can't read cache directory<br />\n";
	}
?>
</body>
</html>
