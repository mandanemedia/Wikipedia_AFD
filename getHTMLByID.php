<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/functions.php";
ini_set('MAX_EXECUTION_TIME', -1);

$id = $_GET["id"]; 
if(!is_numeric($id))
    die("id is not a number!");
echo Crawler::getHTMLByID($id);
?>