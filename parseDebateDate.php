<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/ParseDebateDate.php";
require_once "classes/DebateDate.php";
require_once "classes/AFD.php";
require_once "classes/functions.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';
echo '<br/>';

$fromID = (isset($_GET['fromID']) ? trim($_GET['fromID']) : null);
$toID = (isset($_GET['toID']) ? trim($_GET['toID']) : null);

if(!is_numeric($fromID)|| !is_numeric($toID))
    die("from and to are not set!");
    
if( $fromID >= $toID )
    die("from >= To!");
// need to run from 26 to 50
for($i=$fromID; $i<= $toID; $i++)
{
   $parseDebateDate = new ParseDebateDate($i);
   
   $debateDate = new DebateDate($i);
   $debateDate->updateTotalAFD_inDB();   
}

?>