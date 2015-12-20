<?php
require_once "classes/config.php";
require_once "classes/functions.php";
require_once "classes/ParseAFD_CommentPolarity.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';

$giveHTML = ': Fails WP:GNG. The subject hasn\'t received significant coverage in reliable sources to have a stand alone article.';

$polarity = ParseAFD_CommentPolarity::polarity($giveHTML);

echo $giveHTML;
echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        flush();
        
        
?>