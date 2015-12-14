<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/functions.php";
require_once "classes/AFD.php";
ini_set('MAX_EXECUTION_TIME', -1);

$AFDURL = trim($_GET["AFDURL"]);
if(empty($AFDURL))
    die("AFDURL=$AFDURL is empty!");
echo AFD::getHTMLByURL($AFDURL);

?>