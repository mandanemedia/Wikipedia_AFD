<?php 
require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";
require_once "Signature.php";

class ParseAFD_MainComment{
  
    public $givenAFDID;
    public $afd;
    public $mainComment_Html;
   
    public $parsed_MainCommentError = 0;
    public $startParseTime = 0;
    public $endParseTime = 0;  
    
    public function ParseAFD_MainComment($givenID) {
        $this->startParseTime = date('Y-m-d H:i:s');
        $GLOBALS['log'] .= "<hr/><span class='startCall'>****************** Call ParseAFD_MainComment->ParseAFD_MainComment() </span>";
        
        $this->afd = new AFD("","","");
        $this->afd->load_MainComment_FromDB_ByAFDID($givenID);
    
        $this->givenAFDID = $givenID;
        $this->mainComment_Html  = $this->afd->mainComment_Html;
        $this->setLog();
        
        //call parse
        if( $this->afd->flag_toBeRemoved != 1)
            $this->parseAFD_Content_mainComment();
        
        $this->endParseTime = date('Y-m-d H:i:s');
        
        $parseDuration = round( (strtotime($this->endParseTime) - strtotime($this->startParseTime)) / 3600 * 60, 2);

        $GLOBALS['log'] .= "<br/> Parsed Duration: ".($parseDuration*60)." Sec.";
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_MainComment->ParseAFD_MainComment()*******************</span><hr/>";
    
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        flush();
    }
    
    function setLog() {
        $GLOBALS['log'] .=  "<br/> givenAFDID = $this->givenAFDID";
        $GLOBALS['log'] .=  "<br/> mainComment_Html = ". round((strlen($this->mainComment_Html)/1024),1)."KB";
        //$GLOBALS['log'] .=  "<div class='percentage'> mainComment_Html:</div><div class='mainComment_Html'>".$this->mainComment_Html."</div>";
    }
    
    function parseAFD_Content_mainComment()
    {
        $GLOBALS['log'] .= "<span class='startCall'> ****************** Call ParseAFD_MainComment->parseAFD_Content_mainComment() <a target='_blank' <a href='GetAFDListbyDebateDateListID.php?DebateDateListID=".$this->afd->debateDateListID."#".$this->afd->AFDTitleID."'>". $this->afd->AFDTitle."</a> </span>";
        
        try{
            if (!$this->afd)  
                throw new Exception('this->afd is empty!');
                
            //is allowed to parse the mainComment details
            if($this->afd->flag_DoNotParse != 1)
            {
                //Extract Signiture the mainComment
                //$condition_MainEndResult = preg_match_all("'<b>(.*?)</b>'i", $this->mainComment_Html, $matches_MainEndResult, PREG_OFFSET_CAPTURE);
                //if($condition_MainEndResult>0)
                {
                    //$MainEndResult = $matches_MainEndResult[1][0][0];
                    //$GLOBALS['log'] .="<br/><span class='good'>Full Matched:</span>".$MainEndResult. " -- $condition_TheEndResultDate -- Start".  + ($matches_MainEndResult[0][0][1]) ." Lenght :".strlen("The result was ".$MainEndResult);
                    //$startPosition_MainEndResult = $matches_MainEndResult[1][0][1] + strlen($matches_MainEndResult[1][0][0]);
                    //$endResult_Middle = substr($endResult_Middle, $startPosition_MainEndResult );
              
                    $signature = Signature::parse($this->mainComment_Html);
                    
                    if($signature->parsedPassedNo == 4 )
                        $this->afd->parse_mainComment = 1;
                    else
                        $this->afd->parse_mainComment = 0;
                    
                    $mainComment_ExtraNote2_temp = substr($this->mainComment_Html, $signature->getAfterUTC_Position()); 
                    
                    if( strlen(trim($mainComment_ExtraNote2_temp)) > 15 )
                    {
                        $this->afd->mainComment_ExtraNote2 = $mainComment_ExtraNote2_temp;
                        $GLOBALS['log'] .="<br/><span class='percentage'>MainComment_ExtraNote2:</span>"." - lenght: ".strlen(trim($this->afd->mainComment_ExtraNote2))." - ".$this->afd->mainComment_ExtraNote2 ;
                    }
                    //$this->afd->endResult = $MainEndResult;
                    $this->afd->mainComment_User = $signature->userID;
                    $this->afd->mainComment_UserPosition = $signature->userID_StartPos;
                    $this->afd->mainComment_UserTitle = $signature->userTitle;
                    $this->afd->mainComment_UserURL = $signature->userURL; 
                    $this->afd->mainComment_UserURLType = $signature->userURLType; 
                    $this->afd->mainComment_Note = $signature->initialSentance ;
                    $this->afd->mainComment_Date = $signature->date;
                    $this->afd->mainComment_Time = $signature->time;
                    $this->afd->mainComment_DateTime =  date('Y-m-d H:i:s', strtotime($signature->date." ".$signature->time));
                    $this->afd->updateAFD_OnlyMainComment_byAFDID();
                }
                   
            }
            else
                $GLOBALS['log'] .="<br/><span class='startCall'> By pass by flag_DoNotParse = 1.</span>";
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_EndResult->parseAFD_Content_endResult()*******************</span>";
    }
}
?>