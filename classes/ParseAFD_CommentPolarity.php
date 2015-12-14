<?php 
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";
require_once "Signature.php";
require_once "DistinguishComments.php";
require_once "Comment.php";
require_once "CommentLink.php";

class ParseAFD_CommentPolarity{
   static public $negativeDictionary = array(
        //Negative words
        "Not",      //[0]
        "None",     //[1]
        "No one",   //[2]
        "Nobody",   //[3]
        "Nothing",  //[4]
        "Nowhere",  //[5]
        "No",       //[6]
        "Neither",
        "Never",
        //Negative Adverbs
        "Hardly",
        "Scarcely",
        "Barely",
        //Negative verbs
        "Doesn’t",
        "Isn’t",
        "Wasn’t",
        "Shouldn’t",
        "Wouldn’t",
        "Couldn’t",
        "Won’t",
        "Can’t",
        "Don’t",
        //Add-on
        "fail",
        "delete"
    );
    
    
    static function polarity($givenHTML)
    {
        $findedKeywords = array();
        $outputIndex = 0;
        $outputText = "";
        $GLOBALS['log'] .="<br/>";
        for($i=0 ; $i< count(ParseAFD_CommentPolarity::$negativeDictionary); $i++)
        {
            $negative = ParseAFD_CommentPolarity::$negativeDictionary[$i];
            if( stripos ( strip_tags($givenHTML), $negative) !== false )
            {
                //for bug of No
                if( ( $i!=6 ) || ( $i==6 && $outputIndex == 0))
                {
                    $findedKeywords[] = $negative;
                    if($outputIndex== 0)
                        $outputText .= $negative;
                    else
                        $outputText .= ", ".$negative;
                    $outputIndex--;
                }
            }
        }
        $GLOBALS['log'] .= "<span class='percentage'>Polarity</span> $outputText ($outputIndex)";
        return array($outputIndex, $outputText);
    }
    static function seprateSentenceByPositionID($givenHTML, $givenPosition, $giveLinkLabel)
    {
        $sentances = preg_split('/(\.|\?|\!)( |\"|\'|\))/',$givenHTML);
        $giveLinkLabel =  (trim($giveLinkLabel));
        
        if(count($sentances) < 2)
            return $sentances[0];
        else
        {
            $startPosition = 0;
            $endPosition = strlen($sentances[1]);
            $returnSentance = "";
            
            //$GLOBALS['log'] .= "<table border='1' style='border-color:purple;'>";
            foreach($sentances as $sentance)
            {
                $endPosition = $startPosition + strlen($sentance);
                if( $startPosition <= $givenPosition &&  $givenPosition <= $endPosition )
                {
                    $returnSentance = $sentance.".";
                    $GLOBALS['log'] .=  "<br/><span class='percentage'>".strip_tags($sentance)."</span>";
                    //$GLOBALS['log'] .=  "( startPosition:$startPosition <= givenPosition:$givenPosition <= endPosition:$endPosition) ".substr($givenHTML, $givenPosition, 12)." ";
                    $GLOBALS['log'] .=  "($giveLinkLabel)";
                    if( strpos ( $returnSentance, $giveLinkLabel) === false)
                    {
                        $GLOBALS['log'] .="<span class='bad'>Error in sentance.</span>";
                        $returnSentance = "ErrorXXX-".$returnSentance;
                    }
                    break;
                }
                //else
                //  $GLOBALS['log'] .=  "<br/><span class='percentage'>".strip_tags($sentance)."</span>";
                $startPosition = $endPosition;
            }
            //$GLOBALS['log'] .= "</table>";
            return $returnSentance;
       }
    }
    
}
?>