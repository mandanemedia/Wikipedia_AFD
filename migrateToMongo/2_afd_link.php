<?php
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('max_execution_time', -1);
require_once "config.php";

$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);

$maxDebatedatelistID = 0;
$sql = "select debatedatelistID, AFDID from afd ";
$records = array();
if ( $result=mysqli_query($conn,$sql) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
    {
        //$obj->AFDTitle = utf8_encode($obj->AFDTitle);
        $records[] = $obj;
        if(isset($obj->debatedatelistID))
            if( $obj->debatedatelistID > $maxDebatedatelistID)
                $maxDebatedatelistID = $obj->debatedatelistID;
    }
echo "<pre>";
//print_r($records);

$dbname = 'afd';
try {
    $mongo = new MongoClient(); // connect
    $db = $mongo->selectDB($dbname); // select database hollywood
    // You can also use:  $db=$mongo->$dbname;
}
catch ( MongoConnectionException $e ) {
    die ('Cannot connect to mongodb');
}

$debateDates = $db->debateDates;
foreach ($records as $record)
{
    //print_r($record);
    $debateDates->update(
         array("debateDateListID" => $record->debatedatelistID),
         array('$push' => array("afd"=>$record->AFDID) )
    );
    //$debateDates->save($record);
}
    
echo "</pre>";
echo "This is Done.";

mysqli_close($conn);  

?>