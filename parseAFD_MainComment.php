<?php
require_once "classes/config.php";
require_once "classes/AFD.php";
require_once "classes/ParseAFD.php";
require_once "classes/ParseAFD_MainComment.php";
require_once "classes/functions.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';

$fromID = (isset($_GET['fromID']) ? trim($_GET['fromID']) : null);
$toID = (isset($_GET['toID']) ? trim($_GET['toID']) : null);

if(!is_numeric($fromID)|| !is_numeric($toID))
    die("from and to are not set!");
    
if( $fromID >= $toID )
    die("from >= To!");
    
for($i=$fromID; $i<= $toID; $i++)
{
    $parseAFD_MainComment = new ParseAFD_MainComment($i);
}

?>