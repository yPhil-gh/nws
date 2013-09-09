<?
/*
  MGMT : Manage (add, categorise, promote, delete) feeds

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
?>

<html>
<head>
    <style type="text/css" media="screen">@import "nws-style.css";</style>
    <title>nws - Manage feeds</title>
</head>
<body>
    <div>

<?

class XDOMElement extends DOMElement {
    function __construct($name, $value = null, $namespaceURI = null) {
        parent::__construct($name, null, $namespaceURI);
    }
}

class XDOMDocument extends DOMDocument {
    function __construct($version = null, $encoding = null) {
        parent::__construct($version, $encoding);
        $this->registerNodeClass('DOMElement', 'XDOMElement');
    }

    function createElement($name, $value = null, $namespaceURI = null) {
        $element = new XDOMElement($name, $value, $namespaceURI);
        $element = $this->importNode($element);
        if (!empty($value)) {
            $element->appendChild(new DOMText($value));
        }
        return $element;
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_GET['d'])) $feedToDel = $_GET['d'];
/* if (isset($_POST['d'])) $feedToDel = $_POST['d']; */
if (isset($_GET['u'])) $feedToUp = $_GET['u'];

if (isset($_POST['a']) && filter_var($_POST['a'], FILTER_VALIDATE_URL) && !empty($_POST['a'])) $feedToAdd = $_POST['a'];

if (!empty($_POST['tabName'])) {
    $tabName = $_POST['tabName'];
    $feedToRetab = $_POST['idFeed'];
}
if (!empty($_POST['newTabName'])) {
    $tabName = $_POST['newTabName'];
    $feedToRetab = $_POST['idFeed'];
}

$domIn = new XDOMDocument;
$domIn->load('feeds.xml');

$domOut = new XDOMDocument('1.0', 'utf-8');
$domOut->preserveWhiteSpace=false;
$domOut->formatOutput=true;
$root = $domOut->createElement('feeds');
$domOut->appendChild($root);

$urlTagList = $domIn->getElementsByTagName('url');

$entries = $urlTagList->length;
$feeds = $domIn->documentElement;

if (isset($feedToDel)) {
    $del = $feeds->getElementsByTagName('url')->item($feedToDel);
    $feeds->removeChild($del);
}

if (isset($feedToUp)) {
    $up = $feeds->getElementsByTagName('url')->item($feedToUp);
    $upValue = $up->nodeValue;
    $upTab = $up->getAttribute('tab');
    $feeds->removeChild($up);
}

if (isset($feedToRetab)) {
    $re = $feeds->getElementsByTagName('url')->item($feedToRetab);
    $reValue = $re->nodeValue;
    $feeds->removeChild($re);
}

$newUrlTagList = $domIn->getElementsByTagName('url');

for ($i=0; $i < $newUrlTagList->length ; $i++) {
    $currentTab = $newUrlTagList->item($i)->getAttribute('tab');
    $fedz[] = array ('tab' => $currentTab,  'url' => $newUrlTagList->item($i)->nodeValue);
}

if (isset($feedToAdd)) {
    $newUrlTag = $domOut->createElement('url', $feedToAdd);
    $newAttribute = $domOut->createAttribute('tab');
    $newAttribute->value = 'unsorted';
    $newUrlTag->appendChild($newAttribute);
    $root->appendChild($newUrlTag);
}

if (isset($feedToUp)) {
    $newUpped = $domOut->createElement('url', $upValue);
    $domAttribute = $domOut->createAttribute('tab');
    $domAttribute->value = $upTab;
    $newUpped->appendChild($domAttribute);
    $root->appendChild($newUpped);
}

if (isset($feedToRetab)) {
    $newRetabbed = $domOut->createElement('url', $reValue);
    $newRetabbedAttr = $domOut->createAttribute('tab');
    $newRetabbedAttr->value = $tabName;
    $newRetabbed->appendChild($newRetabbedAttr);
    $root->appendChild($newRetabbed);
}


// re-creation of the file, new and promoted first
foreach ($fedz as $fed) {

    $newTag = $domOut->createElement('url', $fed['url']);

    $oldAttribute = $domOut->createAttribute('tab');
    // Value for the created attribute
    $oldAttribute->value = $fed['tab'];

    // Don't forget to append it to the element
    $newTag->appendChild($oldAttribute);

    // Append it to the document itself
    /* $domDocument->appendChild($domElement); */

    $root->appendChild($newTag);
}

/* foreach ($feedz as $key => $feed) { */
/*   $urlTag = $domOut->createElement('url', $feed); */
/*   $root->appendChild($urlTag); */
/* } */

$defUrlTagList = $domOut->getElementsByTagName('url');

foreach ($defUrlTagList as $defUrlTag) {
    $defUrlTabList[] = $defUrlTag->getAttribute('tab');
}


for ($i=0; $i < $defUrlTagList->length ; $i++) {

    $deffeedz[] = $defUrlTagList->item($i)->nodeValue;
    $myFeedz[] = array("url" => $defUrlTagList->item($i)->nodeValue, "tab" => $defUrlTagList->item($i)->getAttribute('tab'));

    /* $doc = new DOMDocument(); */
    /* $doc->strictErrorChecking = FALSE; */
    /* $doc->loadHTML(file_get_contents($myFeedz[$i]['url'])); */
    /* $xml = simplexml_import_dom($doc); */
    /* $arr = $xml->xpath('//link[@rel="shortcut icon"]'); */
    /* $favicon = $arr[0]['href']; */

    /* $url = parse_url($myFeedz[$i]['url']); */
    /* $subs = explode( '.', $url['host']); */
    /* $domain = $subs[count($subs) -2].'.'.$subs[count($subs) -1]; */
    /* $favicon = (getimagesize($url['scheme'].'://'.$domain.'/favicon.ico') ? $url['scheme'].'://'.$domain.'/favicon.ico' : 'favicon.png'); */

    $favicon = 'http://g.etfv.co/'.$myFeedz[$i]['url'];

    echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';

    echo ' <a title="Delete this feed" class="feedDel" href="'.$_SERVER['PHP_SELF'].'?d='.$i.'">x</a>&nbsp;<a title="Promote this feed as 1st of its tab" class="feedUp" href="'.$_SERVER['PHP_SELF'].'?u='.$i.'">^</a><input type="hidden" name="idFeed" value="'.$i.'">
 <select title="Change this feed\'s tab" name="tabName" value="">';

        echo '<option value="'.$myFeedz[$i]['tab'].'" selected="yes">'.$myFeedz[$i]['tab'].'</option>';

        foreach (array_unique($defUrlTabList) as $defUrlTab) {
            echo '<option value="'.$defUrlTab.'">'.$defUrlTab.'</option>';
        }

        echo '
</select>
<input title="New tab" type="text" size="6" name="newTabName" value="">
<input type="submit" value="<">
<img height="16" src="'.$favicon.'" />
'.$myFeedz[$i]['url'].'
<br />';
        echo '</form>';
}




if (isset($feedToUp) || isset($feedToAdd) || isset($feedToDel) || isset($feedToRetab)) {
    echo '<hr />Wrote: (' . $domOut->save("feeds.xml") . ') bytes';
// header("Cache-Control: no-cache");
/* header('Location: '.$_SERVER['PHP_SELF'].'#'); */
}
echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';
?>
            <input type="text" title="Add a new feed" name="a">
            <input type="submit" name="submit" value="+">
        </form>
        <a href="./">nws</a>
    </div>
</body>
</html>
