<?php 
require_once "config.php";
require_once "functions.php";

class CommentLink {
    public $commentLinkID;
    public $commentID;
    public $AFDID;
    public $link_Html;
    public $giveComment_Html;
    public $link_Sentance;
    public $link_Title;
    public $link_Label;
    public $link_URL;
    public $link_URLPosition;
    public $link_User; 
    public $link_DateTime;
    public $link_External;
    public $link_Policy; 
    public $link_Class;
    public $link_PolarityGrade;
    public $link_PolarityKeyword;
    public $link_ToOtherUser;
    
    private $conn;
    
    public function CommentLink($commentID, $AFDID, $link_Html, $conn="") {
        $this->commentID = $commentID;
        $this->AFDID = $AFDID;
        $this->link_Html =trim($link_Html);
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call CommentLink->CommentLink() </span>";
        
        //create the connection
        $this->conn = $conn;
        
        $conn_NeedToClose = false;
        openDBConnection($this->conn, $conn_NeedToClose);
        
        if( !empty($this->commentID) && !empty($this->AFDID) && !empty($this->link_Html) )
        {
            if(!( $this->checkExisting($this->commentID, $this->AFDID, $this->link_Html, $conn) != -1))
                $this->insert_CommentLink();
        }    
        //$GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called CommentLink::CommentLink()*******************</span>";
    }
    
   function __destruct() {
        if(is_resource($this->conn))
            mysqli_close($this->conn);
   }
   
    //could be optimize by passing another boolean argument and limit the variable
    private function checkExisting($commentID, $AFDID, $link_Html, $givenConn)
    {
        $output = -1;
        try{
            $conn = "";
            $link_Html = trim($link_Html);
            
            if (!$commentID)  
                throw new Exception('CommentID is empty!');
            if (!$AFDID)  
                throw new Exception('AFDID is empty!');
            if (!$link_Html)  
                throw new Exception('link_Html is empty!');
            
            //create the connection
            if($givenConn)
                $conn = $givenConn;
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            //get all comment by a user on a given time
            $sql = "SELECT *
                    FROM  `commentLink`
                    where commentID = '". mysql_real_escape_string($commentID)."' 
                    and AFDID = '". mysql_real_escape_string($AFDID)."'
                    and link_Html = '". mysql_real_escape_string($link_Html)."'";
                    
            
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
                    $currentHtml = trim($obj->link_Html);
                    
                    //exactly the same html
                    if( $currentHtml == $link_Html )
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
                        throw new Exception('<span class="bad"> comment->checkExisting, $existingIndex is -1, there is logical error in code!</span>');
                        
                    $output= $obj->commentID;
                    $this->commentLinkID = $obj->commentLinkID;
                    $this->commentID = $obj->commentID; 
                    $this->AFDID = $obj->AFDID;
                    $this->link_Html = $obj->link_Html;
                    $this->giveComment_Html = $obj->giveComment_Html;
                    $this->link_Sentance = $obj->link_Sentance;
                    $this->link_Title = $obj->link_Title;
                    $this->link_Label = $obj->link_Label;
                    $this->link_URL = $obj->link_URL;
                    $this->link_URLPosition = $obj->link_URLPosition;
                    $this->link_User = $obj->link_User;
                    $this->link_DateTime = $obj->link_DateTime;
                    $this->link_External = $obj->link_External;
                    $this->link_Policy = $obj->link_Policy;
                    $this->link_Class = $obj->link_Class;
                    $this->link_PolarityGrade = $obj->link_PolarityGrade;
                    $this->link_PolarityKeyword = $obj->link_PolarityKeyword;
                    $this->link_ToOtherUser = $obj->link_ToOtherUser;
                }
            }
            else
                throw new Exception('Error on mysqli_query!');
            if(!$givenConn) 
                mysqli_close($conn);
            
            closeDBConnection($conn, $conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception : ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    static function removedAllCommentLink_ByAFDID($givenAFDID)
    {
        try{
            //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call CommentLink::removedAllCommentLink_ByAFDID(givenAFDID=$givenAFDID) </span>";
        
            if (!$givenAFDID)  
                throw new Exception('AFDID is empty!');
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $sql = "DELETE  
                    from `commentLink`
                    where AFDID = '". mysql_real_escape_string($givenAFDID)."'";
            
            if (mysqli_query($conn, $sql))
                $GLOBALS['log'] .= "</b><br/> CommentLinks deleteted <span class='good'>successfully</span> in DB for AFDID=". $givenAFDID;
            else
                $GLOBALS['log'] .= "</b><br/> CommentLinks <span class='bad'>ErrorDeleting</span> in DB for AFDID=".$givenAFDID;
            
            //$GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called Comment::removedAllCommentLink_ByAFDID(No.:". $conn->affected_rows.")*******************</span>";
            
            closeDBConnection($conn,$conn_NeedToClose);
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    static function loadFromDBByCommentLinkID($commentLinkID , $conn="")
    {
        $commentLink = -1; 
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call comment::loadFromDBByAFDID() </span>";
        try{
            $commentLinkID = trim($commentLinkID);
            
            if (!$commentLinkID)  
                throw new Exception('commentLinkID is empty!');
            
            $conn_NeedToClose = false;
            openDBConnection($conn, $conn_NeedToClose);
            
            $sql = "SELECT *
                    FROM `commentLink` 
                    where commentLinkID = '". mysql_real_escape_string($commentLinkID)."'";
                         
            if ($result=mysqli_query($conn,$sql))
            {
                if ($obj=mysqli_fetch_object($result))
                {
                    $commentLink = new CommentLink( $obj->commentID, $obj->AFDID, $obj->link_Html, $conn);
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
        return $commentLink;
    }
    
    private function insert_CommentLink()
    {
        //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call CommentLink->insert_CommentLink() </span>";
         
        try{
            if (!$this->commentID)
                throw new Exception('commentID is Null!');
            if (!$this->AFDID)
                throw new Exception('AFDID is Null!');
            if (!$this->link_Html)
                throw new Exception('link_Html is Null!');
            
            $this->link_Html = trim($this->link_Html);
            
            $conn_NeedToClose = false;
            openDBConnection($this->conn, $conn_NeedToClose);
            
            $sql = "INSERT INTO `commentLink` ( commentID, AFDID, link_Html, giveComment_Html, link_Sentance, link_Title, link_Label, link_URL, link_URLPosition, link_User, link_DateTime, link_External, link_Policy, link_Class, link_PolarityGrade, link_PolarityKeyword, link_ToOtherUser) 
            VALUES ( '$this->commentID', '". mysql_real_escape_string($this->AFDID)."', '". mysql_real_escape_string($this->link_Html)."', '". mysql_real_escape_string($this->giveComment_Html)."', '". mysql_real_escape_string($this->link_Sentance)."', '". mysql_real_escape_string($this->link_Title)."', '". mysql_real_escape_string($this->link_Label)."', '". mysql_real_escape_string($this->link_URL)."', '". mysql_real_escape_string($this->link_URLPosition)."', '". mysql_real_escape_string($this->link_User)."', '". $this->link_DateTime ."', '". $this->link_External ."',  '". $this->link_Policy ."', '". $this->link_Class ."', '". $this->link_PolarityGrade ."', '". $this->link_PolarityKeyword ."', '". $this->link_ToOtherUser ."' )";
            
            //$GLOBALS['log'] .= "<br/>sql: $sql";
            if (mysqli_query($this->conn, $sql)) {
                $this->commentLinkID = $this->conn->insert_id;
                //$GLOBALS['log'] .= "<br/> CommentLink Inserted <span class='good'>successfully</span> to DB AFDID=". $this->AFDID.", commentID=$this->commentID, commentLinkID=$this->commentLinkID";
            } else {
                $GLOBALS['log'] .= "<br/> CommentLink <span class='bad'>Failed</span> to insert to DB";
                $GLOBALS['log'] .= "<br/> <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
            
            closeDBConnection($this->conn, $conn_NeedToClose);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
                
    function update_CommentLink()
    {
        try{
            if (!$this->commentID)
                throw new Exception('commentID is Null!');
            if (!$this->AFDID)
                throw new Exception('AFDID is Null!');
            if (!$this->link_Html)
                throw new Exception('link_Html is Null!');
            
            $this->link_Html = trim($this->link_Html);
             
            $sql = "update `commentLink`
            set commentID = '". mysql_real_escape_string($this->commentID)."', 
            AFDID = '". mysql_real_escape_string($this->AFDID)."', 
            link_Html = '". mysql_real_escape_string($this->link_Html)."', 
            giveComment_Html = '". mysql_real_escape_string($this->giveComment_Html)."',
            link_Sentance = '". mysql_real_escape_string($this->link_Sentance)."',
            link_Title = '". mysql_real_escape_string($this->link_Title)."', 
            link_Label = '". mysql_real_escape_string($this->link_Label)."', 
            link_URL = '". mysql_real_escape_string($this->link_URL)."', 
            link_URLPosition = '". mysql_real_escape_string($this->link_URLPosition)."', 
            link_User = '". mysql_real_escape_string($this->link_User)."', 
            link_DateTime = '". mysql_real_escape_string($this->link_DateTime)."', 
            link_External = '". mysql_real_escape_string($this->link_External)."',
            link_Policy = '". mysql_real_escape_string($this->link_Policy)."',
            link_Class = '". mysql_real_escape_string($this->link_Class)."',
            link_PolarityGrade = '". mysql_real_escape_string($this->link_PolarityGrade)."',
            link_PolarityKeyword = '". mysql_real_escape_string($this->link_PolarityKeyword)."',
            link_ToOtherUser = '". mysql_real_escape_string($this->link_ToOtherUser)."'
            where commentLinkID = '". mysql_real_escape_string($this->commentLinkID)."'";
            
            //$GLOBALS['log'] .= "<br/>sql:$sql";
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> CommentLink updated (update_CommentLink) <span class='good'>successfully</span> to DB AFDID=$this->AFDID, commentLinkID=$this->commentLinkID";
            } else {
                $GLOBALS['log'] .= "<br/> CommentLink <span class='bad'>Failed</span> to update to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($this->conn). "</span>";
            }
            //$GLOBALS['log'] .= "<br/> CommentLink updated <span class='percentage'>CommentLink</span>$this->CommentLink<hr/>".$sql;
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
} 
?>