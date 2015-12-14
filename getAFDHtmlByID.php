<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/functions.php";
require_once "classes/AFD.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';
echo '<br/>';

$id = trim($_GET["id"]);
if(!is_numeric($id))
    die("id=$id is not number!");
echo AFD::getHTMLByID($id);

?>