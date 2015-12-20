<?php 
require_once "config.php";
require_once "functions.php";

//Create a class and a list for URL and fetch their data 
//retreive the data from URL
//Study The Crawler data.
class Crawler {
    public $ID = "";
    public $url = "";
    public $time = "";
    public $html = "";
    public $revision = "";
    public $validation = "";
        
    public function CrawlerURL() {
        $this->url = trim ($this->url);
        $this->time = date('Y-m-d G:i:s');
        
        if(!$fp = fopen( $this->url ,"r" )) 
        {
            return false;
        } 
        //our fopen is right, so let's go
        $this->html = "";
     
        //while it is not the last line, we will add the current line to our $content
        while(!feof($fp)) { 
            $this->html .= fgets($fp, 10240);
        }
        fclose($fp);
        
        // Create connection
        $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            
        $GLOBALS['log'] .= "<br/>********* <br/>". basename(__FILE__, '.php').".php URL:<a href='$this->url' target='_blank'> $this->url </a> <br/> Crawler <span class='good'>successfully</span> ";
        $GLOBALS['log'] .= "<br/> HTML size of the url is :". round((strlen($this->html)/1024),1)."KB";
        //we are done here, don't need the main source anymore
        $this->insterCrawlerToDB($conn);
        $GLOBALS['log'] .= "<br/>*********";
        
        //mysqli_close($conn);
        
        return $this->html;
    }
    
    static function getHTMLByURL($givenURL)
    {
        $output = "";
         try{
            $givenURL = trim($givenURL);
            if (!$givenURL) {
                throw new Exception('URL is Null!');
            }
            
            $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            
            $GLOBALS['log'] .= "<br/> CrawlerURL::getHTMLByURL($givenURL)";
            
            $sql = "select *
                    from crawler
                    where url='$givenURL'
                    and  time =( 
                    	select max(time)
                    	from crawler
                    	where url='$givenURL');";
                        
            //echo $sql;
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output = $row['html'];
                    $output .= "<br/><br/><a href='".$row['url']."'>".$row['url']."</a>";
                    $GLOBALS['log'] .= "<br/>". basename(__FILE__, '.php').".php CrawlerURL::getHTMLByURL() ID:".$row["ID"]." URL: ".$row["url"]." </br>" ;
                }
            } 
            else
            {
                throw new Exception('There is record in DB!');
            }
            //mysqli_close($conn);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        //htmlspecialchars() - fail 
        //nl2br(stripslashes()) put extra space - not goof
        return $output;
    }
    
    static function getHTMLByID($ID)
    {
        $output = "";
        try{
            $ID = trim($ID);
            if (!$ID) {
                throw new Exception('ID=$ID is Null!');
            }
            
            $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            
            $GLOBALS['log'] .= "<br/> CrawlerURL::getHTMLByD($ID)";
            
            $sql = "select *
                    from crawler
                    where id='$ID'";
                        
            //echo $sql;
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output = $row['html'];
                    $output .= "<br/><br/><a href='".$row['url']."'>".$row['url']."</a><br/><br/>";
                    $GLOBALS['log'] .= "<br/>". basename(__FILE__, '.php').".php CrawlerURL::getHTMLByURL() ID:".$row["ID"]." URL: ".$row["url"]." </br>" ;
                }
            }
            else
            {
                throw new Exception('There is record in DB!');
            }
            //mysqli_close($conn);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        //htmlspecialchars() - fail 
        //nl2br(stripslashes()) put extra space - not goof
        return $output;
    }
    
    static function getHTMLByDebateDateListID($DebateDateListID)
    {
        $output = "";
        try{
            $DebateDateListID = trim($DebateDateListID);
            if (!$DebateDateListID) {
                throw new Exception('DebateDateListID=$DebateDateListID is Null!');
            }
            
            $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            
            $GLOBALS['log'] .= "<br/> CrawlerURL::getHTMLByDebateDateListID($DebateDateListID)";
            
            $sql = "select * 
                    from crawler
                    INNER JOIN debatedatelist on crawler.ID=debatedatelist.crawlerID
                    where debatedatelist.debateDateListID= '$DebateDateListID';";
                        
            //echo $sql;
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output = $row['html'];
                    $output .= "<br/><br/><a href='".$row['url']."'>".$row['url']."</a><br/><br/>";
                    $GLOBALS['log'] .= "<br/>". basename(__FILE__, '.php').".php CrawlerURL::getHTMLByDebateDateListID() </br>" ;
                }
            }
            else
            {
                throw new Exception('There is record in DB!');
            }
            //mysqli_close($conn);
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    private function insterCrawlerToDB($conn)
    {
        try{
            
            if (!$this->url) {
                throw new Exception('URL is Null!');
            }
            
            if (!$this->html) {
                throw new Exception('HTML is Empty!');
            }
            
            //$sql = "INSERT INTO `crawler` VALUES ( null, 'a', '1', '1', '1', '1')";
            $sql = "INSERT INTO `crawler` ( url, time, html, validation, revision ) 
            VALUES ( '$this->url', '$this->time', '". mysql_real_escape_string($this->html)."', '$this->validation', '$this->revision')";
            
            //echo $sql;
            if (mysqli_query($conn, $sql)) {
                $GLOBALS['log'] .= "<br/> Inserted <span class='good'>successfully</span> to DB";
            } else {
                $GLOBALS['log'] .= "<br/> <span class='bad'>Failed</span> to insert to DB";
                $GLOBALS['log'] .= "<br/>  <span class='bad'> Error description: " . mysqli_error($conn). "</span>";
            }
            
           $this->checkAllRecordsToRevisionAprtFromLast($conn, mysql_insert_id());
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
    
    private function checkAllRecordsToRevisionAprtFromLast($conn)
    {
        try{
            $lastID = -1;
            if (!$this->url) {
                    throw new Exception('URL is Null!');
            }
            
            //Get the last ID
            $sql = "select ID
                    from crawler
                    where url='$this->url'
                    and  time =( 
                    	select max(time)
                    	from crawler
                    	where url='$this->url');";
                    
            $result = $conn->query($sql);
            if ($result->num_rows > 0){
                $obj = $result->fetch_object();
                $lastID = $obj->ID;
                $this->ID = $lastID;
            }
            else
                throw new Exception('There are no record in DB for giver URL='.$this->url);
            
            // Make the rest of records to the revision flag
            $sql = " update crawler
            set revision = 1
            where url='$this->url' and ID !=$lastID ;" ;
            
            //echo $sql;
            if (mysqli_query($conn, $sql)) {
                $GLOBALS['log'] .= "<br/>  Set to revision flag <span class='good'>successfully</span>(not $lastID)";
                $GLOBALS['log'] .= "<br/>  View it at <a href='http://localhost/AFDVizualization/getHtmlByID.php?id=$lastID'>http://localhost/AFDVizualization/getHtmlByID.php?id=$lastID</a> ";
                
            } else {
                $GLOBALS['log'] .= "<br/> <span class=''>Fail</span> to set to revision flag in DB";
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        
    }
    function getTotalNumberOfCrawelers($conn)
    {
        $output = 0;
        try{
            $sql = "SELECT ID, url FROM crawler";
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output++;
                    $GLOBALS['log'] .= "<br/>". basename(__FILE__, '.php').".php ID:".$row["ID"]." URL: ".$row["url"]." </br>" ;
                }
            }
        }   
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return $output;
    }
    
    function getHTML()
    {
        // Create connection
        $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
        $output = $this->getLastVersionOfCraweleredURL($conn);
        //mysqli_close($conn);   
        return $output;
        
    }
    // need to revise the sql query
    private function getLastVersionOfCraweleredURL($conn)
    {
        $output = 0;
        try{
            if (!$this->url) {
                throw new Exception('URL is Null!');
            }
            
            $sql = "SELECT ID, url, html FROM crawler
                    WHERE ID = '$this->ID'";
            
            $result = $conn->query($sql);
             
            if ($result->num_rows > 0) {
                // output data of each row
                while($row = $result->fetch_assoc()) {
                    $output = $row['html'];
                    $GLOBALS['log'] .= "<br/>". basename(__FILE__, '.php').".php ID:".$row["ID"]." URL: ".$row["url"]." </br>" ;
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
   //$givenURL = "https://en.wikipedia.org/wiki/Wikipedia:Articles_for_deletion";
    public function Crawler( $givenURL = "")
    {
        $this->url = $givenURL;
        $this->CrawlerURL();
    }
    
}

?>