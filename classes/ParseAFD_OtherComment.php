<?php 
require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";
require_once "Signature.php";
require_once "DistinguishComments.php";
require_once "Comment.php";

class ParseAFD_OtherComment{
  
    public $givenAFDID;
    public $afd;
    public $debateDate;
    public $otherComment_Html;
   
    public $parsed_otherCommentError = 0;
    
    public $startParseTime = 0;
    public $endParseTime = 0;
    
    public $distinguishComments;
    
    public function ParseAFD_OtherComment($givenID) {
        $this->startParseTime = date('Y-m-d H:i:s');
        $GLOBALS['log'] .= "<hr/><span class='startCall'>****************** Call ParseAFD_OtherComment->ParseAFD_OtherComment() </span>";
        
        $this->afd = new AFD("","","");
        $this->afd->load_OtherComment_FromDB_ByAFDID($givenID);
        $this->debateDate = new DebateDate($this->afd->debateDateListID);
    
        $this->givenAFDID = $givenID;
        $this->otherComment_Html = $this->afd->otherComment_Html;
        $this->setLog();
        
        //call parse
        if( $this->afd->flag_toBeRemoved != 1)
            $this->parseAFD_Content();
        
        $this->endParseTime = date('Y-m-d H:i:s');
        
        $parseDuration = round( (strtotime($this->endParseTime) - strtotime($this->startParseTime)) / 3600 * 60, 2);

        //$GLOBALS['log'] .= "<br/> Parsed Duration: ".($parseDuration*60)." Sec.";
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_OtherComment->ParseAFD_OtherComment()*******************</span><hr/>";
    
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        flush();
    }
    
    function setLog() {
        $GLOBALS['log'] .=  "<br/> givenAFDID = $this->givenAFDID";
        $GLOBALS['log'] .=  "<br/> otherComment_Html = ". round((strlen($this->otherComment_Html)/1024),1)."KB";
    }
    
    function parseAFD_Content()
    {
        $GLOBALS['log'] .= "<br/> <span class='startCall'> ****************** Call ParseAFD_OtherComment->parseAFD_Content() <a target='_blank' <a href='getAFDHtmlByID.php?id=".$this->afd->AFDID."#".$this->afd->AFDTitleID."'>". $this->afd->AFDTitle."</a> </span>";
        
        try{
            if (!$this->afd)  
                throw new Exception('this->afd is empty!');
            
            //is allowed to parse the endresult details
            if($this->afd->flag_DoNotParse != 1)
            {
                $parse_otherComment = 0;
                $parse_otherComment_User = 0;
                $otherComment_Total = 0;
                
                $GLOBALS['log'] .=  closetags($this->otherComment_Html);
                $this->distinguishComments = new DistinguishComments($this->otherComment_Html, $this->debateDate->url);
                
                //Update AFD table
                $this->afd->otherComment_CounterTime = $this->distinguishComments->condition_time;
                $this->afd->otherComment_CounterDate = $this->distinguishComments->condition_date;
                $this->afd->otherComment_CounterUTC =  $this->distinguishComments->condition_UTC;
                $this->afd->otherComment_CounterUserNormal = $this->distinguishComments->condition_UserID1;
                $this->afd->otherComment_CounterUserTalk = $this->distinguishComments->condition_UserID2; 
                $this->afd->otherComment_CounterUserNew =  $this->distinguishComments->condition_UserID3; 
                $this->afd->otherComment_CounterUserIP =  $this->distinguishComments->condition_UserID4;
                $this->afd->parse_otherComment = $this->distinguishComments->distinguishPercentage;
                $this->afd->parse_otherComment_User = $this->distinguishComments->distinguishPercentage_User;
                
                $this->afd->updateAFD_OnlyOtherComment_byAFDID();
                
                //clear the comment table to remove the previous records to prevent dublication
                //also if there was previous mistake it make show the mistake does not effect over the quality of data
                Comment::removedAllComment_ByAFDID($this->afd->AFDID);
                
                //set the condition to insert into comment table
                $GLOBALS['log'] .=  "<hr style='border: 0; border-top: 1px solid gray;'/>";
                //set the (other)comment table
                for($i=0; $i < count($this->distinguishComments->array_time) ; $i++)
                {
                    $current_comment_Html = $this->distinguishComments->array_time[$i];
                    $current_comment_UserCheck = $this->distinguishComments->array_UTC_UserCheck[$i];
                    
                    //$comment_User and $comment_DateTime are "", this is due to further development
                    $comment = new Comment($this->afd->AFDID, $this->afd->AFDTitleID, $this->afd->debateDateListID, $current_comment_Html, "","", "");
                    
                    $GLOBALS['log'] .= "<table border='1'><tr><td>";
                    $signature = Signature::parse($current_comment_Html);
                    $GLOBALS['log'] .= "</td></tr></table>";
                    $beforeSignature = $signature->initialSentance;
                    $debate_included = $this->check_debate_included($beforeSignature);
                    $bBlock = $this->check_Comment_bBlock($beforeSignature);
                    
                    $comment->AFDTitle = $this->afd->AFDTitle;
                    $comment->articleID = $this->afd->articleID;
                    $comment->comment_UserCheck = $current_comment_UserCheck;
                    //liligago need to write for comment such as delete, keep and etc.
                    $comment->comment = $bBlock[1]; 
                    $comment->comment_User = $signature->userID;
                    $comment->comment_UserPosition = $signature->userID_StartPos;
                    $comment->comment_UserTitle = $signature->userTitle;
                    $comment->comment_UserURL = $signature->userURL;
                    $comment->comment_UserURLType = $signature->userURLType;
                    $comment->comment_Date = $signature->date;
                    $comment->comment_Time = $signature->time;
                    $comment->comment_DateTime = date('Y-m-d H:i:s', strtotime($signature->date." ".$signature->time));
                    $comment->comment_Note = $bBlock[2];
                    $comment->comment_ExtraNote = $debate_included[1];
                    
                    if( $bBlock[0]== 1 && $debate_included[0]==1)
                        $comment->comment_Type = 1;
                    else if( $bBlock[0]== 1 )
                        $comment->comment_Type = "1Error";
                    else if( $debate_included[0]== 1 )
                        $comment->comment_Type = "Flag_Text";
                    else
                        $comment->comment_Type = 0;
                        
                    $comment->comment_Type2 = $bBlock[3];
                    if( $this->distinguishComments->distinguishPercentage >= 80)
                        $comment->flag_DoNotVisualize_Comment = 0;
                    else
                        $comment->flag_DoNotVisualize_Comment = 1;
                    $comment->distinguishPercentage = $this->distinguishComments->distinguishPercentage;
                    
                    //if(!empty($comment->comment_Note))
                    //    $GLOBALS['log'] .="<br/><span class='percentage'> Comment Note:</span><br/> ".strip_tags($comment->comment_Note);
                    if( strlen($comment->comment_Type) == strlen("Flag_Text" ))
                        $GLOBALS['log'] .="<br/><span class='bad'> Flag_Text</span>";
                    
                    //update the comment table
                    $comment->update_Comment();
                    $GLOBALS['log'] .=  "<hr style='border: 0; border-top: 1px solid #FF3339;'/>";
                }
            }
            else
                $GLOBALS['log'] .="<br/><span class='startCall'> By pass by flag_DoNotParse = 1.</span>";
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_OtherComment->parseAFD_Content()*******************</span>";
    }
    
    // This debate has been included in the list of Science fiction-related deletion discussions.  
    function check_debate_included($givenSentance)
    {   
        $comment_ExtraNote ="";
        $condition_Type2 = preg_match_all("/(This debate has been included in the)/i", $givenSentance, $matches_Type2, PREG_OFFSET_CAPTURE);
        if($condition_Type2>0)
        {
            //$GLOBALS['log'] .= "<br/><span class='newFunction'>".$matches_Type2[0][0][1]."</span>";
            $linkedTo = substr($givenSentance, $matches_Type2[0][0][1]+strlen($matches_Type2[0][0][0]));
            ///wiki/Wikipedia:WikiProject_Deletion_sorting/Actors_and_filmmakers
            $condition_LinkedTo = preg_match_all("/href=[\"|\']\/wiki\/Wikipedia:WikiProject_Deletion_sorting\/(.*?)title/i", $linkedTo, $matches_LinkedTo, PREG_OFFSET_CAPTURE);
            $comment_ExtraNote = rtrim(substr($matches_LinkedTo[1][0][0],0,strlen($matches_LinkedTo[1][0][0])-2));
            $comment_TypeCheck1 = 0;
        }
        else
            $comment_TypeCheck1 = 1;
        
        return array($comment_TypeCheck1,$comment_ExtraNote);
    }
    
    //liligago need to just do <b></b> for main keywords, keywords need to define in the config.php
    // This debate has been included in the list of Science fiction-related deletion discussions.  
    function check_Comment_bBlock($givenSentance)
    {   
        $Comment_bBlock ="";
        $Comment_note ="";
        $macthKeyword ="";
        $condition_bBlock = preg_match_all("'<b>(.*?)</b>'i", $givenSentance, $matches_bBlock, PREG_OFFSET_CAPTURE);
        if($condition_bBlock>0)
        {
            $emptyCheck = trim($matches_bBlock[1][0][0]);
            if(!empty($emptyCheck))
            {
                $Comment_bBlock = $emptyCheck;
                
                $comment_Type1 = 1;
                $Comment_note  = substr($givenSentance, $matches_bBlock[0][0][1]+ strlen($matches_bBlock[0][0][0]));
                
                //calculate the Type2 or in other words, find the Macthed Keyword
                if ( preg_match("/(Weak Delete)/i",$Comment_bBlock) )
                    $macthKeyword="Weak Delete";
                else if ( preg_match("/(Speedy Delete)/i",$Comment_bBlock) )
                    $macthKeyword="Speedy Delete";
                else if ( preg_match("/(Strong Delete)/i",$Comment_bBlock) )
                    $macthKeyword="Strong Delete";
                else if ( preg_match("/(Delete)/i",$Comment_bBlock) )
                    $macthKeyword="Delete";
                else if ( preg_match("/(Weak Keep)/i",$Comment_bBlock) )
                    $macthKeyword="Weak Keep";
                else if ( preg_match("/(Strong Keep)/i",$Comment_bBlock) )
                    $macthKeyword="Strong Keep";
                else if ( preg_match("/(Keep)/i",$Comment_bBlock) )
                    $macthKeyword="Keep";
                else if ( preg_match("/(Withdraw)/i",$Comment_bBlock) )
                    $macthKeyword="Withdraw";
                else if ( preg_match("/(Redirected and Merged)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect and Merge";
                else if ( preg_match("/(Merged and Redirected)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect and Merge";
                else if ( preg_match("/(Redirected(| )\/(| )Merged)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect and Merge";
                else if ( preg_match("/(Redirected)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect";
                else if ( preg_match("/(Merged)/i",$Comment_bBlock) )
                    $macthKeyword="Merge";
                else if ( preg_match("/(Redirect and Merge)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect and Merge";
                else if ( preg_match("/(Merge and Redirect)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect and Merge";
                else if ( preg_match("/(Redirect(| )\/(| )Merge)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect and Merge";
                else if ( preg_match("/(Redirect)/i",$Comment_bBlock) )
                    $macthKeyword="Redirect";
                else if ( preg_match("/(Merge)/i",$Comment_bBlock) )
                    $macthKeyword="Merge";
                else if ( preg_match("/(Userfy)/i",$Comment_bBlock) )
                    $macthKeyword="Userfy";
                else if ( preg_match("/(Speedy decline)/i",$Comment_bBlock) )
                    $macthKeyword="Speedy decline";
                else if ( preg_match("/(Reviews)/i",$Comment_bBlock) )
                    $macthKeyword="Reviews";
                else if ( preg_match("/(closing administrator)/i",$Comment_bBlock) )
                    $macthKeyword="closing administrator";
                else if ( preg_match("/(Comment)/i",$Comment_bBlock) )
                    $macthKeyword="Comment";
                else if ( preg_match("/(Note)/i",$Comment_bBlock) )
                    $macthKeyword="Note";
                else if ( preg_match("/(Relisted)/i",$Comment_bBlock) )
                    $macthKeyword="Relisted";
                else if ( preg_match("/(Move and dab)/i",$Comment_bBlock) )
                    $macthKeyword="Move and dab";
                else if ( preg_match("/(Move)/i",$Comment_bBlock) )
                    $macthKeyword="Move";
                else if ( preg_match("/(Oppose)/i",$Comment_bBlock) )
                    $macthKeyword="Oppose";
                else if ( preg_match("/(Question)/i",$Comment_bBlock) )
                    $macthKeyword="Question";
                else if ( preg_match("/(Speedy close)/i",$Comment_bBlock) )
                    $macthKeyword="Speedy close";
            }
            else
                $comment_Type1 = 0;
       }
            else
                $comment_Type1 = 0;
        
        return array($comment_Type1,$Comment_bBlock,$Comment_note,$macthKeyword);
    }              
}
?>