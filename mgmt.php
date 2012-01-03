<html>
<head>
<style type="text/css" media="screen">@import "nws.css";</style>
<title>Manage feeds</title>
<style>
form {
 margin:0;
}
</style>
</head>
<body>
<div><?php

/* error_reporting(E_ALL); */
/* ini_set('display_errors', 1); */

if (isset($_GET['d'])) $feedToDel = $_GET['d'];
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
/* if (isset($_POST['idFeed'])) $idFeed = $_POST['idFeed']; */

/* echo '<pre>'; */
/*     var_dump($_POST); */
/* echo '</pre>'; */

/* var_dump($tabName); */
/* var_dump(); */

$domIn = new DOMDocument;
$domIn->load('feeds.xml');

$domOut = new DOMDocument('1.0', 'utf-8');
$domOut->preserveWhiteSpace=false;
$domOut->formatOutput=true;
$root = $domOut->createElement('feeds');
$domOut->appendChild($root);

$urlTagList = $domIn->getElementsByTagName('url');

$entries = $urlTagList->length;
$feeds = $domIn->documentElement;

if (isset($feedToDel)) {
  $feed = $feeds->getElementsByTagName('url')->item($feedToDel);
  $feeds->removeChild($feed);
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

echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">';

for ($i=0; $i < $defUrlTagList->length ; $i++) {
  $deffeedz[] = $defUrlTagList->item($i)->nodeValue;
  $myFeedz[] = array("url" => $defUrlTagList->item($i)->nodeValue, "tab" => $defUrlTagList->item($i)->getAttribute('tab'));
  echo ' <a title="Delete this feed" class="feedDel" href="'.$_SERVER['PHP_SELF'].'?d='.$i.'">x</a>&nbsp;<a title="Promote this feed as 1st of itstab" class="feedUp" href="'.$_SERVER['PHP_SELF'].'?u='.$i.'">^</a><input type="hidden" name="idFeed" value="'.$i.'">
 <select title="Change this feed\'s tab" name="tabName" value="">';

  echo '<option value="'.$myFeedz[$i]['tab'].'" selected="yes">'.$myFeedz[$i]['tab'].'</option>';

  foreach (array_unique($defUrlTabList) as $defUrlTab) {
    echo '<option value="'.$defUrlTab.'">'.$defUrlTab.'</option>';
  }

  echo '
</select>
<input title="New tab" type="text" size="6" name="newTabName" value=""> <input type="submit" value="<"> '.$myFeedz[$i]['url'].'<br />';
}

echo '</form>';

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