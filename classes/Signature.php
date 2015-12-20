<?php 
require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";

class Signature {
    public $givenSentance;
    public $remainingSentance;
    public $initialSentance;
    public $userID;
    public $userID_StartPos;
    public $userTitle;
    public $userTitle_StartPos;
    public $userURL;
    public $userURLType;
    public $time;
    public $time_StartPos;
    public $date;
    public $date_StartPos;
    public $parsedPassedNo;
    public $errorMessage;

    public function Signature($givenSentance) {
        $this->givenSentance = $givenSentance;  
        $this->parsedPassedNo = 0;  
        $this->errorMessage = " -- ";  
    }
    
    public function log() {
        $GLOBALS['log'] .="<br/><span class='percentage'>givenSentance to Parse Signature:</span><br/>".$this->givenSentance ;
        $GLOBALS['log'] .="<br/><span class='good'>UserID:</span><br/>".$this->userID ;
        $GLOBALS['log'] .="<br/><span class='good'>UserID Position:</span><br/>".$this->userID_StartPos ;
        $GLOBALS['log'] .="<br/><span class='good'>UserTitle:</span><br/>".$this->userTitle ;
        $GLOBALS['log'] .="<br/><span class='good'>UserTitle Position:</span><br/>".$this->userTitle_StartPos ;
        $GLOBALS['log'] .="<br/><span class='good'>Remaining Sentance:</span><br/>".$this->remainingSentance ;
    }
    
    public static function parse($givenSentance)
    {
        $signature = new Signature($givenSentance);
        $GLOBALS['log'] .="<br/><span class='percentage'>givenSentance to Parse Signature:</span><br/>".strip_tags($signature->givenSentance) ;
        echo $GLOBALS['log']; 
        $GLOBALS['log'] = "";
        flush();
        
        //Find the User ID in the next 3 if conditions
        $signature->parsedPassedNo = 0;
        $signature->userURLType = 0;
        //Normal <a href="/wiki/User:DavidLeighEllis" title="User:DavidLeighEllis">DavidLeighEllis</a>
        $condition_UserID1 = preg_match_all(Pattern_UserID1, $signature->givenSentance, $matches_UserID1, PREG_OFFSET_CAPTURE);
        //AFDID=5000 Endresult  <a href="/wiki/User_talk:Black_Kite" title="User talk:Black Kite">Black Kite (talk)</a>
        $condition_UserID2 = preg_match_all(Pattern_UserID2, $signature->givenSentance, $matches_UserID2, PREG_OFFSET_CAPTURE);
        //AFDID=5004 Endresult <a href="/w/index.php?title=User:Sampi&amp;action=edit&amp;redlink=1" class="new" title="User:Sampi (page does not exist)">sampi</a>
        $condition_UserID3 = preg_match_all(Pattern_UserID3, $signature->givenSentance, $matches_UserID3, PREG_OFFSET_CAPTURE);
        ////AFDID=2120 other <a href="/wiki/Special:Contributions/70.198.36.165" title="Special:Contributions/70.198.36.165">70.198.36.165</a>
        $condition_UserID4 = preg_match_all(Pattern_UserID4, $signature->givenSentance, $matches_UserID4, PREG_OFFSET_CAPTURE);
        if($condition_UserID1>0)
        {
            $condition_UserID_LastIndex = count($matches_UserID1[1])-1;
            $matches_UserID_lastOne = $matches_UserID1[1][$condition_UserID_LastIndex];
            $signature->userID_StartPos = $matches_UserID_lastOne[1];
            $signature->userID =  str_replace('"', '', rtrim(substr($matches_UserID_lastOne[0], 0, strlen($matches_UserID_lastOne[0])-2),"\""));
            $signature->userURL = "/wiki/User:$signature->userID";
            $signature->userURLType = 1;
            $signature->remainingSentance = substr($signature->givenSentance, $signature->userID_StartPos + strlen($signature->userID));
            $signature->parsedPassedNo++;
        }
        else if($condition_UserID2>0)
        {
            $condition_UserID_LastIndex = count($matches_UserID2[1])-1;
            $matches_UserID_lastOne = $matches_UserID2[1][$condition_UserID_LastIndex];
            $signature->userID_StartPos = $matches_UserID_lastOne[1];
            $signature->userID =  str_replace('"', '', rtrim(substr($matches_UserID_lastOne[0], 0, strlen($matches_UserID_lastOne[0])-2),"\""));
            $signature->remainingSentance = substr($signature->givenSentance, $signature->userID_StartPos + strlen($signature->userID));
            $signature->userURL = "/wiki/User_talk:$signature->userID";
            $signature->userURLType = 2;
            $signature->parsedPassedNo++;
        }
        else if($condition_UserID3>0)
        {
            $condition_UserID_LastIndex = count($matches_UserID3[1])-1;
            $matches_UserID_lastOne = $matches_UserID3[1][$condition_UserID_LastIndex];
            $signature->userID_StartPos = $matches_UserID_lastOne[1];
            $signature->userID =  str_replace('"', '', rtrim(substr($matches_UserID_lastOne[0], 0, strlen($matches_UserID_lastOne[0])),"\""));
            $signature->remainingSentance = substr($signature->givenSentance, $signature->userID_StartPos + strlen($signature->userID));
            $signature->userURL = "/w/index.php?title=User:$signature->userID&amp;action=edit";
            $signature->userURLType = 3;
            $signature->parsedPassedNo++;
        }
        //liligago check this working
        else if($condition_UserID4>0)
        {
            $condition_UserID_LastIndex = count($matches_UserID4[1])-1;
            $matches_UserID_lastOne = $matches_UserID4[1][$condition_UserID_LastIndex];
            $signature->userID_StartPos = $matches_UserID_lastOne[1];
            $signature->userID =  str_replace('"', '', rtrim(substr($matches_UserID_lastOne[0], 0, strlen($matches_UserID_lastOne[0])),"\""));
            $signature->remainingSentance = substr($signature->givenSentance, $signature->userID_StartPos + strlen($signature->userID));
            $signature->userURL = "/wiki/Special:Contributions/$signature->userID";
            $signature->userURLType = 4;
            $signature->parsedPassedNo++;
        }
        $signature->getInitialSentance();
        //$GLOBALS['log'] .="<br/><span class='percentage'>Remaining Sentance:</span><br/>".$signature->remainingSentance ;
        //$GLOBALS['log'] .="<br/><span class='good'>UserID:</span><br/>".$signature->userID."($signature->userID_StartPos)" ;
        //$GLOBALS['log'] .=" <a href='https://en.wikipedia.org".$signature->userURL."' target='_blank'>https://en.wikipedia.org$signature->userURL</a> - userURLType".$signature->userURLType ;
        
        if( $signature->parsedPassedNo == 1)
        {
            
            //title="User:Joe Decker"
            $condition_UserTitle = preg_match_all("/title=[\"|\']user:(.*?)[\"|\']/i", $signature->remainingSentance, $matches_UserTitle, PREG_OFFSET_CAPTURE);
            //User talk:Black Kite
            $condition_UserTitle2 = preg_match_all("/title=[\"|\']user talk:(.*?)[\"|\']/i", $signature->remainingSentance, $matches_UserTitle2, PREG_OFFSET_CAPTURE);
            
            if($condition_UserTitle>0)
            {
                $matches_UserTitle_lastOne = $matches_UserTitle[1][0];
                $signature->userTitle_StartPos = $matches_UserTitle_lastOne[1];
                $signature->userTitle = rtrim(substr($matches_UserTitle_lastOne[0], 0, strlen($matches_UserTitle_lastOne[0])),"\"");
                $signature->userTitle = rtrim(str_replace("(page does not exist)","",$signature->userTitle),"\"");
                //$signature->remainingSentance = substr($signature->remainingSentance, $signature->userTitle_StartPos + strlen($signature->userTitle));
                $signature->parsedPassedNo++;
            }
            else if($condition_UserTitle2>0)
            {
                $matches_UserTitle_lastOne = $matches_UserTitle2[1][0];
                $signature->userTitle_StartPos = $matches_UserTitle_lastOne[1];
                $signature->userTitle = rtrim(substr($matches_UserTitle_lastOne[0], 0, strlen($matches_UserTitle_lastOne[0])),"\"");
                //$signature->remainingSentance = substr($signature->remainingSentance, $signature->userTitle_StartPos + strlen($signature->userTitle));
                $signature->parsedPassedNo++;
            }
            else
            {
                $signature->userTitle = str_replace('"', '', trim($signature->userID));
                $signature->parsedPassedNo++;
            }
            
            //$GLOBALS['log'] .="<br/><span class='good'>UserTitle:</span><br/>".$signature->userTitle." ($signature->userTitle_StartPos)" ;
            //$GLOBALS['log'] .="<br/><span class='good'>Remaining Sentance:</span><br/>".$signature->remainingSentance ;
        
            if( $signature->parsedPassedNo == 2)
            {
                //title="18:23,"
                //$condition_time = preg_match_all("/[0-2][0-9]\:[0-6][0-9],/", $signature->remainingSentance, $matches_time, PREG_OFFSET_CAPTURE);
                $condition_time = preg_match_all("/([01]?[0-9]|2[0-3]):[0-5][0-9],/", $signature->remainingSentance, $matches_time, PREG_OFFSET_CAPTURE);
                
                if($condition_time>0)
                {
                    $matches_time_LastIndex = count($matches_time[0])-1;
                    $matches_time_lastOne = $matches_time[0][$matches_time_LastIndex];
                    $signature->time_StartPos = $matches_time_lastOne[1];
                    $signature->time = rtrim(substr($matches_time_lastOne[0], 0, strlen($matches_time_lastOne[0])),",");
                    //$signature->remainingSentance = substr($signature->remainingSentance, $signature->time_StartPos + strlen($signature->time));
                    $signature->parsedPassedNo++;
                }
                //
                if( $signature->parsedPassedNo == 3)
                {
                    $condition_date = preg_match_all("/,(.*?)(CET|UTC|GMT)/i", $signature->remainingSentance, $matches_date, PREG_OFFSET_CAPTURE);
                    if($condition_date>0)
                    {
                        $condition_date_LastIndex = count($matches_date[1])-1;
                        $matches_date_lastOne = $matches_date[1][$condition_date_LastIndex];
                        $signature->date_StartPos = $matches_date_lastOne[1];
                        $signature->date = rtrim(substr($matches_date_lastOne[0], 0, strlen($matches_date_lastOne[0])-2),"\"");
                        //$signature->remainingSentance = substr($signature->remainingSentance, $signature->date_StartPos + strlen($signature->date));
                        $signature->parsedPassedNo++;//4
                    }
                    //$GLOBALS['log'] .="<br/><span class='good'>Date:</span><br/>".$signature->date. " ($signature->date_StartPos)" ;
                }
            }
            $GLOBALS['log'] .="<table border='1'><tr><td><span class='good'>UserID:</span></td><td><span class='good'>UserTitle:</span></td><td><span class='good'>Time:</span></td><td><span class='good'>Date:</span></td><td><span class='good'>DateTime:</span></td></tr>";
            $GLOBALS['log'] .="<tr><td>".$signature->userID."($signature->userID_StartPos)</td>" ;
            $GLOBALS['log'] .="<td>".$signature->userTitle." ($signature->userTitle_StartPos)</td>" ;
            $GLOBALS['log'] .="<td>".$signature->time . " ($signature->time_StartPos)</td>";
            $GLOBALS['log'] .="<td>".$signature->date. " ($signature->date_StartPos)</td>" ;
            $GLOBALS['log'] .="<td>".date('Y-m-d H:i:s', strtotime($signature->date." ".$signature->time))."</td></tr/></table>" ;
                   
        }
        return $signature;
    }
    
    public function getInitialSentance() {
        $this->initialSentance = substr($this->givenSentance, 0, $this->userID_StartPos-1- strlen("<a href=\"/wiki/User"));
        $GLOBALS['log'] .="<br/><span class='good'> Initial Sentance:</span><br/> ".($this->initialSentance)."(".strlen($this->initialSentance)." = ".strlen($this->givenSentance)."- ".strlen($this->remainingSentance).") ";
    }
    
    public function getAfterUTC_Position() {
        $output = 0;
        $condition = preg_match_all("/\((CET|UTC|GMT)\)/i", $this->givenSentance, $matches, PREG_OFFSET_CAPTURE);
        if($condition > 0)         
        {
            $condition_LastIndex = count($matches[0])-1;
            $output = $matches[0][$condition_LastIndex][1]+ strlen("(UTC)");
        }
        return $output;
    }
}

?>