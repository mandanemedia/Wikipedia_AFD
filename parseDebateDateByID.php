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
echo '
<style>
body{
    background-color:black;
    color:white;
}
</style>';

$id = trim($_GET["id"]);
if(!is_numeric($id))
    die("id is not a number!");
    
$parseDebateDate = new ParseDebateDate($id);
$debateDate = new DebateDate($id);
$debateDate->updateTotalAFD_inDB();  
?>