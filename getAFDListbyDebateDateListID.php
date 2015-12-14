<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/DebateDate.php";
require_once "classes/functions.php";
require_once "classes/AFD.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';
echo '<br/>';

$DebateDateListID = trim($_GET["DebateDateListID"]);
if(!is_numeric($DebateDateListID))
    die("DebateDateListID=$DebateDateListID is not number!");
    
$debateDate = new DebateDate($DebateDateListID);
$debateDate->getAFDList();

?>