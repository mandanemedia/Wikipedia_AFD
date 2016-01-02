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
        "Aren’t",
        "Wasn’t",
        "Shouldn’t",
        "Wouldn’t",
        "Couldn’t",
        "Won’t",
        "Can’t",
        "Don’t",
        "Hasn’t ",
        "Haven’t ",
        "Doesn't",
        "Isn't",
        "Aren't",
        "Wasn't",
        "Shouldn't",
        "Wouldn't",
        "Couldn't",
        "Won't",
        "Can't",
        "Don't",
        "Hasn't ",
        "Haven't ",
        "Does not",
        "Is not",
        "Was not",
        "Should not",
        "Would not",
        "Could not",
        "Wo not",
        "Ca not",
        "Do not",
        "Has not ",
        "Have not ",
        //Add-on
        "fail",
        "fails",
        "delete",
        "deletes",
        "failure",
        "failures",
        "wrong",
        "wrongs",
        "non-notable",
        "hate"
    );
    
    static public $positiveDictionary = array(
        //Positive words
        //Positive Adverbs
        "completely ",
        //Positive verbs
        "Agree",
        "satisfy",
        "allowed",
        "allow",
        "consensus",
        "notable"
    );
    
    static function polarity($givenHTML)
    {
        $findedKeywords = array();
        $outputIndex = 0;
        $outputText = "";
        for($i=0 ; $i< count(ParseAFD_CommentPolarity::$negativeDictionary); $i++)
        {
            $negative = ParseAFD_CommentPolarity::$negativeDictionary[$i];
            $pieces = explode(" ", strip_tags($givenHTML)." ");
            
            foreach ($pieces as $piece) {
            
                //for example there is deletion. , this going to be deletion. Remove dot(.) from word.
                // can add more than . to the foreach
                $piece = str_replace(array("."),"",$piece);
                
                $percentage = strcasecmp(strtolower(trim($piece)), strtolower(trim($negative)));
            
              /*if($i == 21 )
              {
                  $GLOBALS['log'] .="<br/>";
                  $GLOBALS['log'] .= "<hr>".strlen(strip_tags($piece))." ".strip_tags(strtolower($piece))."<span class='percentage'> $i - negative = ". strtolower($negative);
                  $GLOBALS['log'] .= "<hr></span>";
                  $GLOBALS['log'] .= " percentage = ".$percentage . " piece,len = ".strlen(strip_tags(strtolower($piece))). " negative,len = ".strlen(strip_tags(strtolower($negative)));
              } */
                //next 5 line express if difference between the lenght is less than 3 then it allows match, otherwise fail to match.
                $pieceLen = strlen(strip_tags(strtolower($piece)));
                $negativeLen = strlen(strip_tags(strtolower($negative)));
                $noequalLenght = false;
                if( ($pieceLen == $negativeLen) || ($pieceLen == ($negativeLen-1)) || ($pieceLen == ($negativeLen-2))|| ($pieceLen == ($negativeLen-3)) || (($pieceLen-1) == $negativeLen) || (($pieceLen-2) == $negativeLen)  || (($pieceLen-3) == $negativeLen) )
                    $noequalLenght = true;
                
                if($percentage == 0 && $noequalLenght  && strlen(strip_tags(strtolower($piece))) >0 )
                { 
                    //$GLOBALS['log'] .= "<span class='good'>($givenHTML)<br/> $negative =  piece ($piece)<br/></span> ";
                    
                    $findedKeywords[] = $negative;
                    if($outputIndex == 0)
                        $outputText .= $negative;
                    else
                        $outputText .= ", ".$negative;
                    $outputIndex--;
                }
            } 
        }
        // if there is no negative calculate the positive words
        if($outputIndex == 0)
        {
            // same as above only change $negativeDictionary to $positiveDictionary
            for($i=0 ; $i< count(ParseAFD_CommentPolarity::$positiveDictionary); $i++)
            {
                $negative = ParseAFD_CommentPolarity::$positiveDictionary[$i];
                $pieces = explode(" ", strip_tags($givenHTML)." ");
                
                foreach ($pieces as $piece) {
                
                    //for example there is deletion. , this going to be deletion. Remove dot(.) from word.
                    // can add more than . to the foreach
                    $piece = str_replace(array("."),"",$piece);
                    
                    $percentage = strcasecmp(strtolower(trim($piece)), strtolower(trim($negative)));
                
                    //next 5 line express if difference between the lenght is less than 3 then it allows match, otherwise fail to match.
                    $pieceLen = strlen(strip_tags(strtolower($piece)));
                    $negativeLen = strlen(strip_tags(strtolower($negative)));
                    $noequalLenght = false;
                    if( ($pieceLen == $negativeLen) || ($pieceLen == ($negativeLen-1)) || ($pieceLen == ($negativeLen-2)) || ($pieceLen == ($negativeLen-3)) || (($pieceLen-1) == $negativeLen) || (($pieceLen-2) == $negativeLen) || (($pieceLen-3) == $negativeLen)  )
                        $noequalLenght = true;
                    
                    if($percentage == 0 && $noequalLenght  && strlen(strip_tags(strtolower($piece))) >0 )
                    { 
                        //$GLOBALS['log'] .= "<span class='good'>($givenHTML)<br/> $negative =  piece ($piece)<br/></span> ";
                        
                        $findedKeywords[] = $negative;
                        if($outputIndex == 0)
                            $outputText .= $negative;
                        else
                            $outputText .= ", ".$negative;
                        $outputIndex++;
                    }
                } 
            }
        }
        
        if($outputIndex == 0)
            $GLOBALS['log'] .= "<span class='good'> Polarity - Positive</span>";
        else if($outputIndex > 0)
            $GLOBALS['log'] .= " <span class='good'> Polarity - Positive</span> $outputText ($outputIndex)";
        else
            $GLOBALS['log'] .= " <span class='Bad'>Polarity - Negative :</span> $outputText ($outputIndex)";
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
                    //The next line could be umcomment for debugging purpose.
                    //$GLOBALS['log'] .=  "<br/><span class=''>-".strip_tags($sentance)."</span>";
                    //$GLOBALS['log'] .=  "( startPosition:$startPosition <= givenPosition:$givenPosition <= endPosition:$endPosition) ".substr($givenHTML, $givenPosition, 12)." ";
                    $GLOBALS['log'] .=  "<span class='percentage'>($giveLinkLabel)</span>";
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