<?php 
require_once "config.php";
require_once "functions.php";

class Comment {
    public $commentID; //ID
    public $parrentCommentID;
    public $debateDateListID;
    public $AFDID; // create Unique Combination 1
    public $AFDTitle;
    public $AFDTitleID;
    public $articleID;
    public $comment;
    public $comment_UserCheck;
    public $comment_User; // create Unique Combination 2
    public $comment_UserPosition;
    public $comment_UserTitle;
    public $comment_UserURL;
    public $comment_UserURLType;
    public $comment_Date;
    public $comment_Time;
    public $comment_DateTime; // create Unique Combination 3
    public $comment_Note;
    public $comment_Html; // need to added to the Unique combination by using levenshtein(str1,str2) to get the distance between two string. example AFDID=154
    public $comment_ExtraNote;
    public $comment_Type;
    public $comment_Type2;
    public $flag_DoNotVisualize_Comment;
    public $distinguishPercentage;
    
    private $conn;
    
    public function Comment($AFDID, $AFDTitleID, $debateDateListID, $comment_Html, $comment_User, $comment_DateTime, $conn) {
        $this->AFDTitleID = trim($AFDTitleID);
        $this->AFDID = $AFDID;
        $this->debateDateListID = $debateDateListID;
        $this->comment_User = trim($comment_User);
        $this->comment_DateTime = trim($comment_DateTime);
        $this->comment_Html = $comment_Html;
        
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call Comment->Comment() </span>";
        //$GLOBALS['log'] .= "<br/>this->AFDTitleID=$this->AFDTitleID this->AFDID=$this->AFDID ";
        //$GLOBALS['log'] .= "<br/>this->comment_Html=$this->comment_Html ";
        
        //create the connection
        $this->conn = $conn;
        
        $conn_NeedToClose = false;
        openDBConnection($this->conn, $conn_NeedToClose);
        
        if( !empty($this->AFDID) && !empty($this->debateDateListID) )
        {
            if(!( $this->checkExistingTitle($this->AFDID, $this->debateDateListID, $this->comment_Html, $this->comment_User, $this->comment_DateTime, $conn) != -1))
                $this->insert_Comment();
        }
            
        //$GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called Comment::Comment()*******************</span>";
    }
    
   function __destruct() {
        //if(is_resource($this->conn))
//            mysqli_close($this->conn);
   }
   
    //could be optimize by passing another boolean argument and limit the variable
    private function checkExistingTitle($AFDID, $debateDateListID, $comment_Html, $comment_User, $comment_DateTime, $givenConn)
    {
        $output = -1;
        try{
            $conn = "";
            
            if (!$AFDID)  
                throw new Exception('AFDID is empty!');
            if (!$debateDateListID)  
                throw new Exception('debateDateListID is empty!');
            
            // might be empty
            $comment_User =  trim($comment_User);
            $comment_DateTime =  trim($comment_DateTime);
            
            //create the connection
            if($givenConn)
                $conn = $givenConn;
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            
            //get all comment by a user on a given time
            $sql = "SELECT *
                    FROM  `comment`
                    where AFDID = '". mysql_real_escape_string($AFDID)."' 
                    and debateDateListID = '". mysql_real_escape_string($debateDateListID)."'";
            if(!empty($comment_User)) 
                $sql .= " and comment_User = '". mysql_real_escape_string($comment_User)."'";
            if(!empty($comment_DateTime))
                $sql .= " and comment_DateTime = '". mysql_real_escape_string($comment_DateTime)."'";
            if(!empty($comment_Html))
                $sql .= " and comment_Html = '". mysql_real_escape_string($comment_Html)."'";
                
            
            //$GLOBALS['log'] .= "<br/> SQL: $sql<hr/>";
            if ($result=mysqli_query($conn,$sql))
            {
                //store all of the record in an array
                $arrayRecords = array();
                while ($obj = mysqli_fetch_object($result))
                {
                    $arrayRecords[] = $obj;
                }
                // Free result set
                mysqli_free_result($result); 
                
                //check for duplication
                $existingRecord = false;
                $existingIndex = -1;
                for($i=0; $i< count($arrayRecords);$i++)
                {
                    $obj = $arrayRecords[$i];
                    $currentHtml = $obj->comment_Html;
                    
                    //exactly the same html
                    if( $currentHtml == $comment_Html )
                    {
                        $existingIndex = $i;
                        $existingRecord = true;
                        break;
                    }
                    
                    //have a similarity less than 6
                    else if(levenshtein(strip_tags($currentHtml), strip_tags($comment_Html) < 6))
                    {
                        $existingIndex = $i;
                        $existingRecord = true;
                        break;
                    }
                }
                
                //if exsit set the object to this sql record with index of $existingIndex
                if($existingRecord)
                {
                    $obj = $arrayRecords[$existingIndex];
                    if ($existingIndex == -1)  
                        throw new Exception('<span class="bad"> comment->checkExistingTitle, $existingIndex is -1, there is logical error in code!</span>');
                        
                    $output= $obj->commentID;
                    
                    $this->commentID = $obj->commentID; //ID
                    $this->parrentCommentID = $obj->parrentCommentID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDID = $obj->AFDID; // create Unique Combination 1
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    $this->articleID = $obj->articleID;
                    $this->comment = $obj->comment;
                    $this->comment_UserCheck = $obj->comment_UserCheck;
                    $this->comment_User = $obj->comment_User; // create Unique Combination 2
                    $this->comment_UserPosition = $obj->comment_UserPosition;
                    $this->comment_UserTitle = $obj->comment_UserTitle;
                    $this->comment_UserURL = $obj->comment_UserURL;
                    $this->comment_UserURLType = $obj->comment_UserURLType;
                    $this->comment_Date = $obj->comment_Date;
                    $this->comment_Time = $obj->comment_Time;
                    $this->comment_DateTime = $obj->comment_DateTime; // create Unique Combination 3
                    $this->comment_Note = $obj->comment_Note;
                    $this->comment_Html = $obj->comment_Html;
                    $this->comment_ExtraNote = $obj->comment_ExtraNote;
                    $this->comment_Type = $obj->comment_Type;
                    $this->comment_Type2 = $obj->comment_Type2;
                    $this->flag_DoNotVisualize_Comment = $obj->flag_DoNotVisualize_Comment;
                    $this->distinguishPercentage = $obj->distinguishPercentage;
                }
            }
            else
                throw new Exception('Error on mysqli_query!');
            
            closeDBConnection($conn, $conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception : ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    static function removedAllComment_ByAFDID($givenAFDID)
    {
        try{
            
            //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call Comment::removedAllComment_ByAFDID(givenAFDID=$givenAFDID) </span>";
        
            if (!$givenAFDID)  
                throw new Exception('AFDID is empty!');
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            //if(empty($conn))
            //   $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            
            $sql = "DELETE  
                    from `comment`
                    where AFDID = '". mysql_real_escape_string($givenAFDID)."'";
            
            if (mysqli_query($conn, $sql))
                $GLOBALS['log'] .= "<br/> Comments <u>deleteted</u> <span class='good'>successfully</span> in DB for AFDID=". $givenAFDID;
            else
                $GLOBALS['log'] .= "<br/> Comments <span class='bad'>ErrorDeleting</span> in DB for AFDID=".$givenAFDID;
            
            //$GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called Comment::removedAllComment_ByAFDID(No.:". $conn->affected_rows.")*******************</span>";
            
            closeDBConnection($conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    //Lili gago change from loadFromDBByAFDID to loadFromDBByCommentLinkID
    static function loadFromDBByCommentID($commentID , $conn="")
    {
        $comment = -1; 
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call comment::loadFromDBByCommentID() </span>";
        try{
            $commentID = trim($commentID);
            
            if (!$commentID)  
                throw new Exception('commentID is empty!');
            
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $sql = "SELECT commentID, AFDTitleID, AFDID, debateDateListID, comment_Html, comment_User, comment_DateTime
                    FROM `comment` 
                    where commentID = '". mysql_real_escape_string($commentID)."'";
                         
            if ($result=mysqli_query($conn,$sql))
            {
                if ($obj=mysqli_fetch_object($result))
                {
                    $comment = new Comment( $obj->AFDID, $obj->AFDTitleID, $obj->debateDateListID, $obj->comment_Html, $obj->comment_User, $obj->comment_DateTime, $conn);
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
        return $comment;
    }
    
    static function load_DBObject_ByAFDID($AFDID)
    {
        $comments = array(); 
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call comment::loadFromDBByCommentID() </span>";
        try{
            $AFDID = trim($AFDID);
            
            if (!$AFDID)  
                throw new Exception('commentID is empty!');
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
        
            $sql = "SELECT *
                    FROM `comment` 
                    where AFDID = '". mysql_real_escape_string($AFDID)."'";
                         
            if ($result=mysqli_query($conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    // such as delete or keep
                    if($obj->comment_Type == 1)
                        $comments[] = $obj;
                    //Flag_Text
                    else if ( $obj->comment_Type != 0 )
                        $comments[] = $obj;
                }
                // Free result set
                mysqli_free_result($result);
                
            }
            else
                throw new Exception('Error on mysqli_query!');
            
            closeDBConnection($conn, $conn_NeedToClose);
        }
        catch (Exception $e) {
            echo '<br/><span class=\'bad\'>Caught exception: </span>',  $e->getMessage(), "\n";
        }
        return $comments;
    }
    
    function loadFromDB()
    {
        try{
            $this->AFDTitle = trim($this->AFDTitle);
            $this->AFDTitleID = trim($this->AFDTitleID);
            $this->comment_User = trim($this->comment_User);
            
            if (!$this->AFDID)  
                throw new Exception('AFDID is empty!');
            if (!$this->debateDateListID)  
                throw new Exception('debateDateListID is empty!');
            if (!$this->comment_User)  
                throw new Exception('comment_User is empty!');
            if (!$this->comment_DateTime)  
                throw new Exception('comment_DateTime is empty!');
            
            $sql = "SELECT *
                    FROM  `comment`
                    where AFDID = '". mysql_real_escape_string($AFDID)."' 
                    and debateDateListID = '". mysql_real_escape_string($debateDateListID)."' 
                    and comment_User = '". mysql_real_escape_string($comment_User)."' 
                    and comment_DateTime = '". mysql_real_escape_string($comment_DateTime)."'";
                   
            if ($result=mysqli_query($this->conn,$sql))
            {
                while ($obj=mysqli_fetch_object($result))
                {
                    $this->commentID = $obj->commentID; //ID
                    $this->parrentCommentID = $obj->parrentCommentID;
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->AFDID = $obj->AFDID; // create Unique Combination 1
                    $this->AFDTitle = $obj->AFDTitle;
                    $this->AFDTitleID = $obj->AFDTitleID;
                    $this->articleID = $obj->articleID;
                    $this->comment = $obj->comment;
                    $this->comment_UserCheck = $obj->comment_UserCheck;
                    $this->comment_User = $obj->comment_User; // create Unique Combination 2
                    $this->comment_UserPosition = $obj->comment_UserPosition;
                    $this->comment_UserTitle = $obj->comment_UserTitle;
                    $this->comment_UserURL = $obj->comment_UserURL;
                    $this->comment_UserURLType = $obj->comment_UserURLType;
                    $this->comment_Date = $obj->comment_Date;
                    $this->comment_Time = $obj->comment_Time;
                    $this->comment_DateTime = $obj->comment_DateTime; // create Unique Combination 3
                    $this->comment_Note = $obj->comment_Note;
                    $this->comment_Html = $obj->comment_Html;
                    $this->comment_ExtraNote = $obj->comment_ExtraNote;
                    $this->comment_Type = $obj->comment_Type;
                    $this->comment_Type2 = $obj->comment_Type2;
                    $this->flag_DoNotVisualize_Comment = $obj->flag_DoNotVisualize_Comment;
                    $this->distinguishPercentage = $obj->distinguishPercentage;
                }
                // Free result set
                mysqli_free_result($result);
            }
            else
                throw new Exception('Error on mysqli_query!');
        }
        catch (Exception $e) {
            echo '<br/>Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    private function insert_Comment()
    {
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call afd->insert_Comment() </span>";
        
        try{
            if (!$this->AFDID)
                throw new Exception('AFDID is Null!');
            if (!$this->debateDateListID)
                throw new Exception('debateDateListID is Null!');
            if (!$this->AFDTitleID)
                throw new Exception('AFDTitleID is Null!');
            if (!$this->comment_Html)
                throw new Exception('comment_Html is Null!');
            
            $this->AFDTitleID = trim($this->AFDTitleID);
            $this->debateDateListID = trim($this->debateDateListID);
            $this->comment_Html = trim($this->comment_Html);
            $this->comment_User = trim($this->comment_User);
            $this->comment_DateTime = trim($this->comment_DateTime);
        
            $conn_NeedToClose = false;
            openDBConnection($this->conn, $conn_NeedToClose);
            
            $sql = "INSERT INTO `comment` ( AFDID, debateDateListID, AFDTitleID, comment_Html, comment_User, comment_DateTime) 
            VALUES ( '$this->AFDID', '". mysql_real_escape_string($this->debateDateListID)."', '". mysql_real_escape_string($this->AFDTitleID)."', '". mysql_real_escape_string($this->comment_Html)."', '". mysql_real_escape_string($this->comment_User)."', '". $this->comment_DateTime ."' )";
            
            //$GLOBALS['log'] .= "<br/>sql: $sql";
            
            if (mysqli_query($this->conn, $sql)) {
                $this->commentID = $this->conn->insert_id;
                $GLOBALS['log'] .= "<br/>Comment Inserted <span class='good'>successfully</span> to DB AFDID=". $this->AFDID.", commentID=$this->commentID";
            } else {
                $GLOBALS['log'] .= "<br/> Comment <span class='bad'>Failed</span> to insert to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
            
            closeDBConnection($this->conn, $conn_NeedToClose);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
                
    function update_Comment()
    {
        try{
            if (!$this->AFDID)
                throw new Exception('AFDID is Null!');
            if (!$this->debateDateListID)
                throw new Exception('debateDateListID is Null!');
            if (!$this->AFDTitleID)
                throw new Exception('AFDTitleID is Null!');
            if (!$this->comment_Html)
                throw new Exception('comment_Html is Null!');
            
            $this->AFDTitleID = trim($this->AFDTitleID);
            $this->debateDateListID = trim($this->debateDateListID);
            $this->comment_Html = trim($this->comment_Html);
            $this->comment_User = trim($this->comment_User);
            $this->comment_DateTime = trim($this->comment_DateTime);
                    
            $sql = "update `comment`
            set parrentCommentID = '". mysql_real_escape_string($this->parrentCommentID)."', 
            debateDateListID = '". mysql_real_escape_string($this->debateDateListID)."', 
            AFDID = '". mysql_real_escape_string($this->AFDID)."', 
            AFDTitle = '". mysql_real_escape_string($this->AFDTitle)."', 
            AFDTitleID = '". mysql_real_escape_string($this->AFDTitleID)."', 
            articleID = '". mysql_real_escape_string($this->articleID)."', 
            comment = '". mysql_real_escape_string($this->comment)."', 
            comment_UserCheck = '". mysql_real_escape_string($this->comment_UserCheck)."',
            comment_User = '". mysql_real_escape_string($this->comment_User)."', 
            comment_UserPosition = '". mysql_real_escape_string($this->comment_UserPosition)."', 
            comment_UserTitle = '". mysql_real_escape_string($this->comment_UserTitle)."', 
            comment_UserURL = '". mysql_real_escape_string($this->comment_UserURL)."', 
            comment_UserURLType = '". mysql_real_escape_string($this->comment_UserURLType)."', 
            comment_Date = '". mysql_real_escape_string($this->comment_Date)."', 
            comment_Time = '". mysql_real_escape_string($this->comment_Time)."', 
            comment_DateTime = '". mysql_real_escape_string($this->comment_DateTime)."',
            comment_Note = '". mysql_real_escape_string($this->comment_Note)."',
            comment_Html = '". mysql_real_escape_string($this->comment_Html)."',
            comment_ExtraNote = '". mysql_real_escape_string($this->comment_ExtraNote)."',
            comment_Type = '". mysql_real_escape_string($this->comment_Type)."',
            comment_Type2 = '". mysql_real_escape_string($this->comment_Type2)."', 
            flag_DoNotVisualize_Comment = '". mysql_real_escape_string($this->flag_DoNotVisualize_Comment)."',
            distinguishPercentage = '". mysql_real_escape_string($this->distinguishPercentage)."'
            where  commentID = '". mysql_real_escape_string($this->commentID)."'";
            
            //$GLOBALS['log'] .= "<br/>sql:$sql";
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> otherComment updated (update_otherComment) <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> otherComment <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
            //$GLOBALS['log'] .= "<br/> AFD updated <span class='percentage'>otherComment_Html</span>$this->otherComment_Html<hr/>".$sql;
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
} 
?>