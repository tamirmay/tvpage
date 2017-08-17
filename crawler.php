<?php
class Crawler
{
    public $url;
    public $baseUrl;
    public $depth;
    public $pageTitle;
}

$allLinks = array();
$checkUrl = "";
$selectedDepth = 0;
$currLevel = 0;

function isNullOrEmptyString($param){
    return (!isset($param) || trim($param)==='');
}

//init params from form
$selectedDepth = (int)$_POST['select_depth'];
$checkUrl = $_POST['txt_url'];
if(isNullOrEmptyString($selectedDepth) || isNullOrEmptyString($checkUrl)) {
   echo "<span class='crawler-error' style='color : red'> Missing values in url and / or depth fields </span>";
   echo " <a target='_self' href='https://tamirmay.000webhostapp.com/'> click here to start again </a>";
   die();
}

//add http if user searches a url that does not start with http
if (strpos($checkUrl, 'http') !== 0) {
   $checkUrl = "http://" . $checkUrl;
}

function indentCell($selectedDepth, $currDepth) {
  $indentBy = ($selectedDepth - $currDepth) * 2;
  $rv = "";
  for ($x = 0; $x <= $indentBy; $x++) {
      $rv = $rv . "-";  
  }   
  return $rv;
}

function crawl_page($url, $depth = 5) {
    global $allLinks;
    global $currLevel;

    static $seen = array();
    if (isset($seen[$url]) || $depth === 0) {
        return;
    }
    $seen[$url] = true;

    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($url);
     
    $pageTitle = "";
    $list = $dom->getElementsByTagName("title");
    if ($list->length > 0) {
        $pageTitle = $list->item(0)->textContent;
    }

    $anchors = $dom->getElementsByTagName('a');
    foreach ($anchors as $element) {
        $href = $element->getAttribute('href');    
        $hrefTop = $url;   
        if (0 !== strpos($href, 'http')) {
            $path = '/' . ltrim($href, '/');
            if (extension_loaded('http')) {
                $href = http_build_url($url, array('path' => $path));
            } else {
                $parts = parse_url($url);
                $href = $parts['scheme'] . '://';
                if (isset($parts['user']) && isset($parts['pass'])) {
                    $href .= $parts['user'] . ':' . $parts['pass'] . '@';
                }
                $href .= $parts['host'];
                if (isset($parts['port'])) {
                    $href .= ':' . $parts['port'];
                }
                $href .= $path;
            }
        }
        $crawler = new Crawler($depth, $hrefTop, $href);
        $crawler->depth = $depth;
        $crawler->baseUrl= $hrefTop;
        $crawler->url= $href;
        $crawler->pageTitle= $pageTitle;

        array_push($allLinks, $crawler); //add urls that start with http to the results array
        crawl_page($href, $depth - 1);
    }
   
}
crawl_page($checkUrl, $selectedDepth);
$resultsCount = count($allLinks);
echo "<h3><a href = '$checkUrl'>$checkUrl</a>, selected depth: $selectedDepth found $resultsCount results :</h3>";
echo "<table class='table'><th style = 'background:#c7e8f9'>Linked web pages</th><th style = 'background:#c7e8f9'>Page title</th></tr>";

foreach($allLinks as $value){  
    $indenetStr = indentCell($selectedDepth, $value->depth);  
    echo "<tr class='depth_$value->depth'>             
              <td style = 'background: #d9edf7'> $indenetStr <a href ='$value->url' target='_blank'>$value->url</a></td>
              <td style = 'background: #d9edf7'> $value->pageTitle </td>
          </tr>";
}
echo "</table>";

?>
