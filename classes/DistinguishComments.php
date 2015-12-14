<?php 

require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "Signature.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";

class DistinguishComments {
    public $givenHTML;
    public $give_URLDate;
    public $array_UserID1;
    public $condition_UserID1; //store in afd table
    public $array_UserID2;
    public $condition_UserID2; //store in afd table
    public $array_UserID3; 
    public $condition_UserID3; //store in afd table
    public $array_UserID4; 
    public $condition_UserID4; //store in afd table
    public $array_time;
    public $array_time_UserCheck; //store in afd table
    public $condition_time;
    public $array_UTC_UserCheck; //store in afd table
    public $array_UTC;
    public $condition_UTC; //store in afd table
    public $array_date;
    public $array_date_UserCheck;
    public $condition_date;
    public $NONMatched_Distinguish = 0; //debuging
    public $distinguishPercentage; //store in otherCommentTable
    public $distinguishPercentage_User;
    
    private $matches_UserID1;
    private $matches_UserID2;
    private $matches_UserID3;
    private $matches_UserID4;
    private $matches_time;
    private $matches_date;
    private $matches_UTC;
    private $start_DateTime;
    private $end_DateTime;
    public  $pattern_Date;
    
    //tree kinds of users
    static private $pattern_anyUser = "(href=[\"|\']\/wiki\/User|href=[\"|\']\/wiki\/User_talk|href=[\"|\']\/w\/index.php\?title\=User)";
    static private $pattern_anyTime = "([01]?[0-9]|2[0-3]):[0-5][0-9]( pm| am|pm|am| |)";
    static private $pattern_anyUCT = "\((CET|UTC|GMT)\)";
    
    public function DistinguishComments($givenHTML, $give_URLDate)
    {
        try{
            if (!$givenHTML)  
                throw new Exception("givenHTML=$givenHTML is empty to DistinguishComments:DistinguishComments(givenHTML)!");
                
            $this->givenHTML = $givenHTML;
            $this->give_URLDate = $give_URLDate;
            
            $parsedPassedNo = 0;
            
            //Normal <a href="/wiki/User:DavidLeighEllis" title="User:DavidLeighEllis">DavidLeighEllis</a>
            $this->condition_UserID1 = preg_match_all(Pattern_UserID1, $this->givenHTML, $this->matches_UserID1, PREG_OFFSET_CAPTURE);
            //AFDID=5000 Endresult  <a href="/wiki/User_talk:Black_Kite" title="User talk:Black Kite">Black Kite (talk)</a>
            $this->condition_UserID2 = preg_match_all(Pattern_UserID2, $this->givenHTML, $this->matches_UserID2, PREG_OFFSET_CAPTURE);
            //AFDID=5004 Endresult <a href="/w/index.php?title=User:Sampi&amp;action=edit&amp;redlink=1" class="new" title="User:Sampi (page does not exist)">sampi</a>
            $this->condition_UserID3 = preg_match_all(Pattern_UserID3, $this->givenHTML, $this->matches_UserID3, PREG_OFFSET_CAPTURE);
            //AFDID=2120 other <a href="/wiki/Special:Contributions/70.198.36.165" title="Special:Contributions/70.198.36.165">70.198.36.165</a>
            $this->condition_UserID4 = preg_match_all(Pattern_UserID4, $this->givenHTML, $this->matches_UserID4, PREG_OFFSET_CAPTURE);
            
            //Main section for detection
            $this->condition_time = preg_match_all("/(.*?)".DistinguishComments::$pattern_anyTime."(,| \d)"."/", $this->givenHTML, $this->matches_time, PREG_OFFSET_CAPTURE);
            $this->condition_UTC = preg_match_all("/(.*?)".DistinguishComments::$pattern_anyUCT."/i", $this->givenHTML, $this->matches_UTC, PREG_OFFSET_CAPTURE);
            
            //Most accurate one
            $this->calculatedStartEnd_Time();
            $this->condition_date = preg_match_all("/(.*?)".$this->pattern_Date."/i", $this->givenHTML, $this->matches_date, PREG_OFFSET_CAPTURE);
            
            //echo $GLOBALS['log'] ;
            //$GLOBALS['log'] =  "";
            //flush();
            
            $this->madeOutputArrays();
            $this->validateUser_Pereach_OutputArrays();
            //$this->displayOutputArrays();
            $this->calcualteDistinguishPercentage();
            
            if( $this->distinguishPercentage < PassingPercentage)
            {
                $this->displayOutputArrays();
                $this->NONMatched_Distinguish = 1;
            }
        }
        catch (Exception $e) {
            echo '<br/><span class="bad"> Caught exception: ',  $e->getMessage(), "</span>\n";
        }
    }
    
    private function calculatedStartEnd_Time()
    {
        if(!empty($this->give_URLDate))
        {
            //$GLOBALS['log'] .=  "<br/>give_URLDate=".$this->give_URLDate;
            $pureDate_str = str_replace("https://en.wikipedia.org/wiki/Wikipedia:Articles_for_deletion/Log/","",$this->give_URLDate);
            $pureDate_str = str_replace("_","-",trim($pureDate_str));
            if(strtotime($pureDate_str." 12:01:01"))
                $pureDate_ToTime = date('d F Y',strtotime($pureDate_str." 01:01:01"));
            else
                throw new Exception("cannot convert $this->give_URLDate to correct format!");
            
            $this->start_DateTime =  date('d F Y', strtotime($pureDate_str." 01:01:01"." -2 day"));
            $this->end_DateTime = date('d F Y', strtotime($pureDate_str." 01:01:01"." +25 day"));
            $start_year = date('Y', strtotime($pureDate_str." 01:01:01"." -2 day"));
            $start_month = date('F', strtotime($pureDate_str." 01:01:01"." -2 day"));
            $end_year = date('Y', strtotime($pureDate_str." 01:01:01"." +25 day"));
            $end_month = date('F', strtotime($pureDate_str." 01:01:01"." +25 day"));
            
            if($start_year != $end_year)
                $pattern_year = "($start_year|$end_year)";
            else
                $pattern_year = "$start_year";
                
            if($start_month != $end_month)
                $pattern_month = "($start_month|$end_month)";
            else
                $pattern_month = "$start_month";
        }
        else
        {
            $this->start_DateTime = date('d F Y',strtotime("2011-January-01  01:01:01"));
            $this->end_DateTime = date('d F Y',strtotime("2016-January-01  01:01:01"));
            $pattern_year = "(2011|2012|2013|2014|2015|2016)";
            $pattern_month = "(January|February|March|April|May|June|July|August|September|October|November|December)";
        }
        $this->pattern_Date = "([0-3][0-9]|[0-9])(| )".$pattern_month." ".$pattern_year;
        //$GLOBALS['log'] .=  "<br/>(".$this->start_DateTime ."   >>>>>  ".$this->end_DateTime. " ) <br/> pattern_Date=".$this->pattern_Date. "<br/>pattern_Time=". DistinguishComments::$pattern_anyTime."(,| \d)"." <br/>pattern_UTC=".DistinguishComments::$pattern_anyUCT ;
    }
    
    //  ----------------------------------------------- 100 - 1000 --- This is based on the test
    // $this->condition_date !=  $this->condition_time = 7% - 2.29%
    // $this->condition_UTC !=  $this->condition_time =  2% - 1.7%
    // $this->condition_UTC !=  $this->condition_date =  7% - 4.0%
    private function madeOutputArrays() 
    {
        $temp_matches_date = $this->matches_date[0];
        
        $this->array_time = array();
        $this->array_date = array();
        $this->array_UTC = array();
        
        //Create Time Array
        $this->array_time[0] = $this->matches_time[0][0][0];
        //add 8 May 2014 (UTC) to each end distinguish of time array 
        for ($index_time =1; $index_time< count($this->matches_time[0]); $index_time++) {
            $current = $this->matches_time[0][$index_time][0];
            $previous = $this->array_time[$index_time-1];
            $temp =  substr( $current, 0, 50 );
            $condition_UTCXtime = preg_match_all("/(.*?)".DistinguishComments::$pattern_anyUCT."/i", $temp, $matches_UTCXtime, PREG_OFFSET_CAPTURE);
            if($condition_UTCXtime>0)
            {
                $firstMatched_UTCXtime = $matches_UTCXtime[0][0];
                $temp =  $firstMatched_UTCXtime[0];
                $current = str_replace($temp,"",$current);
            
                $this->array_time[$index_time-1] .= $temp;
                //$this->array_time[$index_time-1] .= "-".strlen($this->array_time[$index_time-1]);
                $this->array_time[$index_time] = $current;
            }
        }
        //fix tha last position in array
        $last_EndPos_time = stripos($this->givenHTML, $this->array_time[count($this->array_time)-1]);
        $this->array_time[count($this->matches_time[0])-1] = substr($this->givenHTML, $last_EndPos_time);
        
        //Create Date Array
        $this->array_date[0] = $this->matches_date[0][0][0];
        for ($index_date =1; $index_date< count($this->matches_date[0]); $index_date++) {
            $current = $this->matches_date[0][$index_date][0];
            $previous = $this->array_date[$index_date-1];
            $temp =  substr( $current, 0, 35 );
            
            $condition_UTCXdate = preg_match_all("/(.*?)".DistinguishComments::$pattern_anyUCT."/i", $temp, $matches_UTCXdate, PREG_OFFSET_CAPTURE);
            if($condition_UTCXdate>0)
            {
                $firstMatched_UTCXdate = $matches_UTCXdate[0][0];
                
                $temp =  $firstMatched_UTCXdate[0];
                $current = str_replace($temp,"",$current);
            
                $this->array_date[$index_date-1] .= $temp;
                //$this->array_date[$index_date-1] .= "-".strlen($this->array_date[$index_date-1]);
                $this->array_date[$index_date] = $current;
            }
            else
            {
                $this->array_date[$index_date] = $current;
            }
        }
        //fix tha last position in array
        $last_EndPos_date = stripos($this->givenHTML, $this->array_date[count($this->array_date)-1]);
        //echo substr($this->givenHTML, 0, $last_EndPos_date)."<span class='newFunction'>$last_EndPos_date -".count($this->array_date)."</span>".substr($this->givenHTML, $last_EndPos_date);
        $this->array_date[count($this->matches_date[0])-1] = substr($this->givenHTML, $last_EndPos_date);
        
        //Create UTC Array
        for ($index_UTC =0; $index_UTC < count($this->matches_UTC[0]); $index_UTC++) {
            $this->array_UTC[$index_UTC] = $this->matches_UTC[0][$index_UTC][0];
            //$this->array_UTC[$index_UTC] .= "-".strlen($this->matches_UTC[0][$index_UTC][0]);
        }
        
    }
    
    public function displayOutputArrays()
    {
        $GLOBALS['log'] .=  "<div class='percentage'> otherComment_Html(condition_UserID1=$this->condition_UserID1, condition_UserID2=$this->condition_UserID2, condition_UserID3=$this->condition_UserID3)</div>";
        $GLOBALS['log'] .=  "<div class='percentage'> condition_time=$this->condition_time, condition_date=$this->condition_date, condition_UTC=$this->condition_UTC:</div>";
        $GLOBALS['log'] .=  "<span class='newFunction'>$this->distinguishPercentage</span>";
        //$GLOBALS['log'] .=  $this->givenHTML;
        
        $GLOBALS['log'] .=  "<hr style='border: 0; border-top: 1px solid #FF3339;'/>";
        //Display
        $GLOBALS['log'] .= "<table border='1' width='100%'>";
        $GLOBALS['log'] .= "<tr><td width='6%'>Index</td>";
        $GLOBALS['log'] .= "<td width='30%'>Time</td>";
        $GLOBALS['log'] .= "<td width='3%'>U</td>";
        $GLOBALS['log'] .= "<td width='30%'>Date</td>";
        $GLOBALS['log'] .= "<td width='3%'>U</td>";
        $GLOBALS['log'] .= "<td width='30%'>UTC</td>";
        $GLOBALS['log'] .= "<td width='3%'>U</td>";
        $GLOBALS['log'] .= "</tr>";
        for($i=0; $i < count($this->array_time) ; $i++)
        {
            $GLOBALS['log'] .= "<tr><td>$i-</td>";
            $GLOBALS['log'] .= "<td>".$this->array_time[$i]."</td>";
            if(!$this->array_time_UserCheck[$i])
                $GLOBALS['log'] .= "<td><span class='errorCheck'>&nbsp</span></td>";
            else
                $GLOBALS['log'] .= "<td>&nbsp</td>";
            if($i < count($this->array_date))
            {
                $GLOBALS['log'] .= "<td>".$this->array_date[$i]."</td>";
                if(!$this->array_date_UserCheck[$i])
                    $GLOBALS['log'] .= "<td><span class='errorCheck'>&nbsp</span></td>";
                else
                    $GLOBALS['log'] .= "<td>&nbsp</td>";
            }
            else
                $GLOBALS['log'] .= "<td>&nbsp</td><td>&nbsp</td>";
            
            if($i < count($this->array_UTC))
            {
                $GLOBALS['log'] .= "<td>".$this->array_UTC[$i]."</td>";
                if(!$this->array_UTC_UserCheck[$i])
                    $GLOBALS['log'] .= "<td><span class='errorCheck'>&nbsp</span></td>";
                else
                    $GLOBALS['log'] .= "<td>&nbsp</td>";
            }
            else
                $GLOBALS['log'] .= "<td>&nbsp</td><td>&nbsp</td>";
            $GLOBALS['log'] .= "</tr>";
        }
        $GLOBALS['log'] .= "</tr>";
        $GLOBALS['log'] .= "</table>";
    }
    
    private function validateUser_Pereach_OutputArrays()
    {
        $this->array_time_UserCheck = array();
        $this->array_UTC_UserCheck = array();
        $this->array_date_UserCheck = array();
        
        for($i=0; $i < count($this->array_time) ; $i++)
            if ( preg_match(Pattern_UserID1,$this->array_time[$i]) )
                $this->array_time_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID2,$this->array_time[$i]) )
                $this->array_time_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID3,$this->array_time[$i]) )
                $this->array_time_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID4,$this->array_time[$i]) )
                $this->array_time_UserCheck[$i]= True;
            else
                $this->array_time_UserCheck[$i]= False;
       
       for($i=0; $i < count($this->array_date) ; $i++)
            if ( preg_match(Pattern_UserID1,$this->array_date[$i]) )
                $this->array_date_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID2,$this->array_date[$i]) )
                $this->array_date_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID3,$this->array_date[$i]) )
                $this->array_date_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID4,$this->array_date[$i]) )
                $this->array_date_UserCheck[$i]= True;
            else
                $this->array_date_UserCheck[$i]= False;
       
       for($i=0; $i < count($this->array_UTC) ; $i++)
            if ( preg_match(Pattern_UserID1,$this->array_UTC[$i]) )
                $this->array_UTC_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID2,$this->array_UTC[$i]) )
                $this->array_UTC_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID3,$this->array_UTC[$i]) )
                $this->array_UTC_UserCheck[$i]= True;
            else if( preg_match(Pattern_UserID4,$this->array_date[$i]) )
                $this->array_UTC_UserCheck[$i]= True;
            else
                $this->array_UTC_UserCheck[$i]= False;
    }
    
    // if there is time, date, and UTC then 100%
    // if there is time, and UTC then 95%
    // if there is time, and date then 90%
    // if there is time, and user1 then 70%
    // if there is time, and user2 then 60%
    // if there is time then 50%
    //for each user that has not been detected in the each comment decrease distinguishPercentage by 20% such as afdID= 167
    private function calcualteDistinguishPercentage()
    {
        $this->distinguishPercentage = 0;
        if($this->condition_time == $this->condition_date && $this->condition_time == $this->condition_UTC)
            $this->distinguishPercentage = 100;
        else if($this->condition_time == $this->condition_UTC)
            $this->distinguishPercentage = 95;
        else if($this->condition_time == $this->condition_date)
            $this->distinguishPercentage = 90;
        else if($this->condition_time == $this->condition_UserID1)
            $this->distinguishPercentage = 70;
        else if($this->condition_time == $this->condition_UserID2)
            $this->distinguishPercentage = 60;
        else 
            $this->distinguishPercentage = 50;
        
        //for each user that has not been detected in the each comment decrease distinguishPercentage by 20%
        $decreasePercentage = 0;
        $this->distinguishPercentage_User = 0;
        for($i=0; $i < count($this->array_time_UserCheck) ; $i++)
            if (!$this->array_time_UserCheck[$i])
                $decreasePercentage += 20;
        if($decreasePercentage>0)
        {
            $this->distinguishPercentage -= $decreasePercentage;
            $this->distinguishPercentage_User = $decreasePercentage;
        }
        // In case of a lot of error in detecting user
        if($this->distinguishPercentage<0)
            $this->distinguishPercentage = 0;
            
    }
}
?>