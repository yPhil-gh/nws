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
<title>NWS</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="robots" content="noindex,nofollow">
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

    // var overlay = jQuery('<div id="overlay"> </div>');

    $.ajaxSetup ({ cache: true })

    $( "#tabs" ).tabs().find( ".ui-tabs-nav" ).sortable({ axis: "x" })

    var totaltabs = $(".tabulators").find( "li" ).size()
    var direction = null
    var ajax_loader = 'nws-load-feed.php'
    var feed_max_age = 3600;
    var ajax_spinner = '<img src="img/loading.gif" class="loading" alt="loading..." />'

    $('body').keyup(function(e) {

        if (e.keyCode == 37 || e.keyCode == 82)
            if( $("#viewer").is(':visible') ) {
                $("#prev").trigger('click')
                direction = null
            } else {
                direction = 'prev'
            }

        if (e.keyCode == 39 || e.keyCode == 84)
            if( $("#viewer").is(':visible') ) {
                $("#next").trigger('click')
                direction = null
            } else {
                direction = 'next'
            }

        if (e.keyCode == 27) {
            close_viewer()
        }

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


    $("#tabs").bind("tabsactivate", function (event, ui) {
        document.title = "NWS : " + ui.newTab.text()
    });


    function pulse() {
        $('.moved').fadeIn(8000)
        $('.moved').fadeOut(200)
    }
    setInterval(pulse, 150)

    $('.reload').click(function(){
        var div_to_reload = $(this).parent()
        var feed_url = encodeURIComponent(div_to_reload.attr('title'))
        var feed_num_item = div_to_reload.attr('data-numItems')
        var feed_img_mode = div_to_reload.attr('data-img')
        var feed_photo_mode = div_to_reload.attr('data-photo')
        div_to_reload.children('div.innerContainer')
            .html(ajax_spinner)
            .load(ajax_loader, "n=" + feed_num_item + "&i="+feed_img_mode+"&p="+feed_photo_mode+"&age="+feed_max_age+"&z=" + feed_url)
    })

    // GALLERY

    var viewport_width = $(window).width()
    var viewport_height = $(window).height()
    var i
    var timeOut = null
    var debug = "empty"

    $("#viewer").css("top", ((viewport_height / 2) - 150) + "px")
    $("#viewer").css("left", ((viewport_width / 2) - 250) + "px")

    $('#play').click(function (e, simulated) {
        if (!simulated) {
            auto_play()
            $("#pause").css("display", "block")
            $("#play").css("display", "none")
        }
    })

    function auto_play() {
        $('#next').trigger('click', [true]);
        timeOut = setTimeout(auto_play, 3000);
    }

    $('#pause, #prev, #next, #cross').click(function (e, simulated) {
        if (!simulated) {
            clearTimeout(timeOut);
            $("#pause").css("display", "none")
            $("#play").css("display", "block")
        }
    })

    function img_gallery(i, div_id, tab_id) {

        if (!tab_id == '') {
            var images = $( "#" + tab_id).find('img').not('.favicon img')
            $("#viewer-img").attr("data-tab", tab_id)
        } else {
            var images = $( "#" + div_id).find('img').not('.favicon img')
        }

        viewport_width = $(window).width()
        viewport_height = $(window).height()

        // var count = (images.length - 1)
        var count = images.length
        var current_img = images.eq(i)

        var post_url = current_img.parent().attr("url")
        var site_url = current_img.attr("data-link").substring(7, current_img.attr("data-link").length)

        var indx = site_url.indexOf("/");

        debug = "Site: " + site_url.substring(0, indx) + " "

        // alert("yow, " + count)

        if (count < 1){
            $("#overlay").html('<div class="error">No images</div>')
            $('#overlay div').css({
                position:'absolute',
                left: ($(window).width() - $('.className').outerWidth())/2,
                top: ($(window).height() - $('.className').outerHeight())/2
            });
            exit
        }

        var theImage = new Image()
        theImage.src = current_img.attr("src")

        var curr_img_width = theImage.width
        var curr_img_height = theImage.height

        $("#viewer-img").fadeOut(0)

        if (curr_img_width < 320) {
            debug = "resized!"
            $("#viewer").css("width", 320 + "px")
            $("#viewer-img").css("width", 320 + "px")
            $("#viewer-img").css("height", 240 + "px")
            $("#viewer-img").css("height", "")
        } else {
            $("#viewer").css("width", (curr_img_width - 5) + "px")
            $("#viewer").css("height", (curr_img_height - 5) + "px")

            $("#viewer-img").css("width", (curr_img_width - 5) + "px")
            $("#viewer-img").css("height", (curr_img_height - 5) + "px")
        }

        if (curr_img_height > viewport_height) {
            $("#viewer").css("max-height", viewport_height + "px")
            $("#viewer-img").css("height", (viewport_height - 5) + "px" + "!important")
            $("#viewer-img").css("max-height", (viewport_height - 2) + "px")
            $("#viewer-img").css("width", "")
        }

        // debug = debug + "W: " + curr_img_width + " / " + viewport_width + " H: " + curr_img_height + " / " + viewport_height + " - "

        $("#viewer").css("display", "block")
        $("#viewer-img").attr("src", current_img.attr("src"))


        $("#viewer-img").attr("data-index", i)
        $("#viewer-img").attr("data-count", count)
        $("#viewer-img").attr("data-id", div_id)
        $("#img-name a").text(debug + "[#" + i + " of " + count + "] " + current_img.attr("alt"))
        $("#img-name a").attr("href", current_img.attr("data-link"))
        $("#img-name a").attr("title", current_img.attr("alt"))
        $("#link-img").attr("href", current_img.attr("src"))

        $("#viewer-img").fadeIn(400)
    }

    $('.gallery-feed').click(function(){
        $("#overlay").show()
        var div_id = $(this).parent().attr("id")
        var tab_id = ''
        img_gallery(0, div_id, tab_id)
    })

    $('.gallery-tab').click(function(){
        $("#overlay").show()
        var div_id = $(this).parent().attr("id")
        var tab_id = $(this).parent().parent().attr("id")
        // alert("plop " + tab_id)
        img_gallery(0, div_id, tab_id)
    })

    // Reposition and resize the image according to viewport
    $(window).resize(function () {
        viewport_width = $(window).width()
        viewport_height = $(window).height()

        $("#viewer").css("top", ((viewport_height / 2) - 150) + "px")
        $("#viewer").css("left", ((viewport_width / 2) - 250) + "px")

        if (viewport_width < 500) {
            $("#viewer").css("left", "0px")
            $("#viewer").css("width", viewport_width + "px")
            $("#viewer-img").css("width", (viewport_width - 10) + "px")
        }

    })

    $("#prev").click(function () {

        var myindex = $(this).parent().find("img").attr("data-index")
        var mycount = $(this).parent().find("img").attr("data-count")
        var mytab = $(this).parent().find("img").attr("data-tab")
        var mydiv_id = $("#viewer-img").attr("data-id")
        myindex = parseInt(myindex)
        mycount = parseInt(mycount)

        if (myindex > mycount) {
            myindex = mycount
        } else {
            if (myindex > 1) {
                myindex = (myindex - 1)
            } else {
                myindex = mycount
            }
        }
        img_gallery(myindex, mydiv_id, mytab)
    })

    $("#next").click(function () {

        var myindex = $(this).parent().find("img").attr("data-index")
        var mycount = $(this).parent().find("img").attr("data-count")
        var mytab = $(this).parent().find("img").attr("data-tab")
        var mydiv_id = $("#viewer-img").attr("data-id")
        myindex = parseInt(myindex)

        if (myindex < mycount) {
            myindex = (myindex + 1)
        }
        else {
            myindex = 1
        }

        img_gallery(myindex, mydiv_id, mytab)
    })

    $("#cross").click(function () {
        close_viewer()
    })

    $("#overlay").click(function () {
        close_viewer()
    })

    function close_viewer() {
        clearTimeout(timeOut);
        $("#pause").css("display", "none")
        $("#play").css("display", "block")
        $("#viewer").css("display", "none");
        $("#overlay").hide()
        $("#overlay").html('')
    }

    $('.reload').trigger('click')
    feed_max_age = 10; // allow to force reloading the feed
})

</script>

<div id="tabs">

<?php

$urls = simplexml_load_file('feeds.xml');
$img_modes=array('none'=> 'none', 'all'=> 'all', 'first'=> 'first');

function outerContainer($u, $numItems, $img, $photo) {

    /* $div_id = substr(htmlspecialchars($u, ENT_QUOTES), 7, strlen(htmlspecialchars($u, ENT_QUOTES))); */
    $div_id = substr($u, 7, strlen($u));

    $illegal_chars = array(".", "?", "=", "/", "&", "_", "-", ";", ",", "+");

    $pos = strlen($div_id);
    /* $div_id = str_replace($illegal_chars, "", substr($div_id, 0, $pos)); */

    $div_id = htmlspecialchars(str_replace($illegal_chars, "", substr($div_id, 0, $pos)), ENT_QUOTES);

    echo '
        <div class="outerContainer" style="" title ="'.htmlspecialchars($u, ENT_QUOTES).'" data-numItems="'.$numItems.'" data-img="'.$img.'" data-photo="'.$photo.'" id="'.$div_id.'">
            <span class="reload" title="Reload '.htmlspecialchars($u).'">&#9889;</span>
            <span class="gallery-feed" title="View '.htmlspecialchars($u).' images">►</span>
            <span class="gallery-tab" title="View all images in this tab">►</span>
            <div class="innerContainer"></div>
        </div>
';
}

foreach ($urls->url as $url) {
    $myAttributes = $url->attributes();
    $numItems = "16";
    $img = 'all';
    $photo = '';
    $tab=NULL;
    foreach($myAttributes as $attr => $val) {
        if ($attr == 'numItems')
            $numItems = $val;
        if ($attr == 'tab')
            $tab = $val;
        if ($attr == 'img')
            $img = $val;
        if ($attr == 'photo')
            $photo = $val;
    }

    if (isset($tab)) {
        $myTabs[] = array('tab'=> (string) $tab, 'url'=> (string) $url, 'numItems'=> (string) $numItems , 'img'=> (string) $img, 'photo'=> (string) $photo);
    }
}

foreach($myTabs as $aRow)
    $tabGroups[$aRow['tab']][] = array('url'=> $aRow['url'], 'numItems'=> $aRow['numItems'], 'img'=> $aRow['img'], 'photo'=> $aRow['photo']);

echo '
    <ul class="tabulators">';

foreach (array_keys($tabGroups) as $tabName) {
    echo '
        <li><a title="'.$tabName.', Drag to re-order" href="#tab-'.$tabName.'"><span class="tabName">'.$tabName.'</span></a></li>';
}

echo '
    </ul>';

foreach (array_keys($tabGroups) as $tabName) {
    echo '
    <div id="tab-'.$tabName.'">';
        foreach ($tabGroups[$tabName] as $tabUrl)
            outerContainer($tabUrl['url'],$tabUrl['numItems'],$tabUrl['img'],$tabUrl['photo']);
    echo '
    </div>';
}

echo '
        <div id="viewer">
            <a id="link-img">
                <img id="viewer-img" />
            </a>
            <span id="cross" title="Close" aria-hidden="true" class="icon-close"></span>
            <span id="img-name"><a></a></span>
            <span id="prev" title="Previous" aria-hidden="true" class="icon-prev"></span>
            <span id="next" title="next" aria-hidden="true" class="icon-next"></span>
            <span id="pause" title="Pause Slideshow" aria-hidden="true" class="icon-pause"></span>
            <span id="play" title="Start Slideshow" aria-hidden="true" class="icon-play"></span>
        </div>
    </div>
<a href="nws-manage.php"><img src="img/nws.png" alt="manage" style="margin-top:.5em" /> Manage feeds</a>
';


// Version Control

$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"User-Agent: nws"
  )
);

$context = stream_context_create($opts);

$current_commits = file_get_contents("https://api.github.com/repos/xaccrocheur/nws/commits", false, $context);

if ($current_commits !== false) {
    $commits = json_decode($current_commits);

    $ref_commit = "7fda92c03932cf7c3236ca92ce7b9c7606216467";

    $current_commit_minus1 = $commits[1]->sha;
    $commit_message = "last message : ".$commits[0]->commit->message;

    if (!strcmp($current_commit_minus1, $ref_commit)) {
        $version_class = "unmoved";
        $version_message = "NWS version is up-to-date : (".$commit_message.")";
    } else {
        $version_class = "moved";
        $version_message = "New version available : (".$commit_message.")";
    }
} else {
        $version_class = "unknown";
        $version_message = "Can't read NWS version status";
}

?>
<div id="overlay"> </div>
<span id="version" onClick="document.location.href='https://github.com/xaccrocheur/nws'" title="<?php echo $version_message ?>">
    <span class="<?php echo $version_class ?>">♼</span>
</span>
</body>
</html>
