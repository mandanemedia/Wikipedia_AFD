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
require_once "ParseAFD_CommentPolarity.php";

class ParseAFD_CommentLink{
    public $givenAFDID;
    public $afd;
    public $afd_Comments;
    public $linkCounter = 0;
    public $links;
    
    public $startParseTime = 0;
    public $endParseTime = 0;
    
    public function ParseAFD_CommentLink($givenID) {
        $this->startParseTime = date('Y-m-d H:i:s');
        $GLOBALS['log'] .= "<hr/><span class='startCall'>****************** Call ParseAFD_CommentLink->ParseAFD_CommentLink() </span>";
        
        $this->givenAFDID = trim($givenID);
        $this->afd = AFD::load_DBObject_ByAFDID($this->givenAFDID);
        $this->afd_Comments = Comment::load_DBObject_ByAFDID($this->givenAFDID);
        $this->links = array();
        
        //$this->setLog();
        
        //call parse
        if( $this->afd->flag_toBeRemoved != 1)
            $this->ParseAFD_CommentLink_Content();
        
        $this->endParseTime = date('Y-m-d H:i:s');
        
        $parseDuration = round( (strtotime($this->endParseTime) - strtotime($this->startParseTime)) / 3600 * 60, 2);

        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_CommentLink->ParseAFD_CommentLink()*******************</span><hr/>";
    
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        flush();
    }
    
    
    function setLog() {
        $GLOBALS['log'] .=  "<br/> givenAFDID = $this->givenAFDID";
        $GLOBALS['log'] .=  "<br/> AFD_endResult = " . closetags($this->afd->endResult_Html);
        $GLOBALS['log'] .=  "<br/> AFD_MainComment = " .  closetags($this->afd->mainComment_Html);
        /*for ($i=0; $i<count($this->afd_Comments); $i++)
        {
            $GLOBALS['log'] .=  "<hr/>";
            $comment = $this->afd_Comments[$i];
            $GLOBALS['log'] .=  "<br/> AFD_Comment[".$i."], commentID=".$comment->commentID." : " . closetags($comment->comment_Html);
        }*/
        $GLOBALS['log'] .=  "<hr/>";
    }
    
    function ParseAFD_CommentLink_Content()
    {
        $GLOBALS['log'] .= "<br/> <span class='startCall'> ****************** Call ParseAFD_CommentLink->ParseAFD_CommentLink_Content() <a target='_blank' <a href='getAFDHtmlByID.php?id=".$this->afd->AFDID."#".$this->afd->AFDTitleID."'>". $this->afd->AFDTitle."</a> </span>";
        try{
            if (!$this->afd)  
                throw new Exception('this->afd is empty!');
            
            //is allowed to parse the endresult details
            if($this->afd->flag_DoNotParse != 1)
            {
                CommentLink::removedAllCommentLink_ByAFDID($this->afd->AFDID);
                
                //1. check endResult -2
                $endResult_Links = $this->check_link($this->afd->endResult_Note, -2 , $this->afd->endResult_DateTime);
                $this->storeInDB(-2, $endResult_Links, $this->afd->endResult_User, $this->afd->endResult_DateTime);
                
                
                //2. check MainComment -1
                $mainComment_Links = $this->check_link($this->afd->mainComment_Note, -1 , $this->afd->mainComment_DateTime);
                $this->storeInDB(-1, $mainComment_Links, $this->afd->mainComment_User, $this->afd->mainComment_DateTime);
                
                //3. check OtherComment.. commentID
                foreach($this->afd_Comments as $afd_comments_each)
                {
                    //either start with delete and etc(comment_Type=1), or there are just note(comment_Type=Flag_Text)
                    //it baypass the the relisted type that in (comment_Type=0)
                    if( $afd_comments_each->comment_Type != 0)
                    {
                        //$comment_Html_NoSigniture = substr($afd_comments_each->comment_Html, 0 , $afd_comments_each->comment_UserPosition -20  ); // <a href=
                        $comment_Html_NoSigniture = $afd_comments_each->comment_Note;
                        //$GLOBALS['log'] .= "<br/><br/><span> xxx ".$afd_comments_each->comment_Note." L:".strlen($afd_comments_each->comment_Note). " yyyy </span><br/>";
                        $otherComment_Links = $this->check_link($comment_Html_NoSigniture, $afd_comments_each->commentID , $afd_comments_each->comment_DateTime);
                        $this->storeInDB($afd_comments_each->commentID, $otherComment_Links, $afd_comments_each->comment_User, $afd_comments_each->comment_DateTime);
                    }
                }
                
            }
            else
                $GLOBALS['log'] .="<br/><span class='startCall'> By pass by flag_DoNotParse = 1.</span>";
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        //$GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD_OtherComment->parseAFD_Content()*******************</span>";
    }
    
    private function storeInDB($giveCommentID , $mainComment_Links, $giveUser, $givenDateTime )
    {
        foreach($mainComment_Links as $mainComment_EachLink)
        {
            //$mainComment_Links[] = array($link_Html[0], $link_Title[1], $link_Label[2], $link_URL[3], $link_URLPosition[4], $link_External[5], $link_Policy[6], $link_Class[7], $link_ToOtherUser[8], $givenHtml[10]]);
            $commentLink = new CommentLink($giveCommentID, $this->afd->AFDID, $mainComment_EachLink[0]);
            
            $commentLink->link_Title = $mainComment_EachLink[1];
            $commentLink->link_Label = $mainComment_EachLink[2];
            $commentLink->link_URL = $mainComment_EachLink[3];
            $commentLink->link_URLPosition = $mainComment_EachLink[4]; //link_URLPosition
            $commentLink->link_User = $giveUser;
            $commentLink->link_DateTime = $givenDateTime;
            $commentLink->link_External = $mainComment_EachLink[5];
            $commentLink->link_Policy = $mainComment_EachLink[6];
            $commentLink->link_Class = $mainComment_EachLink[7];
            //$commentLink->link_Negative =  Furtehr Investigation 
            //http://www.grammarly.com/handbook/sentences/negatives/
            $commentLink->link_ToOtherUser = $mainComment_EachLink[8];
            
            //Polarity
            $commentLink->link_Sentance = ParseAFD_CommentPolarity::seprateSentenceByPositionID($mainComment_EachLink[9], $commentLink->link_URLPosition, $commentLink->link_URL);                                    
            $commentLink->giveComment_Html = $mainComment_EachLink[9];
            //$GLOBALS['log'] .= "<br/><br/><span> xxx ".$commentLink->link_Sentance." L:".strlen($commentLink->link_Sentance). " yyyy </span><br/>";
            $polarity = ParseAFD_CommentPolarity::polarity($commentLink->link_Sentance);
            $commentLink->link_PolarityGrade = $polarity[0];
            $commentLink->link_PolarityKeyword = $polarity[1];
            
            $commentLink->update_CommentLink();
        }
        $GLOBALS['log'] .= "<hr/>";
    }
    private function check_link($givenSentance, $commentID, $link_DateTime)
    {   
        $condition_Link = preg_match_all("/<a(.*?)<\/a>/i", $givenSentance, $matches_Link, PREG_OFFSET_CAPTURE);
        $output = array();
        $GLOBALS['log'] .= "<br/><span class='givenSentance'>".closetags($givenSentance)."</span>"; 
        
        if($condition_Link>0)
        {
            for($i=0 ; $i <count($matches_Link[0]); $i++ )
            {
                $condition_Title = "";
                $link_Label = "";
                $link_URL = "";
                $link_External = "";
                $link_Class = "";
                $link_Title = "";
                $link_Policy = "";
                $link_ToOtherUser = "";
                $link_URLPosition = -1 ;
                
                //html
                $link_Html = $matches_Link[0][$i][0];
                
                //title
                $condition_Title = preg_match_all("/title=[\"|\'](.*?)[\"|\']/i", $matches_Link[0][$i][0], $matches_Title , PREG_OFFSET_CAPTURE);
                if($condition_Title) $link_Title = $matches_Title [1][0][0];
                
                //link_Label
                $condition_Label= preg_match_all("/>(.*?)<\/a>/i", $matches_Link[0][$i][0], $matches_Label , PREG_OFFSET_CAPTURE);
                if($condition_Label) $link_Label = $matches_Label [1][0][0];
                
                //URL
                $condition_URL = preg_match_all("/href=[\"|\'](.*?)[\"|\']/i", $matches_Link[0][$i][0], $matches_URL , PREG_OFFSET_CAPTURE);
                if($condition_URL) {
                    $link_URL = $matches_URL [1][0][0];
                    $link_URLPosition = $matches_URL [1][0][1] + $matches_Link[0][$i][1] ;
                }
                
                //$link_External
                $condition_External_1 = preg_match_all("/wikipedia(.*?)/i", $link_URL , $matches_External_1 , PREG_OFFSET_CAPTURE);
                if($condition_External_1) $link_External = 0;
                else
                {
                   $condition_External_2 = preg_match_all("/wiki(.*?)/i", $link_URL , $matches_External_2 , PREG_OFFSET_CAPTURE);
                   if($condition_External_2) $link_External = 0;
                   else {
                       $condition_External_3 = preg_match_all("/w\/index.php(.*?)/i", $link_URL , $matches_External_3 , PREG_OFFSET_CAPTURE);
                       if($condition_External_3)
                            $link_External = 0;
                       else 
                            $link_External = 1;
                   }
                }
                
                //link_Policy
                if(!preg_match_all("/index.php(.*?)$/i", $link_URL , $matches_Policy , PREG_OFFSET_CAPTURE))
                    if(!preg_match_all("/Articles_for_deletion(.*?)$/i", $link_URL , $matches_Policy , PREG_OFFSET_CAPTURE))
                    {
                        if ( strpos($link_URL,"#")  === false )
                            $link_URL_RemovedBookMark = $link_URL ;
                        else
                            $link_URL_RemovedBookMark = strstr($link_URL, '#', true);
                        
                        $condition_Policy = preg_match_all("/Wikipedia:(.*?)$/i", $link_URL_RemovedBookMark , $matches_Policy , PREG_OFFSET_CAPTURE);
                        if($condition_Policy) $link_Policy = ($matches_Policy[0][0][0]);
                    }
                
                //link_Class
                $condition_Class  = preg_match_all("/class=[\"|\'](.*?)[\"|\']/i", $matches_Link[0][$i][0], $matches_Class, PREG_OFFSET_CAPTURE);
                if($condition_Class) $link_Class = $matches_Class[1][0][0];
                
                //link_ToOtherUser
                $condition_ToOtherUser = preg_match_all("/User\:(.*?)$/i", $link_URL, $matches_ToOtherUser , PREG_OFFSET_CAPTURE);
                if($condition_ToOtherUser) $link_ToOtherUser = $matches_ToOtherUser [1][0][0];
                else
                {
                    $condition_ToOtherUser2 = preg_match_all("/User_talk\:(.*?)$/i", $link_URL, $matches_ToOtherUser2 , PREG_OFFSET_CAPTURE);
                    if($condition_ToOtherUser2) $link_ToOtherUser = $matches_ToOtherUser2 [1][0][0];
                    else
                    {
                        $condition_ToOtherUser3 = preg_match_all("/Special\:Contributions\/(.*?)$/i", $link_URL, $matches_ToOtherUser3 , PREG_OFFSET_CAPTURE);
                        if($condition_ToOtherUser3) $link_ToOtherUser = $matches_ToOtherUser3 [1][0][0];
                    }
                }
                
                //To pass to the Seprate Sentance from html                 
                $givenHtml = $givenSentance;
                                                
                $output[]= array($link_Html, $link_Title, $link_Label, $link_URL, $link_URLPosition, $link_External, $link_Policy, $link_Class, $link_ToOtherUser, $givenHtml);
                //$GLOBALS['log'] .= "<br/><span class='percentage'> ------- </span>".$matches_Link[1][$i][0]."<br/>link_Title=$link_Title, <br/>link_Label=$link_Label, <br/>link_URL=$link_URL, <br/>link_External=$link_External, <br/>link_Policy=$link_Policy, <br/>link_Class=$link_Class";
            }
            
            //display
            $GLOBALS['log'] .= "<table border='1'>";
            $GLOBALS['log'] .= "<tr><td><span class='percentage'>link_Title</span></td><td><span class='percentage'>link_Label</span></td><td><span class='percentage'>link_URL</span></td><td><span class='percentage'>link_External</span></td><td><span class='percentage'>link_Policy</span></td><td><span class='percentage'>link_Class</span></td><td><span class='percentage'>link_ToOtherUser</span></td></tr>";
            foreach($output as $output_each)
            {
                    $GLOBALS['log'] .= "<tr><td>$output_each[1]</td><td>$output_each[2]</td><td>$output_each[3]</td><td>$output_each[4]</td><td>$output_each[5]</td><td>$output_each[6]</td><td>$output_each[7]</td></tr>";
            }
            $GLOBALS['log'] .= "</table>";
        }
        else
            $GLOBALS['log'] .= "<br/><span class='percentage'>No Link has been found.</span>(".strlen($givenSentance).")";
        
        return $output;
    }
}
?>