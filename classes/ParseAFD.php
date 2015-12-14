<?php 
require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";

class ParseAFD{
    
    public $givenAFDID;
    public $afd;
    public $afdURL;
    public $givenHTML;
   
    public $parsed_resultError = 0;
    public $parsed_CommentError = 0;
    public $totalParsed_Comment = 0;
    
    public $startParseTime = 0;
    public $endParseTime = 0;
    
    public function ParseAFD($givenID) {
        $this->startParseTime = date('Y-m-d H:i:s');
        $GLOBALS['log'] .= "<hr/><span class='startCall'>****************** Call ParseAFD->ParseAFD() </span>";
        
        $this->afd = AFD::loadFromDBByAFDID($givenID);
        $this->givenAFDID = $givenID;
        $this->afdURL = $this->afd->AFDURL;
        $this->givenHTML = $this->afd->AFDHTML;
        $this->setLog();
        
        //call parse
        if( $this->afd->flag_toBeRemoved != 1)
            $this->parseAFDContent();
        
        $this->endParseTime = date('Y-m-d H:i:s');
        
        $parseDuration = round( (strtotime($this->endParseTime) - strtotime($this->startParseTime)) / 3600 * 60, 2);
        //$GLOBALS['log'] .= "<br/> this->parsed_resultError=$this->parsed_resultError ";
        //$GLOBALS['log'] .= "<br/> this->parsed_CommentError=$this->parsed_CommentError ";
        //$GLOBALS['log'] .= "<br/> this->totalParsed_Comment=$this->totalParsed_Comment ";
        $GLOBALS['log'] .= "<br/> Parsed Duration: ".($parseDuration*60)." Sec.";
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD->ParseAFD()*******************</span>";
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
    }
    
    function setLog() {
        $GLOBALS['log'] .=  "<br/> givenAFDID = $this->givenAFDID";
        $GLOBALS['log'] .=  "<br/> afdURL = <a target='_blank' href='https://en.wikipedia.org$this->afdURL'>https://en.wikipedia.org$this->afdURL</a>";
        $GLOBALS['log'] .=  "<br/> givenHTML = ". round((strlen($this->givenHTML)/1024),1)."KB";
    }
    
    function parseAFDContent() {
        
        $GLOBALS['log'] .= "<br/> <span class='startCall'> ****************** Call ParseAFD->parseAFDContent() <a target='_blank' <a href='GetAFDListbyDebateDateListID.php?DebateDateListID=".$this->afd->debateDateListID."#".$this->afd->AFDTitleID."'>". $this->afd->AFDTitle."</a> </span>";
        try{
            $dom = new simple_html_dom();
            $dom->load($this->givenHTML, false);
            
            $i=0;
            //main section
            foreach($dom->find('div.afd') as $div_afd) { 
                $i++;
                
                //get the result of discussion
                $endResult_Html = $div_afd->find("p", 0);
                
                $flag_error="";
                //echo $div_afd;
                $result = ereg("^The", $endResult_Html->plaintext);
                $result_2 = ereg("UTC)", $endResult_Html->plaintext);
                             
                $container_nourlexpansion = "";
                $container_sibiling_index = 1;
                $container_nourlexpansion_temp =  getNextSibling($div_afd->find("p", 0),$container_sibiling_index);//1
                $checking = ereg("talk", $container_nourlexpansion_temp->plaintext);
                $checking_2 = ereg("watch", $container_nourlexpansion_temp->plaintext);
                $checking_3 = ereg("views", $container_nourlexpansion_temp->plaintext);
                while(!($checking != 0 && $checking_2 != 0 && $checking_3 != 0))
                {
                    ++$container_sibiling_index;
                    $container_nourlexpansion_temp =  getNextSibling($div_afd->find("p", 0),$container_sibiling_index);//2..n
                    $checking = ereg("talk", $container_nourlexpansion_temp->plaintext);
                    $checking_2 = ereg("watch", $container_nourlexpansion_temp->plaintext);
                    $checking_3 = ereg("views", $container_nourlexpansion_temp->plaintext);
                    
                    if($container_sibiling_index >7)
                    {
                        $GLOBALS['log'] .= "<br/><span class='bad'>Error in MainParsing (container_sibiling_index=$container_sibiling_index)</span>";
                        break;                         
                    }
                }
                if($checking != 0 && $checking_2 != 0 && $checking_3 != 0)
                    $container_nourlexpansion =  getNextSibling($div_afd->find("p", 0),$container_sibiling_index);//1..n
                
                //Add extraNote to endResult as well as fix the bug of endResult
                $endResult_ExtraNote = "";
                for($endresult_i=1; $endresult_i < $container_sibiling_index-1 ; $endresult_i++ )
                    $endResult_Html .= getNextSibling($div_afd->find("p", 0),$endresult_i);
                    
                $endResult_SeprateCondition = preg_match_all ("/\((UTC)\)/", $endResult_Html, $matches_endResultSeprate, PREG_OFFSET_CAPTURE);
                $matches_endResultSeprate_0 = $matches_endResultSeprate[0];
                if( count($matches_endResultSeprate_0) >0 )
                { 
                    $result_2 = 1;
                    $endResult_Html_temp = substr($endResult_Html,0, $matches_endResultSeprate_0[0][1]+5 );
                    $endResult_ExtraNote = substr($endResult_Html, $matches_endResultSeprate_0[0][1]+5 ,  strlen($endResult_Html) - $matches_endResultSeprate_0[0][1]+5  );
                }
                else
                {
                    $endResult_Html_temp = $endResult_Html;
                }
                // such as . or '' might be in the end that would be removed.
                if(strlen(trim($endResult_ExtraNote))<6)
                    $endResult_ExtraNote = "";
                    
                $endResult_Html = $endResult_Html_temp;
                
                if( $result_2 == 0 )
                {
                    $this->parsed_resultError++;
                    $GLOBALS['log'] .= "<div class='bad'> Result failed. (parsedError = True)</div>  debateDateListID=<a target='_blank' href='getHtmlByID.php?id=".$this->afd->debateDateListID."'>".$this->afd->debateDateListID."</a>, AFDID=<a target='_blank' href='getAFDHtmlByID.php?id=".$this->afd->AFDID."'>". $this->afd->AFDID."</a>";
                    $parse_e_result_s = 0;
                    $parse_e_result_e = 0;
                }
                else
                {
                    $GLOBALS['log'] .= "<div class='good'> Result Matched. </div>  debateDateListID=<a target='_blank' href='getHtmlByID.php?id=".$this->afd->debateDateListID."'>".$this->afd->debateDateListID."</a>, AFDID=<a target='_blank' href='getAFDHtmlByID.php?id=".$this->afd->AFDID."'>". $this->afd->AFDID."</a>";
                    $parse_e_result_s = 1;
                    $parse_e_result_e = 1;
                }
                
                //check end result now
                $GLOBALS['log'] .=  "<div class='percentage'>After Result Sibiling index: $container_sibiling_index </div>";
                $GLOBALS['log'] .=  "<div class='percentage'> endResult_Html:</div> $endResult_Html";
                $GLOBALS['log'] .=  "<div class='percentage'> endResult_ExtraNote:</div> $endResult_ExtraNote";
                
                
                //Main Comment Secetion
                $sibling_index=0;
                $mainComment_Html = "";
                $otherComment_Html = "";
                ++$sibling_index;
                $mainComment_Html_temp =  getNextSibling($container_nourlexpansion,$sibling_index);//1
                $mainComment_EndCondition = preg_match_all ("/\((UTC)\)/", $mainComment_Html_temp, $matches_mainComment, PREG_OFFSET_CAPTURE);
                
                $mainComment_Html_temp_2 = "";
                //+++++ fix the bug when have multiple (UTC) in $mainComment_Html_temp and add the rest (2..n) to the other_comments
                if($mainComment_EndCondition > 1 )
                {
                    $matches_mainComment_array= $matches_mainComment[0];
                    for($substring_i=0;   $substring_i< count($matches_mainComment_array); $substring_i++ )
                    {
                        if($substring_i == 0)
                        {
                            $start_position = 0;
                            $end_position = $matches_mainComment_array[$substring_i][1] - $start_position +5 ;
                            //for 1st occurence 
                            $mainComment_Html_temp_2 = substr($mainComment_Html_temp,$start_position, $end_position );
                        }
                        else
                        {
                            $start_position = $matches_mainComment_array[$substring_i-1][1] + 5;
                            $end_position = $matches_mainComment_array[$substring_i][1] - $start_position +5 ;
                            //for 2nd to n occurence 
                            $otherComment_Html .= substr($mainComment_Html_temp,$start_position, $end_position );
                        }
                       //echo "substring_i=$substring_i($start_position,".($matches_mainComment_array[$substring_i][1]+5).")<br/>". substr($mainComment_Html_temp,$start_position, $end_position )." <br/><hr/> ";
                    }
                    $flag_error ='check';
                }//---- fix the bug when have multiple (UTC) in $mainComment_Html_temp and add the rest (2..n) to the other_comments
                if(empty($mainComment_Html_temp_2))
                    $mainComment_Html = $mainComment_Html_temp;
                else
                    $mainComment_Html .=$mainComment_Html_temp_2 ;
                        
                while ( $mainComment_EndCondition == 0 )
                {
                    ++$sibling_index;
                    $mainComment_Html_temp =  getNextSibling($container_nourlexpansion, $sibling_index);//2..n
                    
                    //echo "<div class='newFunction'> check sibling_index=$sibling_index<br/>$mainComment_Html_temp</div>";
                    //$mainComment_EndCondition = ereg("UTC)", trim(strip_tags($mainComment_Html)));
                    //New Way for calculation 
                    $mainComment_EndCondition = preg_match_all ("/\((UTC)\)/", $mainComment_Html_temp, $matches_mainComment, PREG_OFFSET_CAPTURE);
                    
                    $mainComment_Html_temp_2 = "";
                    //+++++ fix the bug when have multiple (UTC) in $mainComment_Html_temp and add the rest (2..n) to the other_comments
                    if($mainComment_EndCondition > 1 )
                    {
                        $matches_mainComment_array= $matches_mainComment[0];
                        //echo $mainComment_Html."<hr/>SSSS";
                        for($substring_i=0;   $substring_i< count($matches_mainComment_array); $substring_i++ )
                        {
                            if($substring_i == 0)
                            {
                                $start_position = 0;
                                $end_position = $matches_mainComment_array[$substring_i][1] - $start_position +5 ;
                                //for 1st occurence 
                                $mainComment_Html_temp_2 = substr($mainComment_Html_temp,$start_position, $end_position );
                            }
                            else
                            {
                                $start_position = $matches_mainComment_array[$substring_i-1][1] + 5;
                                $end_position = $matches_mainComment_array[$substring_i][1] - $start_position +5 ;
                                //for 2nd to n occurence 
                                $otherComment_Html .= substr($mainComment_Html_temp,$start_position, $end_position );
                            }
                            //echo "substring_i=$substring_i($start_position,".($matches_mainComment_array[$substring_i][1]+5).")<br/>". substr($mainComment_Html_temp,$start_position, $end_position )." <br/><hr/> ";
                        }
                        $flag_error ='check';
                    }//---- fix the bug when have multiple (UTC) in $mainComment_Html_temp and add the rest (2..n) to the other_comments
                    
                    if(empty($mainComment_Html_temp_2))
                        $mainComment_Html = $mainComment_Html_temp;
                    else
                        $mainComment_Html .=$mainComment_Html_temp_2 ;
                    
                    if($sibling_index == 10)
                    {
                        throw new Exception('There is record in DB!');
                    }
                }
                
                if( $sibling_index> 1 )
                {
                    echo "<div class='newFunction'> from 2 to $sibling_index for (sibling_index) </div>";
                    for($mainComment_i=2; $mainComment_i < $sibling_index; $mainComment_i++ )
                        $mainComment_Html .= getNextSibling( $container_nourlexpansion, $mainComment_i);
                }
                
                //Add extraNote to endResult as well as fix the bug of endResult
                $mainComment_ExtraNote = "";
                /*$mainComment_ExtraSeprateCondition = preg_match_all ("/\((UTC)\)/", $mainComment_Html, $matches_mainCommentSeprate, PREG_OFFSET_CAPTURE);
                $matches_mainCommentSeprate_0 = $matches_mainCommentSeprate[0];
                $mainComment_Html_temp = substr($mainComment_Html,0, $matches_mainCommentSeprate_0[0][1]+5 );
                $mainComment_ExtraNote = substr($mainComment_Html, $matches_mainCommentSeprate_0[0][1]+5 ,  strlen($mainComment_Html) - $matches_mainCommentSeprate_0[0][1]+5  );
                $mainComment_Html = $mainComment_Html_temp;
                */
                
                $otherComment_Html_pre = $otherComment_Html;
                //other Comment Secetion
                $otherComment_Html = "";
                $sibling_index_otherComment=$sibling_index;
                ++$sibling_index_otherComment;
                $otherComment_Html .=  getNextSibling($container_nourlexpansion, $sibling_index_otherComment);//1
                $otherComment_EndCondition = ereg("No further edits should be made to this page", trim(strip_tags($otherComment_Html)));
                $extra_i=0;
                
                // other comment is not empty
                if( $otherComment_EndCondition == 0 )
                {
                    //$GLOBALS['log'] .=  "<br/><span class='percentage'> otherComment_EndCondition=$otherComment_EndCondition, sibling_index_otherComment=$sibling_index_otherComment</span>";
                    $flag_otherComment_empty=0;
                    $otherComment_Html_next =  getNextSibling($container_nourlexpansion, $sibling_index_otherComment+1);//2
                    $otherComment_EndCondition = ereg("No further edits should be made to this page", trim(strip_tags($otherComment_Html_next)));
                    while ( $otherComment_EndCondition == 0 )
                    {
                        ++$sibling_index_otherComment;
                        $otherComment_Html .=  getNextSibling($container_nourlexpansion, $sibling_index_otherComment);//1
                        $otherComment_Html_next =  getNextSibling($container_nourlexpansion, $sibling_index_otherComment+1);//2
                        $otherComment_EndCondition = ereg("No further edits should be made to this page", trim(strip_tags($otherComment_Html_next)));
                        
                        if($sibling_index == 30)
                        {
                            throw new Exception('There is record in DB!');
                        }
                    }
                    
                    //add extra note before the very end of other comment ( before "No further edit should be made to this page")
                    $otherComment_Html_next_dd = find_innder_dd($otherComment_Html_next);
                    $otherComment_EndCondition_dd = ereg("No further edits should be made to this page", trim(strip_tags($otherComment_Html_next_dd)));
                    while ( $otherComment_EndCondition_dd == 0 )
                    {
                        $extra_i++;
                        $otherComment_Html .= $otherComment_Html_next_dd;
                        $otherComment_Html_next = str_replace($otherComment_Html_next_dd,"",$otherComment_Html_next);
                        $otherComment_Html_next_dd = find_innder_dd($otherComment_Html_next);
                        $otherComment_EndCondition_dd = ereg("No further edits should be made to this page", trim(strip_tags($otherComment_Html_next_dd)));
                    }
                }
                // other comment is almost empty
                else
                {
                    //$GLOBALS['log'] .=  "<br/><span class='percentage'> (empty) otherComment_EndCondition=$otherComment_EndCondition, sibling_index_otherComment=$sibling_index_otherComment</span>";
                    $otherComment_Html_next = $otherComment_Html;
                    $otherComment_Html = "";
                    //add extra note before the very end of other comment ( before "No further edit should be made to this page")
                    $otherComment_Html_next_dd = find_innder_dd($otherComment_Html_next);
                    $otherComment_EndCondition_dd = ereg ("No further edits should be made to this page", trim(strip_tags($otherComment_Html_next_dd)));
                    while ( $otherComment_EndCondition_dd == 0 )
                    {
                        $extra_i++;
                        $otherComment_Html .= $otherComment_Html_next_dd;
                        $otherComment_Html_next = str_replace($otherComment_Html_next_dd,"",$otherComment_Html_next);
                        $otherComment_Html_next_dd = find_innder_dd($otherComment_Html_next);
                        $otherComment_EndCondition_dd = ereg("No further edits should be made to this page", trim(strip_tags($otherComment_Html_next_dd)));
                    }
                    if( $extra_i == 0)
                        $flag_otherComment_empty=1;
                    else
                        $flag_otherComment_empty=0;    
                }
                
                $otherComment_Html = $otherComment_Html_pre.$otherComment_Html;
                
                $GLOBALS['log'] .=  "<div class='percentage'> mainComment_Html(sibling_index=$sibling_index):</div>$mainComment_Html";
                $GLOBALS['log'] .=  "<div class='percentage'> mainComment_ExtraNote:</div>$mainComment_ExtraNote";
                $GLOBALS['log'] .= str_replace("background-color: #F3F9FF;"," ",$this->givenHTML);
                $GLOBALS['log'] .= "<div class='percentage'>extra_i:$extra_i for AFDID:".$this->givenAFDID."</div>,    <a target='_blank' href='https://en.wikipedia.org$this->afdURL'>https://en.wikipedia.org$this->afdURL</a>";
                $GLOBALS['log'] .= "<div class='percentage'>otherComment_Html:</div><br/>(flag_otherComment_empty=$flag_otherComment_empty)<br/> $otherComment_Html <hr/>";
                $GLOBALS['log'] .= "<div class='percentage'> otherComment_Html_next:</div><br/> $otherComment_Html_next ";
                
                //storeInformation
                $this->afd->flag_error = $flag_error;
                $this->afd->flag_otherComment_empty= $flag_otherComment_empty;
                $this->afd->endResult_Html = $endResult_Html;
                $this->afd->endResult_ExtraNote = $endResult_ExtraNote;
                $this->afd->parse_e_result_s = $result;
                $this->afd->parse_e_result_e = $result_2;
                $this->afd->mainComment_Html = $mainComment_Html;
                $this->afd->mainComment_ExtraNote = $mainComment_ExtraNote;
                $this->afd->otherComment_Html = $otherComment_Html;
                $this->afd->plainlinks_Html = $container_nourlexpansion;
                
                $this->afd->parse_e_result_s = $parse_e_result_s;
                $this->afd->parse_e_result_e = $parse_e_result_e;
                $this->afd->updateAFD_withoutAFDHTML_byAFDID();
            }
        } 
        catch (Exception $e) {
            echo '<br/>Caught exception: ',  $e->getMessage(), "\n";
        }
        
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseAFD->parseAFDContent()*******************</span>";
        echo $GLOBALS['log'];
        $GLOBALS['log']=""; 
        flush();
    }
}

?>