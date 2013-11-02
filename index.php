<?php
/*
  index : View all feeds

  This script is part of NWS
  https://github.com/xaccrocheur/nws/

*/
?>

<!DOCTYPE html>
<html>
<head>
<title>nws</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="shortcut icon" type="image/x-icon" href="img/nws.png" />
    <link href="libs/jquery-ui.css" rel="stylesheet" type="text/css" />
    <style type="text/css" media="screen">@import "nws-style.css";</style>
    <base target='_blank' />
</head>
<body>

<script src="libs/jquery.min.js"></script>
<script src="libs/jquery-ui.min.js"></script>

<script>

$(document).ready(function() {

    $.ajaxSetup ({ cache: true })

    $( "#tabs" ).tabs().find( ".ui-tabs-nav" ).sortable({ axis: "x" })

    var totaltabs = $(".tabulators").find( "li" ).size()
    var direction = null
    var ajax_loader = 'nws-load-feed.php'
    var ajax_spinner = '<img src="img/loading.gif" class="loading" alt="loading..." />'

    $('body').keyup(function(e) {

        if (e.keyCode == 37 || e.keyCode == 82)
            direction = 'prev'
        else if (e.keyCode == 39 || e.keyCode == 84)
            direction = 'next'
        else
            return

        var active_tab = $("#tabs").tabs("option", "active")

        if (direction != null)
            if (direction == 'next')
                if (active_tab < totaltabs -1)
                    $("#tabs").tabs("option", "active", active_tab + 1)
                else
                    $("#tabs").tabs("option", "active", 0)
            else
                if (active_tab != 0)
                    $("#tabs").tabs("option", "active", active_tab - 1)
                else
                    $("#tabs").tabs("option", "active", totaltabs - 1)

    })

    function pulse() {
        $('.moved').fadeIn(8000)
        $('.moved').fadeOut(200)
    }
    setInterval(pulse, 150)

    $('.reload').click(function(){
        var div_to_reload = $(this).parent()
        var feed_url = encodeURIComponent(div_to_reload.attr('title'))
        var feed_num_item = div_to_reload.attr('data-numItems')
        div_to_reload.children('div.innerContainer')
            .html(ajax_spinner)
            .load(ajax_loader, "n=" + feed_num_item + "&z=" + feed_url)
    })

    $('.reload').trigger('click')

})

</script>

<div id="tabs">

<?php

$urls = simplexml_load_file('feeds.xml');

function outerContainer($u, $numItems) {
    echo '
        <div class="outerContainer" style="" title ="'.htmlspecialchars($u, ENT_QUOTES).'" data-numItems="'.$numItems.'">
            <span class="reload" title="Reload '.htmlspecialchars($u).'">&#9889;</span>
            <div class="innerContainer"></div>
        </div>
';
}

foreach ($urls->url as $url) {
    $myAttributes = $url->attributes();
    $numItems = "16";
    $tab=NULL;
    foreach($myAttributes as $attr => $val) {
        if ($attr == 'numItems')
            $numItems = $val;
        if ($attr == 'tab')
            $tab = $val;
    }
    if (isset($tab)) {
        $myTabs[] = array('tab'=> (string) $tab, 'url'=> (string) $url, 'numItems'=> (string) $numItems);
    }
}

foreach($myTabs as $aRow) 
    $tabGroups[$aRow['tab']][] = array('url'=> $aRow['url'], 'numItems'=> $aRow['numItems']);

echo '
    <ul class="tabulators">';

foreach (array_keys($tabGroups) as $tabName) {
    echo '
        <li><a title="'.$tabName.', Drag to re-order" href="#tab-'.$tabName.'"><span>'.$tabName.'</span></a></li>';
}

echo '
    </ul>';

foreach (array_keys($tabGroups) as $tabName) {
    echo '
    <div id="tab-'.$tabName.'">';
        foreach ($tabGroups[$tabName] as $tabUrl)
            outerContainer($tabUrl['url'],$tabUrl['numItems']);
    echo '
    </div>';
}

echo '
    </div>
<a href="nws-manage.php"><img src="img/nws.png" alt="manage" style="margin-top:.5em" /> Manage feeds</a>
';

// Version Control
$commits = json_decode(file_get_contents("https://api.github.com/repos/xaccrocheur/nws/commits"));

$current_commit_minus1 = $commits[1]->sha;
$ref_commit = "648bcdcfd4eb19d9fc0030a22997e07b11431779";
$commit_message = "last message : ".$commits[0]->commit->message;

if (!strcmp($current_commit_minus1, $ref_commit)) {
    $version_class = "unmoved";
    $version_message = "NWS version is up-to-date : (".$commit_message.")";
} else {
    $version_class = "moved";
    $version_message = "New version available : (".$commit_message.")";
}

?>

<span id="version" onClick="document.location.href='https://github.com/xaccrocheur/nws'" title="<?php echo $version_message ?>">
    <span class="<?php echo $version_class ?>">♼</span>
</span>
</body>
</html>
