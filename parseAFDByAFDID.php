<?php
require_once "classes/config.php";
require_once "classes/AFD.php";
require_once "classes/ParseAFD.php";
require_once "classes/functions.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';

$AFDID = (isset($_GET['AFDID']) ? trim($_GET['AFDID']) : null);

if(!is_numeric($AFDID))
    die("AFDID is not set!");

$parseAFD = new ParseAFD($AFDID);

?>