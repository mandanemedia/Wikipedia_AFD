<?php 
require_once "Crawler.php";
require_once "DebateDateList.php";
require_once "AFD.php";
require_once "DebateDate.php";
require_once "config.php";
require_once "functions.php";
require_once "simple_html_dom.php";

class ParseDebateDate {
    public $givenID;
    public $givenURL;
    public $givenHTML;
    
    public $extractedTableOfContent;
    public $debateDate;
    
    public $parsedError = 0;
    public $totalParsed = 0;
    
    public $startParseTime = 0;
    public $endParseTime = 0;
    
    public function ParseDebateDate($givenID) {
        
        $this->startParseTime = date('Y-m-d H:i:s');
        
        $GLOBALS['log'] .= "<span class='startCall'> <br/>****************** Call ParseDebateDate->ParseDebateDate() </span>";
            
        $this->debateDate = new DebateDate($givenID);
        $this->givenID = $givenID;
        $this->givenURL = $this->debateDate->url;
        $this->givenHTML = $this->debateDate->html;
        
        $this->setLog();
        
        //call parse
        $totalCalculated = $this->parseTableOfContent();
        $this->parseContent($totalCalculated);
        
        $this->endParseTime = date('Y-m-d H:i:s');
        $parseDuration = round( (strtotime($this->endParseTime) - strtotime($this->startParseTime)) / 3600 * 60, 2);
        
        $GLOBALS['log'] .= "<span class='bad'><br/>Total number of parsed Error: ".$this->parsedError."</span>";
        if($this->parsedError!= 0 )
            $GLOBALS['log'] .= "<br/> this->totalParsed=$this->totalParsed with Error percentage(".round($this->parsedError/$this->totalParsed,2).") in ".$parseDuration." min.";
        else
            $GLOBALS['log'] .= "<br/> this->totalParsed=$this->totalParsed with Error percentage(0) in ".$parseDuration." min.";
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseDebateDate->ParseDebateDate()*******************</span><hr/><hr/>";
        
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        flush();
    }
    
    function setLog() {
        $GLOBALS['log'] .=  "<br/> givenID = $this->givenID";
        $GLOBALS['log'] .=  "<br/> givenURL = $this->givenURL";
        $GLOBALS['log'] .=  "<br/> givenHTML = ". round((strlen($this->givenHTML)/1024),1)."KB";
    }
    
    /*
    <div id="toc" class="toc">
        <div id="toctitle">
            <h2>Contents</h2>
            <span class="toctoggle">&nbsp;[<a href="#" id="togglelink">hide</a>]&nbsp;</span>
        </div>
        <ul>
        <li class="toclevel-1">
            <a href="#Gregory_Hesse">
                <span class="tocnumber">1</span> 
                <span class="toctext">Gregory Hesse</span>
            </a>
        </li>
        <li class="toclevel-1">
            <a href="#Capital_Region_Airport_Authority">
                <span class="tocnumber">2</span> 
                <span class="toctext">Capital Region Airport Authority</span>
            </a>
        </li>
        http://web-developer-thing.blogspot.ca/2010/02/php-simple-html-dom-parser-makes.html
        http://simplehtmldom.sourceforge.net/manual.htm
    */
    // get the this section
    // store it as a html into DebateDateList
    // create a new table and put all of the new links within the new table
    // crawl data from that table
    function parseTableOfContent() {
        
        $GLOBALS['log'] .= "<br/> <span class='startCall'> ****************** Call ParseDebateDate->parseTableOfContent() <br/> <a target='_blank' <a href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>";
        try{
            $dom = new simple_html_dom();
            $dom->load($this->givenHTML, false);
            
            $i=0;
            //
            foreach($dom->find('div.toc') as $div_doc) {
                $i++;
                if ($i>1) {
                    throw new Exception("<span class='bad'> There are more than one table of content on the <a target='_blank' href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>!");
                }
                $j=0;
                foreach($div_doc->find('ul') as $ul) 
                    if(trim($ul->parent()->tag)=="div")
                    {
                        $this->extractedTableOfContent = $ul;
                        $j++;
                        if ($j>1) {
                            echo $ul->parent()->tag."<br/><hr/>";
                            echo $ul;
                            throw new Exception("<span class='bad'> There are more than one URL on the table of content on the <a target='_blank' href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>!");
                        }
                        $k=0;
                        foreach($ul->find('li.toclevel-1') as $li) {
                            $k++;
                            foreach( $li->find('a') as  $atag)
                            {
                                $extractedNo = $atag->find('span.tocnumber', 0)->plaintext;
                                $GLOBALS['log'] .= "<br/> $k - $extractedNo -";
                                $atagTitle = $atag->find('span.toctext', 0)->plaintext;
                                $GLOBALS['log'] .= " $atagTitle";
                                $atagHref = $atag->href;
                                $GLOBALS['log'] .= " $atagHref";
                                
                                //storeInformation
                                //$afd = new AFD($atagTitle, $this->debateDate->debateDateListID, $this->debateDate->conn);
                                //$afd->AFDTitleID = $atagHref;
                                //$this->debateDate->addNewAFDByTitle($afd);
                            }
                        }
                        $this->debateDate->updateTotalAFDTable($k);
                        $GLOBALS['log'] .= "<br/><b>Total AFD No. (Table)=".$this->debateDate->totalAFDTable."=".$this->debateDate->totalAFDContent."(Content) </b>";
                    }
                if ($j<1) {
                    throw new Exception("<br/><span class='bad'> There is no URL in the table of content on the <a target='_blank' href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>!");
                }
                //just keep output to calculate the percentage 
                $output = $k;
            }
            if ($i<1) {
                throw new Exception("<span class='bad'> There is no table of content on the <a target='_blank' href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>!");
            }
            
            if (!$this->extractedTableOfContent ) {
                throw new Exception("<span class='bad'> extractedTableOfContent is empty at <a target='_blank' href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>!");
            }
            
            $this->debateDate->html_tableOfContent =  $this->extractedTableOfContent;
            $this->debateDate->updateTableOfContent();
        } 
        catch (Exception $e) {
            echo '<br/>Caught exception: ',  $e->getMessage(), "\n";
        }
    
        $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called ParseDebateDate->parse()*******************</span>";
        echo $GLOBALS['log'];
        $GLOBALS['log']="";
        return $output;
    }
    
    function parseContent($totalCalculated) {
        
        $GLOBALS['log'] .= "<br/> <span class='startCall'> ****************** Call ParseDebateDate->parseContent() <a target='_blank' <a href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a> </span>";
        try{
            $dom = new simple_html_dom();
            $dom->load($this->givenHTML, false);
            
            $i=0;
            //main section
            foreach($dom->find('div.afd') as $div_afd) { 
                $i++;
                
                //get the result of discussion
                $endResult_Html = $div_afd->find("p", 0);
                $GLOBALS['log'] .="<hr/>$i out of $totalCalculated (<span class='good'>". round($i/$totalCalculated, 2) ."%</span>) For   ";
                $GLOBALS['log'] .="<a target='_blank' <a href='getHtmlByID.php?id=".$this->debateDate->crawlerID."'>". $this->debateDate->url."</a>";
                $GLOBALS['log'] .= ", crawlerID =".$this->debateDate->crawlerID.", and  debateDateListID =".$this->debateDate->debateDateListID."<br/> $endResult_Html";
                
                
                $flag_error = "";
                $result = ereg("^The", $div_afd->find("p", 0)->plaintext);
                $result_2 = ereg("UTC)$", $div_afd->find("p", 0)->plaintext);
                if($result == 0)
                {
                    $GLOBALS['log'] .= "<br/><span class='bad'>$i.  Result did Not match. (parsedError = $this->parsedError)</span>";
                    $this->parsedError++;
                    $flag_error = "endResult_Start";
                    $parse_e_result_s = 0;
                    if($result_2 == 0)
                    {
                        $GLOBALS['log'] .= "<br/><span class='bad'>$i.  Result_end failed. (parsedError = $this->parsedError)</span>";
                        $this->parsedError++;
                        $flag_error = $flag_error ."; endResult_End";
                        $parse_e_result_e = 0;
                    }
                    else
                    {
                        $GLOBALS['log'] .= "<br/><span class='good'>$i.  Result_end Matched.</span>";
                        $parse_e_result_e = 1;
                    }
                }
                else
                {
                    $GLOBALS['log'] .= "<br/><span class='good'>$i.  Result Matched.</span>";
                    $parse_e_result_s = 1;
                    
                    if($result_2 == 0)
                    {
                        $GLOBALS['log'] .= "<br/><span class='bad'>$i.  Result_end failed. (parsedError = $this->parsedError)</span>";
                        $this->parsedError++;
                        $flag_error = $flag_error . "endResult_End";
                        $parse_e_result_e = 0;
                    }
                    else
                    {
                        $GLOBALS['log'] .= "<br/><span class='good'>$i.  Result_end Matched.</span>";
                        $parse_e_result_e = 1;
                    }
                    
                }
                
                           
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
                {
                    $container_nourlexpansion =  getNextSibling($div_afd->find("p", 0),$container_sibiling_index);//1..n
                    $GLOBALS['log'] .=  "<div class='percentage'>After Result Sibiling index: $container_sibiling_index </div>";
                }
                
                //if($container_sibiling_index>2)
                for($result_i=2; $result_i < $container_sibiling_index; $result_i++ )
                        $endResult_Html .= getNextSibling($div_afd->find("p", 0),$result_i);
                
                
                $AFDTitleID = $container_nourlexpansion ->find('span.nourlexpansion', 0)->prev_sibling()->id ; 
                //echo $container_nourlexpansion ->find('span.nourlexpansion', 0);     
                //echo "<br/>"; 
                $articleURL = $container_nourlexpansion ->find('span.nourlexpansion', 0)->find('a', 0)->href;
                $AFDTitle   = $container_nourlexpansion ->find('span.nourlexpansion', 0)->find('a', 0)->plaintext;
                $flag_deletedArticle = "";
                $flag_articleURL_Working = 0;
                if(!empty($container_nourlexpansion ->find('span.nourlexpansion', 0)->find('a', 0)->class))
                {
                    $flag_deletedArticle = $container_nourlexpansion ->find('span.nourlexpansion', 0)->find('a', 0)->class;
                    $GLOBALS['log'] .=  "$flag_deletedArticle<br/>";
                    $flag_articleURL_Working = 0;
                }
                else
                {
                    $flag_articleURL_Working = 1;
                    $flag_deletedArticle = "";
                }
                //echo $container_nourlexpansion ->find('span.nourlexpansion', 0)->next_sibling();
                //echo "<br/>"; 
                $AFDURL = $container_nourlexpansion ->find('span.nourlexpansion', 0)->next_sibling()->href;
                
                 
                
                //storeInformation
                $afd = new AFD($AFDTitle, $this->debateDate->debateDateListID, $this->debateDate->conn);
                $afd->AFDTitleID = $AFDTitleID;
                $afd->articleURL = $articleURL;
                $afd->flag_deletedArticle = $flag_deletedArticle;
                $afd->flag_articleURL_Working = $flag_articleURL_Working;
                $afd->AFDURL = $AFDURL;
                $afd->AFDHTML = $div_afd;
                $afd->endResult_Html = $endResult_Html;
                $afd->parse_e_result_s = $parse_e_result_s;
                $afd->parse_e_result_e = $parse_e_result_e;
                $this->debateDate->addNewAFDByTitle($afd);
                $this->totalParsed++;
                
                //echo $container_nourlexpansion;
                $GLOBALS['log'] .= "\"<span class='percentage'>$AFDTitle</span>\" crawlerID=".$this->debateDate->crawlerID.", afd->debateDateListID=$afd->debateDateListID , afd->AFDID= <a target='_blank' href='getAFDHtmlByID.php?id=".$afd->AFDID."'>". $afd->AFDID."</a> <br/>";
                $GLOBALS['log'] .=  "<br/>". $div_afd->plaintext;
                
                echo $GLOBALS['log'];
                $GLOBALS['log']=""; 
                flush();
                
            }
            $this->debateDate->updateTotalAFDContent($i);
        } 
        catch (Exception $e) {
            echo '<br/>Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    /*
    <div class="boilerplate afd vfd xfd-closed">
        <dl>
            <dd>
                <i>
                    The following discussion is an archived debate of the proposed deletion of the article below. <span style="color:red"><b>Please do not modify it.</b></span> Subsequent comments should be made on the appropriate discussion page (such as the article's <a href="/wiki/Help:Using_talk_pages" title="Help:Using talk pages">talk page</a> or in a <a href="/wiki/Wikipedia:Deletion_review" title="Wikipedia:Deletion review">deletion review</a>). No further edits should be made to this page.
                </i>
            </dd>
        </dl>
        <p> //**result
            The result was 
            <b>keep</b>. (
            <a href="/wiki/Wikipedia:Non-admin_closure" title="Wikipedia:Non-admin closure">non-admin closure</a>
            ) 
            <a href="/wiki/User:Northamerica1000" title="User:Northamerica1000">NorthAmerica</a>
            <sup>
                <a href="/wiki/User_talk:Northamerica1000" title="User talk:Northamerica1000">1000</a>
            </sup> 
            07:27, 24 May 2014 (UTC)
        </p>
        <h3> //** Title and article link
            <span class="mw-headline" id="Billy_Boy_on_Poison">
                <a href="/wiki/Billy_Boy_on_Poison" title="Billy Boy on Poison">Billy Boy on Poison</a>
            </span>
            <span class="mw-editsection">
                <span class="mw-editsection-bracket">
                    [
                </span>
                <a href="/w/index.php?title=Wikipedia:Articles_for_deletion/Billy_Boy_on_Poison&amp;action=edit&amp;section=T-1" title="Wikipedia:Articles for deletion/Billy Boy on Poison">edit</a>
                <span class="mw-editsection-bracket">]</span>
            </span>
        </h3>
        <dl> //** plainlinks_Html
            <dd>
                <span id="Billy_Boy_on_Poison"></span>
                <span class="plainlinks nourlexpansion lx">
                    <a href="/wiki/Billy_Boy_on_Poison" title="Billy Boy on Poison">Billy Boy on Poison</a> 
                    (<a class="external text" href="//en.wikipedia.org/w/index.php?title=Billy_Boy_on_Poison&amp;action=edit">edit</a>
                    &nbsp;| 
                    <a href="/wiki/Talk:Billy_Boy_on_Poison" title="Talk:Billy Boy on Poison">talk</a>
                    &nbsp;| 
                    <a class="external text" href="//en.wikipedia.org/w/index.php?title=Billy_Boy_on_Poison&amp;action=history">history</a>
                    <span class="sysop-show">
                        &nbsp;| 
                        <a class="external text" href="//en.wikipedia.org/w/index.php?title=Billy_Boy_on_Poison&amp;action=protect">protect</a>
                        &nbsp;| 
                        <a class="external text" href="//en.wikipedia.org/w/index.php?title=Billy_Boy_on_Poison&amp;action=delete">delete</a>
                    </span>
                    &nbsp;| 
                    <a class="external text" href="//en.wikipedia.org/w/index.php?title=Special:Whatlinkshere/Billy_Boy_on_Poison&amp;limit=999">links</a>
                    &nbsp;| 
                    <a class="external text" href="//en.wikipedia.org/w/index.php?title=Billy_Boy_on_Poison&amp;action=watch">watch</a>
                    &nbsp;| 
                    <a class="external text" href="//en.wikipedia.org/w/index.php?title=Special:Log&amp;page=Billy+Boy+on+Poison">logs</a>
                    &nbsp;| 
                    <a rel="nofollow" class="external text" href="http://stats.grok.se/en/latest90/Billy_Boy_on_Poison">views</a>
                    )
                </span> 
                – (
                <a href="/wiki/Wikipedia:Articles_for_deletion/Billy_Boy_on_Poison" title="Wikipedia:Articles for deletion/Billy Boy on Poison">View AfD</a>
                &nbsp;
                <b>·</b> 
                <span class="plainlinks">
                    <a rel="nofollow" class="external text" href="http://toolserver.org/~snottywong/cgi-bin/votecounter.cgi?page=Wikipedia:Articles_for_deletion/Billy_Boy_on_Poison">Stats</a>
                </span>)
            </dd>
            <dd>
                (
                <span class="plainlinks">
                    <i>Find sources:</i>
                    &nbsp;
                    <a rel="nofollow" class="external text" href="//www.google.com/search?as_eq=wikipedia&amp;q=%22Billy+Boy+on+Poison%22&amp;num=50">"Billy Boy on Poison"</a>
                    &nbsp;–&nbsp;
                    <a rel="nofollow" class="external text" href="//www.google.com/search?q=%22Billy+Boy+on+Poison%22&amp;tbm=nws">news</a>
                    &nbsp;
                    <b>·</b> 
                    <a rel="nofollow" class="external text" href="//www.google.com/search?&amp;q=%22Billy+Boy+on+Poison%22+site:news.google.com/newspapers&amp;source=newspapers">newspapers</a>
                    &nbsp;
                    <b>·</b> 
                    <a rel="nofollow" class="external text" href="//www.google.com/search?tbs=bks:1&amp;q=%22Billy+Boy+on+Poison%22">books</a>
                    &nbsp;
                    <b>·</b> 
                    <a rel="nofollow" class="external text" href="//scholar.google.com/scholar?q=%22Billy+Boy+on+Poison%22">scholar</a>
                    &nbsp;
                    <b>·</b> 
                    <a rel="nofollow" class="external text" href="http://www.highbeam.com/Search?searchTerm=%22Billy+Boy+on+Poison%22">highbeam</a>
                    &nbsp;
                    <b>·</b> 
                    <a rel="nofollow" class="external text" href="http://www.jstor.org/action/doBasicSearch?Query=%22Billy+Boy+on+Poison%22&amp;acc=on&amp;wc=on">JSTOR</a>
                    &nbsp;
                    <b>·</b> 
                    <a rel="nofollow" class="external text" href="//www.google.com/images?safe=off&amp;tbm=isch&amp;tbs=sur:fmc&amp;q=%22Billy+Boy+on+Poison%22+-site:wikipedia.org+-site:wikimedia.org">free images</a>
                    &nbsp;
                    <b>·</b> 
                    <a class="external text" href="//en.wikipedia.org/wiki/Wikipedia:The_Wikipedia_Library">wikipedia library</a>
                </span>
                )
            </dd>
        </dl>
        //* Main Comment
        <p>
            Notability concerns. This should actually have been deleted in 2008 per 
            <a href="/wiki/Wikipedia:Articles_for_deletion/On_My_Way_(Billy_Boy_on_Poison_Song)" title="Wikipedia:Articles for deletion/On My Way (Billy Boy on Poison Song)">this</a> 
            discussion. 
            <span style="background:#FF0;font-family:Rockwell Extra Bold">
                <a href="/wiki/User:Launchballer" title="User:Launchballer">
                    <font color="#00F">
                        Laun
                    </font>
                </a>
                <a href="/wiki/User_talk:Launchballer" title="User talk:Launchballer">
                    <font color="#00F">
                        chba
                    </font>
                </a>
                <a href="/wiki/Special:Contributions/Launchballer" title="Special:Contributions/Launchballer">
                    <font color="#00F">
                        ller
                    </font>
                </a>
            </span>
            09:40, 7 May 2014 (UTC)
        </p>
        //** Main Comment_ ExtraNote
        <dl>
            <dd><small class="delsort-notice">Note: This debate has been included in the <a href="/wiki/Wikipedia:WikiProject_Deletion_sorting/California" title="Wikipedia:WikiProject Deletion sorting/California">list of California-related deletion discussions</a>. <a href="/wiki/User:Gene93k" title="User:Gene93k">• Gene93k</a> (<a href="/wiki/User_talk:Gene93k" title="User talk:Gene93k">talk</a>) 01:15, 8 May 2014 (UTC)</small></dd>
            <dd><small class="delsort-notice">Note: This debate has been included in the <a href="/wiki/Wikipedia:WikiProject_Deletion_sorting/Bands_and_musicians" title="Wikipedia:WikiProject Deletion sorting/Bands and musicians">list of Bands and musicians-related deletion discussions</a>. <a href="/wiki/User:Gene93k" title="User:Gene93k">• Gene93k</a> (<a href="/wiki/User_talk:Gene93k" title="User talk:Gene93k">talk</a>) 01:15, 8 May 2014 (UTC)</small></dd>
        </dl>
        <ul>
            <li>
                <b>Keep</b>
                . Coverage at Allmusic 
                <a rel="nofollow" class="external autonumber" href="http://www.allmusic.com/artist/billy-boy-on-poison-mn0001057321/biography">[60]</a>
                <a rel="nofollow" class="external autonumber" href="http://www.allmusic.com/album/drama-junkie-queen-mw0000814915">[61]</a>
                , 
                <i>The Guardian</i> 
                <a rel="nofollow" class="external autonumber" href="http://www.theguardian.com/music/2009/apr/30/billy-boy-on-poison">[62]</a>
                , 
                <i>The Georgia Straight</i> 
                <a rel="nofollow" class="external autonumber" href="http://www.straight.com/article-248143/billy-boy-poisons-drama-junkie-queen-misses-badass-mark">[63]</a>
                , and AOL 
                <a rel="nofollow" class="external autonumber" href="http://www.aolradioblog.com/2010/02/23/apolo-ohno-dayquil-and-nyquil-commercial-song/">[64]</a> 
                looks to be enough to pass 
                <a href="/wiki/Wikipedia:BAND" title="Wikipedia:BAND" class="mw-redirect">WP:BAND</a>
                . 
                <small>
                    <span style="border:1px solid">
                        <a href="/wiki/User:Gongshow" title="User:Gongshow">
                            <b>
                                <span style="color:black">
                                    &nbsp;Gongshow&nbsp;
                                </span>
                            </b>
                        </a>
                    </span>
                    &nbsp;&nbsp;
                    <span style="background-color:black">
                        <a href="/wiki/User_talk:Gongshow" title="User talk:Gongshow">
                            <b>
                                <span style="color:#ffffff">talk</span>
                            </b>
                        </a>
                    </span>
                </small> 
                03:30, 12 May 2014 (UTC)
            </li>
        </ul>
        <hr style="width:55%;">
        <dl>
            <dd>
                <span style="color:#FF4F00;">
                    <b>
                        <a href="/wiki/Wikipedia:RELIST" title="Wikipedia:RELIST" class="mw-redirect">Relisted</a>
                        to generate a more thorough discussion so a clearer consensus may be reached.
                    </b>
                </span>
                <br>
            </dd>
            <dd>
                <small>
                    Please add new comments below this notice. Thanks, 
                    <span style="font:1.1em&quot;Avenir&quot;;padding:1px 3px;border:1px solid #909;color:#909">
                        czar&nbsp;
                            <a href="/wiki/User:Czar" title="User:Czar">
                                <font color="#909">?</font>
                            </a>
                    </span> 
                    03:31, 15 May 2014 (UTC)
                </small>
            </dd>
        </dl>
        <hr style="width:55%;">
        <ul>
            <li>
                <b>Keep</b>
                , Gongshow's references appears sufficient to establish notability. --
                <a href="/wiki/User:Joe_Decker" title="User:Joe Decker">j?e decker</a>
                <a href="/wiki/User_talk:Joe_Decker" title="User talk:Joe Decker">
                    <sup>
                        <small>
                            <i>talk</i>
                        </small>
                    </sup>
                </a> 
                00:10, 24 May 2014 (UTC)
            </li>
        </ul>
        <dl>
            <dd>
                <i>
                    The above discussion is preserved as an archive of the debate. 
                    <span style="color:red">
                        <b>Please do not modify it.</b>
                    </span> 
                    Subsequent comments should be made on the appropriate discussion page (such as the article's 
                    <a href="/wiki/Help:Using_talk_pages" title="Help:Using talk pages">talk page</a> 
                    or in a 
                    <a href="/wiki/Wikipedia:Deletion_review" title="Wikipedia:Deletion review">deletion review</a>
                    ). No further edits should be made to this page.
                </i>
            </dd>
        </dl>
    </div>
    */

}

?>