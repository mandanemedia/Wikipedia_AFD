<?php 
require_once "config.php";
require_once "functions.php";

/* 
ALTER TABLE afd AUTO_INCREMENT = 1

*/

class AFD {
    public $AFDID;
    public $debateDateListID;
    public $AFDTitle;
    public $AFDTitleID;
    
    public $AFDURL;
    public $AFDURL_2;
    public $AFDHTML;
    public $articleURL;
    public $articleID;
    public $flag_AFDURL_Working;
    public $flag_articleURL_Working;
    public $flag_deletedArticle;
    public $flag_error;
    public $flag_toBeRemoved;
    public $flag_DoNotParse;
    public $flag_DoNotVisualize;
    public $flag_completeAFDParse;
    public $flag_otherComment_empty;
    
    public $endResult;
    public $endResult_User;
    public $endResult_UserPosition;
    public $endResult_UserTitle;
    public $endResult_UserURL;
    public $endResult_UserURLType;
    public $endResult_Date;
    public $endResult_Time;
    public $endResult_DateTime;
    public $endResult_Note;
    public $endResult_Html;
    public $endResult_ExtraNote;
    public $endResult_type;
    
    public $mainComment;
    public $mainComment_User;
    public $mainComment_UserPosition;
    public $mainComment_UserTitle;
    public $mainComment_UserURL;
    public $mainComment_UserURLType;
    public $mainComment_Date;
    public $mainComment_Time;
    public $mainComment_DateTime;
    public $mainComment_Note;
    public $mainComment_Html;
    public $mainComment_ExtraNote; // This one is added by the ParseAFD, as sometime there are some  sentance after signiture. 
    public $mainComment_ExtraNote2; // This one is added by the ParseAFD_MainComment, as sometime there are some  sentance after signiture such as AFDID=2. 
    public $mainComment_Type;
    
    public $plainlinks_Html;
    public $otherComment_Html;
    
    public $parse_endResult_s;
    public $parse_endResult_e;
    public $parse_endResult_details;
    public $parse_mainComment;
    public $parse_otherComment;
    public $parse_otherComment_User;
    
    public $otherComment_CounterTime;
    public $otherComment_CounterDate;
    public $otherComment_CounterUTC;
    public $otherComment_CounterUserNormal;
    public $otherComment_CounterUserTalk;
    public $otherComment_CounterUserNew;
    public $otherComment_CounterUserIP;
    
    private $conn;
    public function AFD($AFDTitle, $debateDateListID,  $conn) {
        $this->AFDTitle = trim($AFDTitle);
        $this->debateDateListID = $debateDateListID;
        
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->AFD() </span>";
        //$GLOBALS['log'] .= "<br/>this->AFDTitle=$this->AFDTitle , this->debateDateListID=$this->debateDateListID";
        //echo $GLOBALS['log'];
        //$GLOBALS['log'] = "";
        
        if( !empty($this->AFDTitle) && !empty($this->debateDateListID) )
        {
            $this->conn = $conn;
            if(!( $this->checkExistingTitle($this->AFDTitle, $this->debateDateListID ,$conn) != -1))
                $this->insertAFD();
            //else // To make it fast, it is loaded in the checkExistingTitle and this else is disable
            //    $this->loadFromDB();
        }
    }
    
    function __destruct() {
        //if(is_resource($this->conn))
//            mysqli_close($this->conn);
    }
    
    //could be optimize by passing another boolean argument and limit the variable
    private function checkExistingTitle($AFDTitle, $debateDateListID,$givenConn)
    {
        $output = -1;
        try{
            $conn = "";
            $AFDTitle = trim($AFDTitle);
            
            if (!$AFDTitle)  
                throw new Exception('AFDTitle is empty!');
            if (!$debateDateListID)  
                throw new Exception('debateDateListID is empty!');
            
            
            if($givenConn)
                $conn = $givenConn;
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
                
            $sql = "SELECT *
                    FROM AFD 
                    where AFDTitle = '". mysql_real_escape_string($AFDTitle)."' 
                    and debateDateListID = '". mysql_real_escape_string($debateDateListID)."' ";
                
            if ($result=mysqli_query($conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    $output= $obj->AFDID;
                    
                    $this->AFDID = $obj->AFDID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    
                    $this->AFDURL = $obj->AFDURL;
                    $this->AFDURL_2 = $obj->AFDURL_2;
                    $this->AFDHTML = $obj->AFDHTML;
                    $this->articleURL = $obj->articleURL;
                    $this->articleID = $obj->articleID;
                    $this->flag_AFDURL_Working = $obj->flag_AFDURL_Working;
                    $this->flag_articleURL_Working = $obj->flag_articleURL_Working;
                    $this->flag_deletedArticle = $obj->flag_deletedArticle;
                    $this->flag_error = $obj->flag_error;
                    $this->flag_toBeRemoved = $obj->flag_toBeRemoved;
                    $this->flag_DoNotParse = $obj->flag_DoNotParse;
                    $this->flag_DoNotVisualize = $obj->flag_DoNotVisualize;
                    $this->flag_completeAFDParse = $obj->flag_completeAFDParse;
                    $this->flag_otherComment_empty = $obj->flag_otherComment_empty;
                    
                    $this->endResult = $obj->endResult;
                    $this->endResult_User = $obj->endResult_User;
                    $this->endResult_UserPosition = $obj->endResult_UserPosition;
                    $this->endResult_UserTitle = $obj->endResult_UserTitle;
                    $this->endResult_UserURL = $obj->endResult_UserURL;
                    $this->endResult_UserURLType = $obj->endResult_UserURLType;
                    $this->endResult_Date = $obj->endResult_Date;
                    $this->endResult_Time = $obj->endResult_Time;
                    $this->endResult_DateTime = $obj->endResult_DateTime;
                    $this->endResult_Note = $obj->endResult_Note;
                    $this->endResult_Html = $obj->endResult_Html;
                    $this->endResult_ExtraNote = $obj->endResult_ExtraNote;
                    $this->endResult_Type = $obj->endResult_Type;
                    
                    $this->mainComment = $obj->mainComment;
                    $this->mainComment_User = $obj->mainComment_User;
                    $this->mainComment_UserPosition = $obj->mainComment_UserPosition;
                    $this->mainComment_UserTitle = $obj->mainComment_UserTitle;
                    $this->mainComment_UserURL = $obj->mainComment_UserURL;
                    $this->mainComment_UserURLType = $obj->mainComment_UserURLType;
                    $this->mainComment_Date = $obj->mainComment_Date;
                    $this->mainComment_Time = $obj->mainComment_Time;
                    $this->mainComment_DateTime = $obj->mainComment_DateTime;
                    $this->mainComment_Note = $obj->mainComment_Note;
                    $this->mainComment_Html = $obj->mainComment_Html;
                    $this->mainComment_ExtraNote = $obj->mainComment_ExtraNote;
                    $this->mainComment_ExtraNote2 = $obj->mainComment_ExtraNote2;
                    $this->mainComment_Type = $obj->mainComment_Type;
                    
                    $this->plainlinks_Html = $obj->plainlinks_Html;
                    $this->otherComment_Html = $obj->otherComment_Html;
                    
                    $this->parse_endResult_s = $obj->parse_endResult_s;
                    $this->parse_endResult_e = $obj->parse_endResult_e;
                    $this->parse_endResult_details = $obj->parse_endResult_details;
                    $this->parse_mainComment = $obj->parse_mainComment;
                    $this->parse_otherComment = $obj->parse_otherComment;
                    $this->parse_otherComment_User = $obj->parse_otherComment_User;
                    
                    $this->otherComment_CounterTime = $obj->otherComment_CounterTime;
                    $this->otherComment_CounterDate = $obj->otherComment_CounterDate;
                    $this->otherComment_CounterUTC = $obj->otherComment_CounterUTC;
                    $this->otherComment_CounterUserNormal = $obj->otherComment_CounterUserNormal;
                    $this->otherComment_CounterUserTalk = $obj->otherComment_CounterUserTalk;
                    $this->otherComment_CounterUserNew = $obj->otherComment_CounterUserNew;
                    $this->otherComment_CounterUserIP = $obj->otherComment_CounterUserIP;
                }
                // Free result set
                mysqli_free_result($result);
                closeDBConnection($conn,$conn_NeedToClose);
            }
            else
                throw new Exception('Error on mysqli_query!');
            //if(!$givenConn) 
//                mysqli_close($conn);
        }
        catch (Exception $e) {
            echo 'Caught exception : ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    function is_toBeRemoved()
    {
        if($this->toBeRemoved == 1)
            return true;
        else
            return false;
    }
    
    static function loadFromDBByAFDID($AFDID , $conn="")
    {
        $afd = -1; 
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call AFD::loadFromDBByAFDID() </span>";
        try{
            $AFDID = trim($AFDID);
            
            if (!$AFDID)  
                throw new Exception('AFDID is empty!');
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $sql = "SELECT AFDTitle, debateDateListID 
                    FROM AFD 
                    where AFDID = '". mysql_real_escape_string($AFDID)."'";
                         
            if ($result=mysqli_query($conn,$sql))
            {
                if ($obj=mysqli_fetch_object($result))
                {
                    $afd = new AFD($obj->AFDTitle, $obj->debateDateListID,  $conn);
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
                
            closeDBConnection($conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $afd;
    }
    
    static function load_DBObject_ByAFDID($AFDID)
    {
        $afd = -1; 
        
        try{
            $AFDID = trim($AFDID);
            
            if (!$AFDID)  
                throw new Exception('AFDID is empty!');
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $sql = "SELECT *
                    FROM AFD 
                    where AFDID = '". mysql_real_escape_string($AFDID)."'";
                         
            if ($result=mysqli_query($conn,$sql))
            {
                if ($obj = mysqli_fetch_object($result))
                {
                    $afd = $obj;
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
                
            closeDBConnection($conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $afd;
    }
    
    
    function loadFromDB()
    {
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->loadFromDB() </span>";
        try{
            $this->AFDTitle = trim($this->AFDTitle);
            
            if (!$this->AFDTitle)  
                throw new Exception('AFDTitle is empty!');
            
            $sql = "SELECT * 
                    FROM AFD 
                    where AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
                    and debateDateListID = '". mysql_real_escape_string($this->debateDateListID)."'";
                
            if ($result=mysqli_query($this->conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    $this->AFDID = $obj->AFDID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    $this->AFDURL = $obj->AFDURL;
                    $this->AFDURL_2 = $obj->AFDURL_2;
                    $this->AFDHTML = $obj->AFDHTML;
                    $this->articleURL = $obj->articleURL;
                    $this->articleID = $obj->articleID;
                    $this->flag_AFDURL_Working = $obj->flag_AFDURL_Working;
                    $this->flag_articleURL_Working = $obj->flag_articleURL_Working;
                    $this->flag_deletedArticle = $obj->flag_deletedArticle;
                    $this->flag_error = $obj->flag_error;
                    $this->flag_toBeRemoved = $obj->flag_toBeRemoved;
                    $this->flag_DoNotParse = $obj->flag_DoNotParse;
                    $this->flag_DoNotVisualize = $obj->flag_DoNotVisualize;
                    $this->flag_completeAFDParse = $obj->flag_completeAFDParse;
                    $this->flag_otherComment_empty = $obj->flag_otherComment_empty;
                    
                    $this->endResult = $obj->endResult;
                    $this->endResult_User = $obj->endResult_User;
                    $this->endResult_UserPosition = $obj->endResult_UserPosition;
                    $this->endResult_UserTitle = $obj->endResult_UserTitle;
                    $this->endResult_UserURL = $obj->endResult_UserURL;
                    $this->endResult_UserURLType = $obj->endResult_UserURLType;
                    $this->endResult_Date = $obj->endResult_Date;
                    $this->endResult_Time = $obj->endResult_Time;
                    $this->endResult_DateTime = $obj->endResult_DateTime;
                    $this->endResult_Note = $obj->endResult_Note;
                    $this->endResult_Html = $obj->endResult_Html;
                    $this->endResult_ExtraNote = $obj->endResult_ExtraNote;
                    $this->endResult_Type = $obj->endResult_Type;
                    
                    $this->mainComment = $obj->mainComment;
                    $this->mainComment_User = $obj->mainComment_User;
                    $this->mainComment_UserPosition = $obj->mainComment_UserPosition;
                    $this->mainComment_UserTitle = $obj->mainComment_UserTitle;
                    $this->mainComment_UserURL = $obj->mainComment_UserURL;
                    $this->mainComment_UserURLType = $obj->mainComment_UserURLType;
                    $this->mainComment_Date = $obj->mainComment_Date;
                    $this->mainComment_Time = $obj->mainComment_Time;
                    $this->mainComment_DateTime = $obj->mainComment_DateTime;
                    $this->mainComment_Note = $obj->mainComment_Note;
                    $this->mainComment_Html = $obj->mainComment_Html;
                    $this->mainComment_ExtraNote = $obj->mainComment_ExtraNote;
                    $this->mainComment_ExtraNote2 = $obj->mainComment_ExtraNote2;
                    
                    $this->plainlinks_Html = $obj->plainlinks_Html;
                    $this->otherComment_Html = $obj->otherComment_Html;
                    
                    $this->parse_endResult_s = $obj->parse_endResult_s;
                    $this->parse_endResult_e = $obj->parse_endResult_e;
                    $this->parse_endResult_details = $obj->parse_endResult_details;
                    $this->parse_mainComment = $obj->parse_mainComment;
                    $this->parse_otherComment = $obj->parse_otherComment;
                    $this->parse_otherComment_User = $obj->parse_otherComment_User;
                    
                    $this->otherComment_CounterTime = $obj->otherComment_CounterTime;
                    $this->otherComment_CounterDate = $obj->otherComment_CounterDate;
                    $this->otherComment_CounterUTC = $obj->otherComment_CounterUTC;
                    $this->otherComment_CounterUserNormal = $obj->otherComment_CounterUserNormal;
                    $this->otherComment_CounterUserTalk = $obj->otherComment_CounterUserTalk;
                    $this->otherComment_CounterUserNew = $obj->otherComment_CounterUserNew;
                    $this->otherComment_CounterUserIP = $obj->otherComment_CounterUserIP;
                    
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    function load_EndResult_FromDB_ByAFDID($givenID)
    {
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->loadFromDB() </span>";
        try{
            $givenID = trim($givenID);
            
            if (!$givenID)  
                throw new Exception('givenID is empty!');
            
            $sql = "SELECT AFDID, debateDateListID, AFDTitle, AFDTitleID, AFDURL, articleURL, articleID, flag_AFDURL_Working, flag_articleURL_Working, flag_deletedArticle, flag_error, flag_toBeRemoved, flag_DoNotParse, flag_DoNotVisualize, flag_completeAFDParse, flag_otherComment_empty, endResult, endResult_User, endResult_UserPosition, endResult_UserTitle, endResult_UserURL, endResult_UserURLType, endResult_Date, endResult_Time, endResult_DateTime, endResult_Note, endResult_Html, endResult_ExtraNote, endResult_Type, parse_endResult_s, parse_endResult_e, parse_endResult_details
                    FROM AFD 
                    where AFDID = '". mysql_real_escape_string($givenID)."'";
            
            //$GLOBALS['log'] .= "<hr/> $sql <hr/>";
            
            $conn_NeedToClose = false;
            openDBConnection($this->conn, $conn_NeedToClose);
               
            if ($result=mysqli_query($this->conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    $this->AFDID = $obj->AFDID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    $this->AFDURL = $obj->AFDURL;
                    $this->articleURL = $obj->articleURL;
                    
                    $this->articleID = $obj->articleID;
                    $this->flag_AFDURL_Working = $obj->flag_AFDURL_Working;
                    $this->flag_articleURL_Working = $obj->flag_articleURL_Working;
                    $this->flag_deletedArticle = $obj->flag_deletedArticle;
                    $this->flag_error = $obj->flag_error;
                    $this->flag_toBeRemoved = $obj->flag_toBeRemoved;
                    $this->flag_DoNotParse = $obj->flag_DoNotParse;
                    $this->flag_DoNotVisualize = $obj->flag_DoNotVisualize;
                    $this->flag_completeAFDParse = $obj->flag_completeAFDParse;
                    $this->flag_otherComment_empty = $obj->flag_otherComment_empty;
                    
                    $this->endResult = $obj->endResult;
                    $this->endResult_User = $obj->endResult_User;
                    $this->endResult_UserPosition = $obj->endResult_UserPosition;
                    $this->endResult_UserTitle = $obj->endResult_UserTitle;
                    $this->endResult_UserURL = $obj->endResult_UserURL;
                    $this->endResult_UserURLType = $obj->endResult_UserURLType;
                    $this->endResult_Date = $obj->endResult_Date;
                    $this->endResult_Time = $obj->endResult_Time;
                    $this->endResult_DateTime = $obj->endResult_DateTime;
                    $this->endResult_Note = $obj->endResult_Note;
                    $this->endResult_Html = $obj->endResult_Html;
                    $this->endResult_ExtraNote = $obj->endResult_ExtraNote;
                    $this->endResult_Type = $obj->endResult_Type;
                    
                    $this->parse_endResult_s = $obj->parse_endResult_s;
                    $this->parse_endResult_e = $obj->parse_endResult_e;
                    $this->parse_endResult_details = $obj->parse_endResult_details;
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
            
            closeDBConnection($this->conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    function load_MainComment_FromDB_ByAFDID($givenID)
    {
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->load_MainComment_FromDB_ByAFDID() </span>";
        try{
            $givenID = trim($givenID);
            
            if (!$givenID)  
                throw new Exception('givenID is empty!');
            
            $sql = "SELECT AFDID, debateDateListID, AFDTitle, AFDTitleID, AFDURL, articleURL, articleID, flag_AFDURL_Working, flag_articleURL_Working, flag_deletedArticle, flag_error, flag_toBeRemoved, flag_DoNotParse, flag_DoNotVisualize, flag_completeAFDParse, flag_otherComment_empty, mainComment, mainComment_User, mainComment_UserPosition, mainComment_UserTitle, mainComment_UserURL, mainComment_UserURLType, mainComment_Date, mainComment_Time, mainComment_DateTime, mainComment_Note, mainComment_Html, mainComment_ExtraNote, mainComment_ExtraNote2, mainComment_Type, parse_endResult_s, parse_endResult_e, parse_endResult_details, parse_mainComment
                    FROM AFD 
                    where AFDID = '". mysql_real_escape_string($givenID)."'";
            
            //$GLOBALS['log'] .= "<hr/> $sql <hr/>";
            
            $conn_NeedToClose = false;
            openDBConnection($this->conn, $conn_NeedToClose);
                
            if ($result=mysqli_query($this->conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    $this->AFDID = $obj->AFDID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    $this->AFDURL = $obj->AFDURL;
                    $this->articleURL = $obj->articleURL;
                    
                    $this->articleID = $obj->articleID;
                    $this->flag_AFDURL_Working = $obj->flag_AFDURL_Working;
                    $this->flag_articleURL_Working = $obj->flag_articleURL_Working;
                    $this->flag_deletedArticle = $obj->flag_deletedArticle;
                    $this->flag_error = $obj->flag_error;
                    $this->flag_toBeRemoved = $obj->flag_toBeRemoved;
                    $this->flag_DoNotParse = $obj->flag_DoNotParse;
                    $this->flag_DoNotVisualize = $obj->flag_DoNotVisualize;
                    $this->flag_completeAFDParse = $obj->flag_completeAFDParse;
                    $this->flag_otherComment_empty = $obj->flag_otherComment_empty;
                    
                    $this->mainComment = $obj->mainComment;
                    $this->mainComment_User = $obj->mainComment_User;
                    $this->mainComment_UserPosition = $obj->mainComment_UserPosition;
                    $this->mainComment_UserTitle = $obj->mainComment_UserTitle;
                    $this->mainComment_UserURL = $obj->mainComment_UserURL;
                    $this->mainComment_UserURLType = $obj->mainComment_UserURLType;
                    $this->mainComment_Date = $obj->mainComment_Date;
                    $this->mainComment_Time = $obj->mainComment_Time;
                    $this->mainComment_DateTime = $obj->mainComment_DateTime;
                    $this->mainComment_Note = $obj->mainComment_Note;
                    $this->mainComment_Html = $obj->mainComment_Html;
                    $this->mainComment_ExtraNote = $obj->mainComment_ExtraNote;
                    $this->mainComment_ExtraNote2 = $obj->mainComment_ExtraNote2;
                    $this->mainComment_Type = $obj->mainComment_Type;
                    
                    $this->parse_endResult_s = $obj->parse_endResult_s;
                    $this->parse_endResult_e = $obj->parse_endResult_e;
                    $this->parse_endResult_details = $obj->parse_endResult_details;
                    $this->parse_mainComment = $obj->parse_mainComment;
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
            
            closeDBConnection($this->conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    function load_OtherComment_FromDB_ByAFDID($givenID)
    {
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->load_MainComment_FromDB_ByAFDID() </span>";
        try{
            $givenID = trim($givenID);
            
            if (!$givenID)  
                throw new Exception('givenID is empty!');
           
            $sql = "SELECT AFDID, debateDateListID, AFDTitle, AFDTitleID, AFDURL, articleURL, articleID, flag_AFDURL_Working, flag_articleURL_Working, flag_deletedArticle, flag_error, flag_toBeRemoved, flag_DoNotParse, flag_DoNotVisualize, flag_completeAFDParse, flag_otherComment_empty, otherComment_Html, parse_otherComment, parse_otherComment_User, otherComment_CounterTime, otherComment_CounterDate, otherComment_CounterUTC, otherComment_CounterUserNormal, otherComment_CounterUserTalk, otherComment_CounterUserNew, otherComment_CounterUserIP
                    FROM AFD 
                    where AFDID = '". mysql_real_escape_string($givenID)."'";
            
            //$GLOBALS['log'] .= "<hr/> $sql <hr/>";
            
            $conn_NeedToClose = false;
            openDBConnection($this->conn, $conn_NeedToClose);
                
            if ($result=mysqli_query($this->conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    $this->AFDID = $obj->AFDID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    $this->AFDURL = $obj->AFDURL;
                    $this->articleURL = $obj->articleURL;
                    
                    $this->articleID = $obj->articleID;
                    $this->flag_AFDURL_Working = $obj->flag_AFDURL_Working;
                    $this->flag_articleURL_Working = $obj->flag_articleURL_Working;
                    $this->flag_deletedArticle = $obj->flag_deletedArticle;
                    $this->flag_error = $obj->flag_error;
                    $this->flag_toBeRemoved = $obj->flag_toBeRemoved;
                    $this->flag_DoNotParse = $obj->flag_DoNotParse;
                    $this->flag_DoNotVisualize = $obj->flag_DoNotVisualize;
                    $this->flag_completeAFDParse = $obj->flag_completeAFDParse;
                    $this->flag_otherComment_empty = $obj->flag_otherComment_empty;
                    
                    $this->otherComment_Html = $obj->otherComment_Html;
                
                    $this->parse_otherComment = $obj->parse_otherComment;
                    $this->parse_otherComment_User = $obj->parse_otherComment_User;
                    
                    $this->otherComment_CounterTime = $obj->otherComment_CounterTime;
                    $this->otherComment_CounterDate = $obj->otherComment_CounterDate;
                    $this->otherComment_CounterUTC = $obj->otherComment_CounterUTC;
                    $this->otherComment_CounterUserNormal = $obj->otherComment_CounterUserNormal;
                    $this->otherComment_CounterUserTalk = $obj->otherComment_CounterUserTalk;
                    $this->otherComment_CounterUserNew = $obj->otherComment_CounterUserNew;
                    $this->otherComment_CounterUserIP = $obj->otherComment_CounterUserIP;
                           
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
            
            closeDBConnection($this->conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    static function getHTMLByID($ID)
    {
        $output = "";
        try{
            $ID = trim($ID);
            if (!$ID) {
                throw new Exception('ID=$ID is Null!');
            }
            
            $conn = "";
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $GLOBALS['log'] .= "<br/> CrawlerURL::getHTMLByID($ID)";
            
            $sql = "select *
                    from afd
                    where AFDID='$ID'";
                        
            //echo $sql;
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output =  str_replace("background-color: #F3F9FF;"," ",$row['AFDHTML']);
                    
                    $output.= "<br/><table border='1'>";
                    $output.= "<tr>
                                    <td>AFDID</td>
                                    <td>debateDateListID</td>
                                    <td>AFDTitle</td>
                                    <td>AFDTitleID</td>
                                    <td>AFDURL</td>
                                    <td>AFDURL_2</td>
                                    <td>articleURL</td>
                                    <td>articleID</td>
                               </tr>";
                    $output.= "<tr>
                                <td>".$row['AFDID']."</td>
                                <td>".$row['debateDateListID']."</td>
                                <td>".$row['AFDTitle']."</td>
                                <td>".$row['AFDTitleID']."</td>
                                <td><a target='_blank' href='https://en.wikipedia.org".$row['AFDURL']."'>".$row['AFDURL']."</a></td>
                                <td>".$row['AFDURL_2']."</td>
                                <td><a target='_blank' href='https://en.wikipedia.org".$row['articleURL']."'>".$row['articleURL']."</a></td>
                                <td>".$row['articleID']."</td>
                               </tr>";
                    $output.= "</table>";
                    
                    $output.= "<br/><table border='1'>";
                    $output.= "<tr>
                                    <td>flag_AFDURL_Working</td>
                                    <td>flag_articleURL_Working</td>
                                    <td>flag_deletedArticle</td>
                                    <td>flag_error</td>
                                    <td>flag_toBeRemoved</td>
                                    <td>flag_DoNotParse</td>
                                    <td>flag_DoNotVisualize</td>
                                    <td>flag_completeAFDParse</td>
                                    <td>flag_otherComment_empty</td>
                                    <td>endResult</td>
                                    <td>endResult_User</td>
                                    <td>endResult_UserPosition</td>
                                    <td>endResult_UserTitle</td>
                                    <td>endResult_UserURL</td>
                                    <td>endResult_UserURLType</td>
                                    <td>endResult_Date</td>
                                    <td>endResult_Time</td>
                                    <td>endResult_DateTime</td>
                                    <td>endResult_ExtraNote</td>
                                    <td>endResult_Type</td>
                                    <td>parse_endResult_s</td>
                                    <td>parse_endResult_e</td>
                                    <td>parse_endResult_details</td>
                                    <td>parse_mainComment</td>
                                    <td>parse_otherComment</td>
                                    <td>parse_otherComment_User</td>
                                    <td>otherComment_CounterTime</td>
                                    <td>otherComment_CounterDate</td>
                                    <td>otherComment_CounterUTC</td>
                                    <td>otherComment_CounterUserNormal</td>
                                    <td>otherComment_CounterUserTalk</td>
                                    <td>otherComment_CounterUserNew</td>
                                    <td>otherComment_CounterUserIP</td>
                               </tr>";
                    $output.= "<tr>
                                <td>".$row['flag_AFDURL_Working']."</td>
                                <td>".$row['flag_articleURL_Working']."</td>
                                <td>".$row['flag_deletedArticle']."</td>
                                <td>".$row['flag_error']."</td>
                                <td>".$row['flag_toBeRemoved']."</td>
                                <td>".$row['flag_DoNotParse']."</td>
                                <td>".$row['flag_DoNotVisualize']."</td>
                                <td>".$row['flag_completeAFDParse']."</td>
                                <td>".$row['flag_otherComment_empty']."</td>
                                <td>".$row['endResult']."</td>
                                <td>".$row['endResult_User']."</td>
                                <td>".$row['endResult_UserPosition']."</td>
                                <td>".$row['endResult_UserTitle']."</td>
                                <td>".$row['endResult_UserURL']."</td>
                                <td>".$row['endResult_UserURLType']."</td>
                                <td>".$row['endResult_Date']."</td>
                                <td>".$row['endResult_Time']."</td>
                                <td>".$row['endResult_DateTime']."</td>
                                <td>".$row['endResult_ExtraNote']."</td>
                                <td>".$row['endResult_Type']."</td>
                                <td>".$row['parse_endResult_s']."</td>
                                <td>".$row['parse_endResult_e']."</td>
                                <td>".$row['parse_endResult_details']."</td>
                                <td>".$row['parse_mainComment']."</td>
                                <td>".$row['parse_otherComment']."</td>
                                <td>".$row['parse_otherComment_User']."</td>
                                <td>".$row['otherComment_CounterTime']."</td>
                                <td>".$row['otherComment_CounterDate']."</td>
                                <td>".$row['otherComment_CounterUTC']."</td>
                                <td>".$row['otherComment_CounterUserNormal']."</td>
                                <td>".$row['otherComment_CounterUserTalk']."</td>
                                <td>".$row['otherComment_CounterUserNew']."</td>
                                <td>".$row['otherComment_CounterUserIP']."</td>
                               </tr>";
                    $output.= "</table>";
                    $output.= "<span class='percantage'>endResult_Html:</span>";
                    $output.= $row['endResult_Html'];
                    $output.= "<span class='percantage'>endResult_Note:</span>";
                    $output.= $row['endResult_Note'];
                    
                    $output.= "<table border='1'>";
                    $output.= "<tr>
                                    <td>mainComment</td>
                                    <td>mainComment_User</td>
                                    <td>mainComment_UserPosition</td>
                                    <td>mainComment_UserTitle</td>
                                    <td>mainComment_UserURL</td>
                                    <td>mainComment_UserURLType</td>
                                    <td>mainComment_Date</td>
                                    <td>mainComment_Time</td>
                                    <td>mainComment_DateTime</td>
                                    <td>mainComment_Note</td>
                                    <td>mainComment_ExtraNote</td>
                                    <td>mainComment_ExtraNote2</td>
                                    <td>mainComment_Type</td>
                               </tr>";
                    $output.= "<tr>
                                <td>".$row['mainComment']."</td>
                                <td>".$row['mainComment_User']."</td>
                                <td>".$row['mainComment_UserPosition']."</td>
                                <td>".$row['mainComment_UserTitle']."</td>
                                <td>".$row['mainComment_UserURL']."</td>
                                <td>".$row['mainComment_UserURLType']."</td>
                                <td>".$row['mainComment_Date']."</td>
                                <td>".$row['mainComment_Time']."</td>
                                <td>".$row['mainComment_DateTime']."</td>
                                <td>".$row['mainComment_Note']."</td>
                                <td>".$row['mainComment_ExtraNote']."</td>
                                <td>".$row['mainComment_ExtraNote2']."</td>
                                <td>".$row['mainComment_Type']."</td>
                               </tr>";
                    $output.= "</table>";
                    $output.= "<span class='percantage'>mainComment_Html:</span>";
                    $output.= $row['mainComment_Html'];
                    $output.= "<br/><span class='percantage'>plainlinks_Html:</span>";
                    $output.= $row['plainlinks_Html'];
                    $output.= "<br/><span class='percantage'>otherComment_Html:</span>";
                    $output.= $row['otherComment_Html'];
                }
            }
            else
            {
                throw new Exception('There is No record in DB for AFDID=$ID !');
            }
            
            closeDBConnection($conn, $conn_NeedToClose);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    static function getOnlyHTMLByID($ID)
    {
        $output = "";
        try{
            $ID = trim($ID);
            if (!$ID) {
                throw new Exception('ID=$ID is Null!');
            }
            
            $conn = "";
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $GLOBALS['log'] .= "<br/> AFD::getOnlyHTMLByID($ID)";
            
            $sql = "select AFDHTML
                    from afd
                    where AFDID='$ID'";
                        
            //echo $sql;
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output =  str_replace("background-color: #F3F9FF;"," ",$row['AFDHTML']);
                }
            }
            else
            {
                throw new Exception('There is No record in DB for AFDID=$ID !');
            }
            closeDBConnection($conn, $conn_NeedToClose);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    static function getHTMLByURL($AFDURL)
    {
        $output = "";
        try{
            $AFDURL = trim($AFDURL);
            if (!$AFDURL) {
                throw new Exception('AFDURL=$AFDURL is Null!');
            }
            
            $conn = "";
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $GLOBALS['log'] .= "<br/> AFD::getHTMLByURL($AFDURL)";
            
            $sql = "select AFDID, AFDURL, AFDHTML
                    from afd
                    where AFDURL='$AFDURL'";
                        
            //echo $sql;
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output = $row[''];
                    $output .= "<br/><br/><a href='".$row['AFDURL']."'>".$row['AFDURL']."</a><br/><br/>";
                    $GLOBALS['log'] .= "<br/>". basename(__FILE__, '.php').".php AFD::getHTMLByURL() AFDID:".$row["AFDID"]." AFDURL: ".$row["AFDURL"]." </br>" ;
                }
            }
            else
            {
                throw new Exception('There is no record in DB for AFDURL=$AFDURL!');
            }
            closeDBConnection($conn, $conn_NeedToClose);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    private function insertAFD()
    {
        $GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->insertAFD() </span>";
        
        //$AFDTitle, $debateDateListID
        try{
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
        
            // It is optimized to increase the performance
            $sql = "INSERT INTO `AFD` ( debateDateListID, AFDTitleID, AFDTitle) 
            VALUES ( '$this->debateDateListID', '". mysql_real_escape_string($this->AFDTitleID)."', '". mysql_real_escape_string($this->AFDTitle)."' )";
            
            if (mysqli_query($this->conn, $sql)) {
                $this->AFDID = $this->conn->insert_id;
                $GLOBALS['log'] .= "<br/> AFD Inserted <span class='good'>successfully</span> to DB AFDID=". $this->AFDID;
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to insert to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
             
    function updateAFD_Initial()
    {
        try{
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitleID) {
                throw new Exception('AFDTitle is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }
                
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
                 
            $sql = "update afd
            set AFDTitleID = '$this->AFDTitleID', 
            AFDURL = '". mysql_real_escape_string($this->AFDURL)."', 
            AFDHTML = '". mysql_real_escape_string($this->AFDHTML)."', 
            articleURL = '". mysql_real_escape_string($this->articleURL)."', 
            articleID = '$this->articleID', 
            AFDURL_2 = '". mysql_real_escape_string($this->AFDURL_2)."', 
            flag_deletedArticle = '$this->flag_deletedArticle', 
            flag_articleURL_Working = '$this->flag_articleURL_Working', 
            endResult_Html = '". mysql_real_escape_string($this->endResult_Html)."',  
            parse_endResult_s='". mysql_real_escape_string($this->parse_endResult_s)."', 
            parse_endResult_e='". mysql_real_escape_string($this->parse_endResult_e)."' 
            where  AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
            and AFDID = '". mysql_real_escape_string($this->AFDID)."'";
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> AFD updated(Initial) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to update(Initial) to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
                
    function updateAFD()
    {
        try{
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitleID) {
                throw new Exception('AFDTitle is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }
                
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
                 
            $sql = "update afd
            set AFDTitleID = '$this->AFDTitleID', 
            AFDURL = '". mysql_real_escape_string($this->AFDURL)."', 
            AFDHTML = '". mysql_real_escape_string($this->AFDHTML)."', 
            articleURL = '". mysql_real_escape_string($this->articleURL)."', 
            articleID = '$this->articleID', 
            AFDURL_2 = '". mysql_real_escape_string($this->AFDURL_2)."', 
            flag_AFDURL_Working = '$this->flag_AFDURL_Working', 
            flag_articleURL_Working = '$this->flag_articleURL_Working', 
            flag_deletedArticle = '$this->flag_deletedArticle', 
            flag_error ='$this->flag_error', 
            flag_toBeRemoved ='$this->flag_toBeRemoved', 
            flag_DoNotParse ='$this->flag_DoNotParse',
            flag_DoNotVisualize ='$this->flag_DoNotVisualize', 
            flag_completeAFDParse ='$this->flag_completeAFDParse', 
            flag_otherComment_empty ='$this->flag_otherComment_empty', 
            endResult = '". mysql_real_escape_string($this->endResult)."', 
            endResult_User = '". mysql_real_escape_string($this->endResult_User)."', 
            endResult_UserPosition = '". mysql_real_escape_string($this->endResult_UserPosition)."', 
            endResult_UserTitle = '". mysql_real_escape_string($this->endResult_UserTitle)."', 
            endResult_UserURL = '". mysql_real_escape_string($this->endResult_UserURL)."',  
            endResult_UserURLType = '". mysql_real_escape_string($this->endResult_UserURLType)."', 
            endResult_Date = '". mysql_real_escape_string($this->endResult_Date)."', 
            endResult_Time = '". mysql_real_escape_string($this->endResult_Time)."', 
            endResult_DateTime = '". mysql_real_escape_string($this->endResult_DateTime)."', 
            endResult_Note = '". mysql_real_escape_string($this->endResult_Note)."', 
            endResult_Html = '". mysql_real_escape_string($this->endResult_Html)."', 
            endResult_ExtraNote = '". mysql_real_escape_string($this->endResult_ExtraNote)."', 
            endResult_Type = '". mysql_real_escape_string($this->endResult_Type)."', 
            mainComment = '". mysql_real_escape_string($this->mainComment)."', 
            mainComment_User = '". mysql_real_escape_string($this->mainComment_User)."', 
            mainComment_UserPosition = '". mysql_real_escape_string($this->mainComment_UserPosition)."', 
            mainComment_UserTitle = '". mysql_real_escape_string($this->mainComment_UserTitle)."', 
            mainComment_UserURL = '". mysql_real_escape_string($this->mainComment_UserURL)."', 
            mainComment_UserURLType = '". mysql_real_escape_string($this->mainComment_UserURLType)."', 
            mainComment_Date = '". mysql_real_escape_string($this->mainComment_Date)."', 
            mainComment_Time = '". mysql_real_escape_string($this->mainComment_Time)."', 
            mainComment_DateTime = '". mysql_real_escape_string($this->mainComment_DateTime)."', 
            mainComment_Note='". mysql_real_escape_string($this->mainComment_Note)."', 
            mainComment_Html = '". mysql_real_escape_string($this->mainComment_Html)."', 
            mainComment_ExtraNote = '". mysql_real_escape_string($this->mainComment_ExtraNote)."',
            mainComment_ExtraNote2 = '". mysql_real_escape_string($this->mainComment_ExtraNote2)."', 
            mainComment_Type = '". mysql_real_escape_string($this->mainComment_Type)."', 
            plainlinks_Html = '". mysql_real_escape_string($this->plainlinks_Html)."', 
            otherComment_Html = '". mysql_real_escape_string($this->otherComment_Html)."', 
            parse_endResult_s='". mysql_real_escape_string($this->parse_endResult_s)."', 
            parse_endResult_e='". mysql_real_escape_string($this->parse_endResult_e)."', 
            parse_endResult_details='". mysql_real_escape_string($this->parse_endResult_details)."',  
            parse_mainComment='". mysql_real_escape_string($this->parse_mainComment)."', 
            parse_otherComment='". mysql_real_escape_string($this->parse_otherComment)."',
            parse_otherComment_User='". mysql_real_escape_string($this->parse_otherComment_User)."', 
            otherComment_CounterTime='". mysql_real_escape_string($this->otherComment_CounterTime)."',
            otherComment_CounterDate='". mysql_real_escape_string($this->otherComment_CounterDate)."',
            otherComment_CounterUTC='". mysql_real_escape_string($this->otherComment_CounterUTC)."',
            otherComment_CounterUserNormal='". mysql_real_escape_string($this->otherComment_CounterUserNormal)."',
            otherComment_CounterUserTalk='". mysql_real_escape_string($this->otherComment_CounterUserTalk)."',
            otherComment_CounterUserNew='". mysql_real_escape_string($this->otherComment_CounterUserNew)."',
            otherComment_CounterUserIP='". mysql_real_escape_string($this->otherComment_CounterUserIP)."'
            where  AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
            and AFDID = '". mysql_real_escape_string($this->AFDID)."'";
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> AFD updated (updateAFD) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
            //$GLOBALS['log'] .= "<br/> AFD updated <span class='percentage'>otherComment_Html</span>$this->otherComment_Html<hr/>".$sql;
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    function updateAFD_withoutAFDHTML_byAFDID()
    {
        try{
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitleID) {
                throw new Exception('AFDTitle is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }
                
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
            
            $sql = "update afd
            set AFDTitleID = '$this->AFDTitleID', 
            AFDURL = '". mysql_real_escape_string($this->AFDURL)."', 
            articleURL = '". mysql_real_escape_string($this->articleURL)."', 
            articleID = '$this->articleID', 
            AFDURL_2 = '". mysql_real_escape_string($this->AFDURL_2)."', 
            flag_AFDURL_Working = '$this->flag_AFDURL_Working', 
            flag_articleURL_Working = '$this->flag_articleURL_Working', 
            flag_deletedArticle = '$this->flag_deletedArticle', 
            flag_error ='$this->flag_error', 
            flag_toBeRemoved ='$this->flag_toBeRemoved', 
            flag_DoNotParse ='$this->flag_DoNotParse', 
            flag_DoNotVisualize ='$this->flag_DoNotVisualize',
            flag_completeAFDParse ='$this->flag_completeAFDParse', 
            flag_otherComment_empty ='$this->flag_otherComment_empty', 
            endResult = '". mysql_real_escape_string($this->endResult)."', 
            endResult_User = '". mysql_real_escape_string($this->endResult_User)."', 
            endResult_UserPosition = '". mysql_real_escape_string($this->endResult_UserPosition)."', 
            endResult_UserTitle = '". mysql_real_escape_string($this->endResult_UserTitle)."', 
            endResult_UserURL = '". mysql_real_escape_string($this->endResult_UserURL)."',  
            endResult_UserURLType = '". mysql_real_escape_string($this->endResult_UserURLType)."',  
            endResult_Date = '". mysql_real_escape_string($this->endResult_Date)."', 
            endResult_Time = '". mysql_real_escape_string($this->endResult_Time)."', 
            endResult_DateTime = '". mysql_real_escape_string($this->endResult_DateTime)."', 
            endResult_Note = '". mysql_real_escape_string($this->endResult_Note)."', 
            endResult_Html = '". mysql_real_escape_string($this->endResult_Html)."', 
            endResult_ExtraNote = '". mysql_real_escape_string($this->endResult_ExtraNote)."', 
            endResult_Type = '". mysql_real_escape_string($this->endResult_Type)."', 
            mainComment = '". mysql_real_escape_string($this->mainComment)."', 
            mainComment_User = '". mysql_real_escape_string($this->mainComment_User)."', 
            mainComment_UserPosition = '". mysql_real_escape_string($this->mainComment_UserPosition)."', 
            mainComment_UserTitle = '". mysql_real_escape_string($this->mainComment_UserTitle)."',  
            mainComment_UserURL = '". mysql_real_escape_string($this->mainComment_UserURL)."',  
            mainComment_Date = '". mysql_real_escape_string($this->mainComment_Date)."', 
            mainComment_Time = '". mysql_real_escape_string($this->mainComment_Time)."', 
            mainComment_DateTime = '". mysql_real_escape_string($this->mainComment_DateTime)."', 
            mainComment_Note = '". mysql_real_escape_string($this->mainComment_Note)."', 
            mainComment_Html = '". mysql_real_escape_string($this->mainComment_Html)."', 
            mainComment_ExtraNote = '". mysql_real_escape_string($this->mainComment_ExtraNote)."', 
            mainComment_ExtraNote2 = '". mysql_real_escape_string($this->mainComment_ExtraNote2)."',
            mainComment_Type = '". mysql_real_escape_string($this->mainComment_Type)."', 
            plainlinks_Html = '". mysql_real_escape_string($this->plainlinks_Html)."', 
            otherComment_Html = '". mysql_real_escape_string($this->otherComment_Html)."', 
            parse_endResult_s='". mysql_real_escape_string($this->parse_endResult_s)."', 
            parse_endResult_e='". mysql_real_escape_string($this->parse_endResult_e)."', 
            parse_endResult_details='". mysql_real_escape_string($this->parse_endResult_details)."',
            parse_mainComment='". mysql_real_escape_string($this->parse_mainComment)."', 
            parse_otherComment='". mysql_real_escape_string($this->parse_otherComment)."',
            parse_otherComment_User='". mysql_real_escape_string($this->parse_otherComment_User)."', 
            otherComment_CounterTime='". mysql_real_escape_string($this->otherComment_CounterTime)."',
            otherComment_CounterDate='". mysql_real_escape_string($this->otherComment_CounterDate)."',
            otherComment_CounterUTC='". mysql_real_escape_string($this->otherComment_CounterUTC)."',
            otherComment_CounterUserNormal='". mysql_real_escape_string($this->otherComment_CounterUserNormal)."',
            otherComment_CounterUserTalk='". mysql_real_escape_string($this->otherComment_CounterUserTalk)."',
            otherComment_CounterUserNew='". mysql_real_escape_string($this->otherComment_CounterUserNew)."',
            otherComment_CounterUserIP='". mysql_real_escape_string($this->otherComment_CounterUserIP)."'  
            where  AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
            and AFDID = '". mysql_real_escape_string($this->AFDID)."'";
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> AFD updated (withoutAFDHTML) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
            
            //$GLOBALS['log'] .= "<br/> AFD updated <span class='percentage'>otherComment_Html</span>$this->otherComment_Html<hr/>".$sql;
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    function updateAFD_OnlyEndResult_byAFDID()
    {
         try{
            
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitleID) {
                throw new Exception('AFDTitleID is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
            
            $sql = "update afd 
            set endResult = '". mysql_real_escape_string($this->endResult)."', 
            endResult_User = '". mysql_real_escape_string($this->endResult_User)."', 
            endResult_UserPosition = '". mysql_real_escape_string($this->endResult_UserPosition)."', 
            endResult_UserTitle = '". mysql_real_escape_string($this->endResult_UserTitle)."',  
            endResult_UserURL = '". mysql_real_escape_string($this->endResult_UserURL)."',   
            endResult_UserURLType = '". mysql_real_escape_string($this->endResult_UserURLType)."',
            endResult_Date = '". mysql_real_escape_string($this->endResult_Date)."',
            endResult_Time = '". mysql_real_escape_string($this->endResult_Time)."',
            endResult_DateTime = '". mysql_real_escape_string($this->endResult_DateTime)."', 
            endResult_Note = '". mysql_real_escape_string($this->endResult_Note)."', 
            endResult_Html = '". mysql_real_escape_string($this->endResult_Html)."', 
            endResult_ExtraNote = '". mysql_real_escape_string($this->endResult_ExtraNote)."',
            endResult_Type = '". mysql_real_escape_string($this->endResult_Type)."',
            flag_DoNotParse = '". mysql_real_escape_string($this->flag_DoNotParse)."',
            flag_DoNotVisualize = '". mysql_real_escape_string($this->flag_DoNotVisualize)."', 
            parse_endResult_details='". mysql_real_escape_string($this->parse_endResult_details)."'  
            where  AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
            and AFDID = '". mysql_real_escape_string($this->AFDID)."'";
            
            //$GLOBALS['log'] .= "<hr/>$sql<hr/>";
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> AFD updated (OnlyEndResult) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
        }   
        catch (Exception $e) {
            echo "<br/><span class='bad'>Caught exception: ",  $e->getMessage(), "</span>\n";
        }
    }
    
    function updateAFD_OnlyMainComment_byAFDID()
    {
         try{
            
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitleID) {
                throw new Exception('AFDTitleID is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
            
            $sql = "update afd 
            set mainComment = '". mysql_real_escape_string($this->mainComment)."', 
            mainComment_User = '". mysql_real_escape_string($this->mainComment_User)."', 
            mainComment_UserPosition = '". mysql_real_escape_string($this->mainComment_UserPosition)."', 
            mainComment_UserTitle = '". mysql_real_escape_string($this->mainComment_UserTitle)."', 
            mainComment_UserURL = '". mysql_real_escape_string($this->mainComment_UserURL)."', 
            mainComment_UserURLType = '". mysql_real_escape_string($this->mainComment_UserURLType)."', 
            mainComment_Date = '". mysql_real_escape_string($this->mainComment_Date)."',
            mainComment_Time = '". mysql_real_escape_string($this->mainComment_Time)."',
            mainComment_DateTime = '". mysql_real_escape_string($this->mainComment_DateTime)."', 
            mainComment_Note = '". mysql_real_escape_string($this->mainComment_Note)."', 
            mainComment_Html = '". mysql_real_escape_string($this->mainComment_Html)."', 
            mainComment_ExtraNote = '". mysql_real_escape_string($this->mainComment_ExtraNote)."',
            mainComment_ExtraNote2 = '". mysql_real_escape_string($this->mainComment_ExtraNote2)."',
            mainComment_Type = '". mysql_real_escape_string($this->mainComment_Type)."',
            flag_DoNotParse = '". mysql_real_escape_string($this->flag_DoNotParse)."',
            flag_DoNotVisualize = '". mysql_real_escape_string($this->flag_DoNotVisualize)."', 
            parse_mainComment='". mysql_real_escape_string($this->parse_mainComment)."'
            where  AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
            and AFDID = '". mysql_real_escape_string($this->AFDID)."'";
            
            //$GLOBALS['log'] .= "<hr/>$sql<hr/>";
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> AFD updated (OnlyMainComment) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
        }   
        catch (Exception $e) {
            echo "<br/><span class='bad'>Caught exception: ",  $e->getMessage(), "</span>\n";
        }
    }
    
    function updateAFD_OnlyOtherComment_byAFDID()
    {
         try{
            
            if (!$this->debateDateListID) {
                throw new Exception('debateDateListID is Null!');
            }
            if (!$this->AFDTitleID) {
                throw new Exception('AFDTitleID is Null!');
            }
            if (!$this->AFDTitle) {
                throw new Exception('AFDTitle is Null!');
            }
            
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
            
            $sql = "update afd 
            set parse_otherComment ='". mysql_real_escape_string($this->parse_otherComment)."',
            parse_otherComment_User ='". mysql_real_escape_string($this->parse_otherComment_User)."', 
            otherComment_CounterTime='". mysql_real_escape_string($this->otherComment_CounterTime)."',
            otherComment_CounterDate='". mysql_real_escape_string($this->otherComment_CounterDate)."',
            otherComment_CounterUTC='". mysql_real_escape_string($this->otherComment_CounterUTC)."',
            otherComment_CounterUserNormal='". mysql_real_escape_string($this->otherComment_CounterUserNormal)."',
            otherComment_CounterUserTalk='". mysql_real_escape_string($this->otherComment_CounterUserTalk)."',
            otherComment_CounterUserNew='". mysql_real_escape_string($this->otherComment_CounterUserNew)."',
            otherComment_CounterUserIP='". mysql_real_escape_string($this->otherComment_CounterUserIP)."'
            where AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."'
            and AFDID = '". mysql_real_escape_string($this->AFDID)."'";
            
            //$GLOBALS['log'] .= "<hr/>$sql<hr/>";
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> AFD updated(OnlyOtherComment) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> AFD <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
        }   
        catch (Exception $e) {
            echo "<br/><span class='bad'>Caught exception: ",  $e->getMessage(), "</span>\n";
        }
    }
} 
?>