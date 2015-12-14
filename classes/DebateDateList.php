<?php 
require_once "Crawler.php";
require_once "config.php";
require_once "functions.php";

//Create a class and a list for URL and featch their data 
//retreive the URL from URL
//Study The Crawler data.
class DebateDateList {
    public $list;
    
    //months
    private $month1 = "January";
    private $month2 = "February";
    private $month3 = "March";
    private $month4 = "April";
    private $month5 = "May";
    private $month6 = "June";
    private $month7 = "July";
    private $month8 = "August";
    private $month9 = "September";
    private $month10 = "October";
    private $month11 = "November";
    private $month12 = "December";
    
    private $baseURL = "https://en.wikipedia.org/wiki/Wikipedia:Articles_for_deletion/Log/";
    //https://en.wikipedia.org/wiki/Wikipedia:Articles_for_deletion/Log/2015_January_6
    
    public function DebateDateList() {
        
        $GLOBALS['log'] .= "<br/>**** Call DebateDateList->DebateDateList()";
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        
        $this->createList();
        
        if( 0 == count($this->list)  )
            die("<br/>List is empty!");
            
        //$this->printListWithATag();
        
        // Create connection
        $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
         
        $this->saveListToDB($conn);
        
        $this->crawlList($conn);
        
        $conn->close();
        
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
    }
    
    
    
    private function crawlList($conn)
    {
        $GLOBALS['log'] .= "<br/>================================================================== Start of list." ;
        $GLOBALS['log'] .= "<br/>**** Call DebateDateList->crawlList() and  then call Crawler";
        echo $GLOBALS['log'];
        $GLOBALS['log'] = "";
        echo "<br/>";
            
        $i=0;
        foreach($this->list as $record) {
            $i++;
            echo "------------------------------------------------------------------------------------> $i";
        
            //timer
            echo "<br/> Go in 8 sec";
            for($j=8;$j>0 ; $j--)
            {
                ob_flush();
                flush();
                echo " ".$j." ";
                sleep(1);
            }
            
            $crawler = new Crawler($record);
            
            //$this->updateByIDandCrawlerID($this->getIDInDBByURL($record,$conn),$crawler->ID,$conn);
            $this->updateByURLandCrawlerID($crawler->url,$crawler->ID,$conn);
            
            echo $GLOBALS['log'];
            $GLOBALS['log'] = "";
        }
        $GLOBALS['log'] .= "================================================================== End of list</br/>Total No.:".count($this->list) ;
        ob_end_flush(); 
    }
    
    // from 15 May 2014 to 15 may 2015
    public function createList() {
        $this->list = array();
        
        
        /*
        for($i=15 ; $i<=31; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month5."_".$i;
        }
        
        //June - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month6."_".$i;
        }
        
        //July - 31 days 
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month7."_".$i;
        }
        
        
        //August - 31 days
        for($i=2 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month8."_".$i;
        }
        
        //September - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month9."_".$i;
        }
        */
        //Start From here
        /*
        //October - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month10."_".$i;
        }
        
        //November - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month11."_".$i;
        }
        
        //December - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2013_".$this->month12."_".$i;
        }
        
        
        //January - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month1."_".$i;
        }
        
        //February - 28 days; 29 days in Leap Years
        //2015 ->28
        //2014 ->29
        for($i=1 ; $i<=28 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month2."_".$i;
        }
        
        //March - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month3."_".$i;
        }
        
        
        //April - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month4."_".$i;
        }
        
        //May - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month5."_".$i;
        }
        
        //June - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month6."_".$i;
        }
        
        //July - 31 days 
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month7."_".$i;
        }
        
        
        //August - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month8."_".$i;
        }
        
        //September - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month9."_".$i;
        }
        
        //October - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month10."_".$i;
        }
        
        //November - 30 days
        for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month11."_".$i;
        }
        
        //December - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2014_".$this->month12."_".$i;
        }
        //January - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2015_".$this->month1."_".$i;
        }
        
        //February - 28 days; 29 days in Leap Years
        //2015 ->28
        //2014 ->29
        for($i=1 ; $i<=28 ; $i++)
        {
            $this->list[] = $this->baseURL."2015_".$this->month2."_".$i;
        }
        
        //March - 31 days
        for($i=1 ; $i<=31 ; $i++)
        {
            $this->list[] = $this->baseURL."2015_".$this->month3."_".$i;
        }*/
        
        //April - 30 days
        /*for($i=1 ; $i<=30 ; $i++)
        {
            $this->list[] = $this->baseURL."2015_".$this->month4."_".$i;
        }
        
        //May - 31 days
        for($i=1 ; $i<=15 ; $i++)
        {
            $this->list[] = $this->baseURL."2015_".$this->month5."_".$i;
        }*/
    }
    
    public function printListWithATag() {
        $i= 0;
        foreach($this->list as $record) {
            $i++;
            echo "<br/>$i- <a href='$record' target='blank'> $record </a>";
        }
    }
    
    private function saveListToDB($conn) {
       
        $i=0;
        foreach($this->list as $record) {
            $i++;
            $record=trim($record);
            $URLID = $this->getIDInDBByURL($record,$conn);
            if( $URLID != -1){
                //echo "<br/>$record exist in debateDateList by ID = $URLID";
                $GLOBALS['log'] .= "<br/> $i- $record <span class='good'>exist</span> in debateDateList by ID = $URLID";
            }
            else{
                
                $GLOBALS['log'] .= "<br/> $i- ";
                //echo "<br/>$record does not exist into debateDateList";
                $URLID = $this->insterURL($record, $conn);
                //echo " ***** $record inserted into debateDateList by ID = $URLID";
                $GLOBALS['log'] .= " by ID = $URLID ****";
            }
        }
    }
    
    private function getIDInDBByURL($givenURL, $conn)
    {
        $output = -1;
        try{
            $givenURL = trim($givenURL);
            if (!$givenURL) {
                throw new Exception('URL is Null!');
            }
            
            $sql = "SELECT debateDateListID, url
                    FROM debateDateList
                    WHERE url = '$givenURL'";
            //
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output = $row['debateDateListID'];
                }
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        //htmlspecialchars() - fail 
        //nl2br(stripslashes()) put extra space - not goof
        return $output;
    }
    
    private function insterURL($givenURL, $conn)
    {
        $output=-1;
        try{
            if (!$givenURL) {
                throw new Exception('URL is Null!');
            }
            
            //$sql = "INSERT INTO `crawler` VALUES ( null, 'a', '1', '1', '1', '1')";
            $sql = "INSERT INTO `debateDateList` ( url, crawlerID) 
            VALUES ( '$givenURL', '0')";
            
            //echo $sql;
            if (mysqli_query($conn, $sql)) {
                $GLOBALS['log'] .= " $givenURL Inserted <span class='good'>successfully</span> to debateDateList";
            } else {
                $GLOBALS['log'] .= " $givenURL <span class='bad'>Failed</span> to insert to debateDateList";
            }
            $output = mysqli_insert_id($conn);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    private function updateByIDandCrawlerID($givenID, $crawlerID, $conn)
    {
        $GLOBALS['log'] .= "<br/>**** Call DebateDateList->updateByIDandCrawlerID()";
        
        $output=-1;
        try{
            if (!$givenID || !$crawlerID) {
                throw new Exception('ID=$givenID or CrawerID=$crawlerID is empty!');
            }
            
            $sql = " update debateDateList
            set crawlerID = '$crawlerID'
            where debateDateListID ='$givenID' ;" ;
            
            echo "<br/> ***** <br/>".$sql."<br/>";
            
            //echo $sql;
            if (mysqli_query($conn, $sql)) {
                $GLOBALS['log'] .= "<br/>  debateDateList joined <span class='good'>successfully</span> to crawler";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Failed</span> debateDateList joined to crawler givenID = $givenID";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    private function updateByURLandCrawlerID($url, $crawlerID, $conn)
    {
        $GLOBALS['log'] .= "****<br/> Call DebateDateList->updateByURLandCrawlerID($url, $crawlerID)";
        
        $output=-1;
        try{
            if (!$url || !$crawlerID) {
                throw new Exception('ID=$url or CrawerID=$crawlerID is empty!');
            }
            $url = trim($url);
            $sql = " update debateDateList
            set crawlerID = '$crawlerID'
            where url ='$url' ;" ;
            
            
            //echo $sql;
            if (mysqli_query($conn, $sql)) {
                $GLOBALS['log'] .= "<br/>  debateDateList joined <span class='good'>successfully</span> to crawler<br/>*****<br/>";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Failed</span> debateDateList joined to crawler givenID = $givenID";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
}

?>