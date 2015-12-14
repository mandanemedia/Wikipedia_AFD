<?php 
require_once "AFD.php";

class DebateDate {
    public $debateDateListID;
    public $crawlerID;
    public $url;
    public $time;
    public $html;
    public $revision;
    public $validation;
    public $html_tableOfContent;
    public $totalAFDTable;
    public $totalAFDContent;
    public $totalAFD_inDB;
    public $conn;
    
    public $AFDList;
    
    public function DebateDate($debateDateListID) {
        try{
            //$GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call DebateDate->DebateDate() </span>";
            //echo $GLOBALS['log'];
            //$GLOBALS['log'] = "";
            
            // Create connection
            $this->conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name);
            // Check connection
            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
            if (!$debateDateListID) {
                throw new Exception('givenID is Null!');
            }
            
            $sql = "select debateDateListID, crawlerID, crawler.url, time, html, validation, revision, html_tableOfContent, totalAFDTable, totalAFDContent, totalAFD_inDB 
                    from debatedatelist
                    INNER JOIN crawler ON debatedatelist.crawlerID = crawler.ID
                    where debateDateListID = '$debateDateListID'";
            
            if ($result=mysqli_query($this->conn,$sql))
            {
              while ($obj=mysqli_fetch_object($result))
                {
                    $this->debateDateListID = $obj->debateDateListID;
                    $this->crawlerID = $obj->crawlerID;
                    $this->url = $obj->url;
                    $this->time = $obj->time;
                    $this->html = $obj->html;
                    $this->revision = $obj->validation;
                    $this->validation = $obj->revision;
                    $this->html_tableOfContent = $obj->html_tableOfContent;
                    $this->totalAFD = $obj->totalAFDTable;
                    $this->totalAFD = $obj->totalAFDContent;
                    $this->totalAFD_inDB = $obj->totalAFD_inDB;
                }
              // Free result set
              mysqli_free_result($result);
            }
            
            mysqli_close($this->conn );  
            //$this->setLog();
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        //$GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called DebateDate->DebateDate()*******************</span>";
        //echo $GLOBALS['log'];
        //$GLOBALS['log']="";
    }
    
    public function setLog() {
        $GLOBALS['log'] .=  "<br/> debateDateListID = $this->debateDateListID";
        $GLOBALS['log'] .=  "<br/> crawlerID = $this->crawlerID";
        $GLOBALS['log'] .=  "<br/> url = <a target='_blank' href='$this->url'> $this->url </a>";
        $GLOBALS['log'] .=  "<br/> time = $this->time";
        $GLOBALS['log'] .=  "<br/> html = ". round((strlen($this->html)/1024),1)."KB";
        $GLOBALS['log'] .=  "<br/> revision = $this->revision";
        $GLOBALS['log'] .=  "<br/> validation = $this->validation";
        $GLOBALS['log'] .=  "<br/> html_tableOfContent = ". round((strlen($this->html_tableOfContent)/1024),1)."KB";;
    }
    
    public function updateTableOfContent()
    {
        try{
            if (!$this->html_tableOfContent) {
                    throw new Exception('html_tableOfContent is Null!');
            }
            
            // Make the rest of records to the revision flag
            $sql = " update debatedatelist
            set html_tableOfContent = '".mysql_real_escape_string($this->html_tableOfContent)."'
            where debateDateListID='$this->debateDateListID';" ;
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> tableOFContent is stored  <span class='good'>successfully</span>, debateDateListID=$this->debateDateListID";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Fail</span> to store tableOFContent into DB.";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    public function updateTotalAFD_inDB()
    {
        try{
            if (!$this->debateDateListID) {
                    throw new Exception('updateTotalAFD_inDB(), debateDateListID is empty!');
            }
            
            $sql = "select count(DISTINCT AFDID) as totalAFD_inDB
                    from afd
                    where debateDateListID='$this->debateDateListID';" ;
            
            if ($result=mysqli_query($this->conn,$sql))
            {
              while ($obj=mysqli_fetch_object($result))
                {
                    $totalAFD_inDB = $obj->totalAFD_inDB;
                    $this->totalAFD_inDB = $totalAFD_inDB;
                }
              // Free result set
              mysqli_free_result($result);
            }
            //echo "<hr/>this->debateDateListID= $this->debateDateListID,  this->totalAFD_inDB = $this->totalAFD_inDB";
            //flush();
            
            $sql = " update debatedatelist
            set totalAFD_inDB = '".$totalAFD_inDB."'
            where debateDateListID='$this->debateDateListID';" ;
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> tableOFContent is stored  <span class='good'>successfully</span>, debateDateListID=$this->debateDateListID";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Fail</span> to store tableOFContent into DB.";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    public function getAFDList()
    {
        $GLOBALS['log'] .= "<br/>****************** <span class='startCall'> Call DebateDate->getAFDList() </span>";
        echo $GLOBALS['log'];
        $GLOBALS['log']="";
        ob_flush();
        flush();
        
        try{
            if (!$this->debateDateListID) {
                    throw new Exception('getAFDList(), debateDateListID is empty!');
            }
            
            $sql = "select *
                    from afd
                    where debateDateListID='$this->debateDateListID';" ;
            $this->AFDList = array();
            
            $GLOBALS['log'] .="<table border='1' width='100%' id='topOfList'>";
            $GLOBALS['log'] .= "<tr>
                                    <td width='5%'>No.</td>
                                    <td width='5%'>AFDID</td>
                                    <td width='20%'>AFDTitle</td>
                                    <td width='5%'>flag deletedArticle</td>
                                    <td width='5%'>Keep</td>
                                    <td width='5%'>parse_result Start</td>
                                    <td width='5%'>parse_result End</td>
                                    <td width='40%'>endResult_Html</td>
                                </tr>";
            $i=0;
            $j=0;
            if ($result=mysqli_query($this->conn,$sql))
            {
              while ($obj=mysqli_fetch_object($result))
                {
                    $i++;
                    $this->AFDList[] = $obj->AFDID;
                    $GLOBALS['log'] .= "<tr>
                                            <td valign='top'><a href='#$obj->AFDTitleID'>$i</a></td>
                                            <td valign='top'><a target='_blank' href='getAFDHtmlByID.php?id=$obj->AFDID'>$obj->AFDID</a></td>
                                            <td valign='top'><a target='_blank' href='https://en.wikipedia.org$obj->AFDURL'>$obj->AFDTitle</a></td>
                                            <td valign='top'>$obj->flag_deletedArticle</a></td>";
                   if($obj->flag_articleURL_Working!=0 ) 
                        $GLOBALS['log'] .= "<td valign='top'><a target='_blank' href=' https://en.wikipedia.org$obj->articleURL'>Link</a></td>";
                   else
                        $GLOBALS['log'] .= "<td valign='top'></td>";
                   
                   $GLOBALS['log'] .= "     <td valign='top'>$obj->parse_endResult_details</a></td>
                                            <td valign='top'>$obj->endResult_Html</a></td>
                                        </tr>";                      
                                        if(!empty($obj->flag_deletedArticle))
                                            $j++;
                    //echo $GLOBALS['log'];
//                    $GLOBALS['log']  = "";
//                    ob_flush();
//                    flush();
                }
              // Free result set
              mysqli_free_result($result);
            }
            $GLOBALS['log'] .= "<tr>
                                            <td>T: $i</td>
                                            <td></td>
                                            <td> </td>
                                            <td>D:$j</td>
                                            <td>K:".($i-$j)."</td>
                                            
                                        </tr>";
            $GLOBALS['log'] .= "</table>";
            $GLOBALS['log'] .= "<br/><span class='endCall'>**** End Called DebateDate->getAFDList()*******************</span>";
            
            $GLOBALS['log'] .="<hr/> $this->html_tableOfContent<hr/>";
            
            $sql = "select *
                    from afd
                    where debateDateListID='$this->debateDateListID';" ;
            
            $GLOBALS['log'] .="<table border='1' width='100%'>";
            $i=0;
            if ($result=mysqli_query($this->conn,$sql))
            {
              while ($obj=mysqli_fetch_object($result))
                {   
                    $i++;
                    $GLOBALS['log'] .= "<tr><td valign='top' width='100%' colspan='8'>$i- <a href='#topOfList'>To top</a>,".str_replace("background-color: #F3F9FF;"," ",$obj->AFDHTML)."</td></tr>";
                    $GLOBALS['log'] .= "<tr><td valign='top' width='100%' style='background-color: #F3F9FF;'>-</td></tr>";
                }
            }
            $GLOBALS['log'] .= "</table>";
            echo $GLOBALS['log'];
            $GLOBALS['log']="";
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    public function updateTotalAFDTable($totalAFDTable)
    {
        try{
            if (!$totalAFDTable) {
                    throw new Exception('$totalAFDTable is empty!');
            }
            
            $this->totalAFDTable = $totalAFDTable;
            // Make the rest of records to the revision flag
            $sql = " update debatedatelist
            set totalAFDTable = '$totalAFDTable'
            where debateDateListID='$this->debateDateListID';" ;
            
            $GLOBALS['log'] .= "<br/> $totalAFDTable is stored  <span class='good'>successfully</span>, debateDateListID=$this->debateDateListID";
            echo $GLOBALS['log'];
            $GLOBALS['log'] = "";
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> totalAFDTable is stored  <span class='good'>successfully</span>, debateDateListID=$this->debateDateListID";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Fail</span> to store totalAFDTable into DB.";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    public function updateTotalAFDContent($totalAFDContent)
    {
        try{
            if (!$totalAFDContent) {
                    throw new Exception('$totalAFDContent is empty!');
            }
            
            $this->totalAFDContent = $totalAFDContent;
            // Make the rest of records to the revision flag
            $sql = " update debatedatelist
            set totalAFDContent = '$totalAFDContent'
            where debateDateListID='$this->debateDateListID';" ;
            
            
            if (mysqli_query($this->conn, $sql)) {
                $GLOBALS['log'] .= "<br/> totalAFDContent is stored  <span class='good'>successfully</span>, debateDateListID=$this->debateDateListID";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Fail</span> to store totalAFDContent into DB.";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    public function __destruct()
    {
        if(is_resource($conn))
            mysqli_close($this->conn); 
    }
    
    public function addNewAFDByTitle($afd)
    {
        $afd->updateAFD_Initial();
        //$afd->updateAFD();
    }
    
}
?>
