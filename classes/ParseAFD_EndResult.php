<?php 
require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";
require_once "Signature.php";

class ParseAFD_EndResult{
  
    public $givenAFDID;
    public $afd;
    public $endResult_Html;
   
    public $parsed_EndResultError = 0;
    
    public $startParseTime = 0;
    public $endParseTime = 0;  
    
    public function ParseAFD_EndResult($givenID) {
        $this->startParseTime = date('Y-m-d H:i:s');
        $GLOBALS['log'] .= "<hr/><span class='startCall'>****************** Call ParseAFD_EndResult->ParseAFD_EndResult() </span>";
        
        $this->afd = new AFD("","","");
        $this->afd->load_EndResult_FromDB_ByAFDID($givenID);
    
        $this->givenAFDID = $givenID;
        $this->endResult_Html = $this->afd->endResult_Html;
        $this->setLog();
        
        //call parse
        if( $this->afd->flag_toBeRemoved != 1)
            $this->parseAFD_Content_endResult();
        
        $this->endParseTime = date('Y-m-d H:i:s');
        
        $parseDuration = round( (strtotime($this->endParseTime) - strtotime($this->startParseTime)) / 3600 * 60, 2);

        $GLOBALS['log'] .= "<br/> Parsed Duration: ".($parseDuration*60)." Sec.";
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_EndResult->ParseAFD_EndResult()*******************</span><hr/>";
    
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        flush();
    }
    
    function setLog() {
        $GLOBALS['log'] .=  "<br/> givenAFDID = $this->givenAFDID";
        $GLOBALS['log'] .=  "<br/> endResult_Html = ". round((strlen($this->endResult_Html)/1024),1)."KB";
        $GLOBALS['log'] .=  "<div class='percentage'> endResult_Html:</div><div class='endResult_Html'>".$this->endResult_Html."</div>";
    }
    
    function parseAFD_Content_endResult()
    {
        $GLOBALS['log'] .= "<br/> <span class='startCall'> ****************** Call ParseAFD_EndResult->parseAFD_Content_endResult() <a target='_blank' <a href='GetAFDListbyDebateDateListID.php?DebateDateListID=".$this->afd->debateDateListID."#".$this->afd->AFDTitleID."'>". $this->afd->AFDTitle."</a> </span>";
        
        try{
            if (!$this->afd)  
                throw new Exception('this->afd is empty!');
                
            //is allowed to parse the endresult details
            if($this->afd->flag_DoNotParse != 1)
            {
                $parse_endResult_details = 0;
                
                //1.Cut off extra from the START section
                $condition_TheResultIs = preg_match_all("/(The result was)/", $this->endResult_Html, $matches_TheResultIs, PREG_OFFSET_CAPTURE);
                if($condition_TheResultIs>0)
                {
                    $startPosition_TheResultIs = $matches_TheResultIs[0][0][1] + 14 ;
                    $endResult_Middle = substr($this->endResult_Html, $startPosition_TheResultIs );
                    
                    //2.Cut off extra from the END section
                    $condition_TheEndResultDate = preg_match_all("/\((UTC)\)/i", $endResult_Middle, $matches_TheEndResultDate, PREG_OFFSET_CAPTURE);
                    if($condition_TheEndResultDate>0)
                    {
                        
                        $condition_TheEndResultDate_LastIndex = count($matches_TheEndResultDate[1])-1;
                        $endPosition_TheEndResultDate = $matches_TheEndResultDate[0][$condition_TheEndResultDate_LastIndex][1];
                        $endResult_Middle = substr($endResult_Middle, 0, $endPosition_TheEndResultDate + 5 );
                        
                        //3.Extract the main section of EndResult
                        $condition_MainEndResult = preg_match_all("'<b>(.*?)</b>'i", $endResult_Middle, $matches_MainEndResult, PREG_OFFSET_CAPTURE);
                        if($condition_MainEndResult>0)
                        {
                            $MainEndResult = $matches_MainEndResult[1][0][0];
                            $GLOBALS['log'] .="<br/><span class='good'>Full Matched:</span>".$MainEndResult. " -- $condition_TheEndResultDate -- Start".  + ($matches_MainEndResult[0][0][1]) ." Lenght :".strlen("The result was ".$MainEndResult);
                            $startPosition_MainEndResult = $matches_MainEndResult[1][0][1] + strlen($matches_MainEndResult[1][0][0]);
                            $endResult_Middle = substr($endResult_Middle, $startPosition_MainEndResult );
                      
                            $signature = Signature::parse($endResult_Middle);
                            
                            if($signature->parsedPassedNo > 2 )
                            {
                                if($signature->parsedPassedNo == 4 )
                                    $this->afd->parse_endResult_details = 1;
                                else
                                    $this->afd->parse_endResult_details = 0;
                                
                                $this->afd->endResult = $MainEndResult;
                                $this->afd->endResult_User = $signature->userID;
                                $this->afd->endResult_UserPosition = strlen("The result was ".$MainEndResult) + $signature->userID_StartPos;
                                $this->afd->endResult_UserTitle = $signature->userTitle;
                                $this->afd->endResult_UserURL = $signature->userURL; 
                                $this->afd->endResult_UserURLType = $signature->userURLType;
                                $this->afd->endResult_Note = $signature->initialSentance;
                                $this->afd->endResult_Date = $signature->date;
                                $this->afd->endResult_Time = $signature->time;
                                $this->afd->endResult_DateTime =  date('Y-m-d H:i:s', strtotime($signature->date." ".$signature->time));
                                $this->afd->updateAFD_OnlyEndResult_byAFDID();
                            }
                        }
                        else
                            $GLOBALS['log'] .="<br/><span class='bad'>Failed to Match Main_EndResult.</span>";
                    }
                    else
                        $GLOBALS['log'] .="<br/><span class='bad'>Failed to Match.</span>";
                }
                else
                    $GLOBALS['log'] .="<br/><span class='bad'>Failed to Match.</span>";
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