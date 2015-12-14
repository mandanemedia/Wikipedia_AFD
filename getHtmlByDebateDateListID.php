<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/functions.php";
ini_set('MAX_EXECUTION_TIME', -1);

$DebateDateListID = $_GET["DebateDateListID"];
if(!is_numeric($DebateDateListID))
    die("DebateDateListID is not a number!");
     
echo Crawler::getHTMLByDebateDateListID($DebateDateListID);
?>