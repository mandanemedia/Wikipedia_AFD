<?php
require_once "classes/config.php";
require_once "classes/AFD.php";
require_once "classes/ParseAFD.php";
require_once "classes/functions.php";
require_once "classes/ParseAFD_OtherComment.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';

$AFDID = (isset($_GET['AFDID']) ? trim($_GET['AFDID']) : null);

if(!is_numeric($AFDID))
    die("AFDID is not set!");
    
//421
//429
$parseAFD_OtherComment = new ParseAFD_OtherComment($AFDID);

echo $GLOBALS['log'];
$GLOBALS['log'] = "";
flush();
?>