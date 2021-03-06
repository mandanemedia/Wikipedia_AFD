<?php
ini_set('MAX_EXECUTION_TIME', -1);
ini_set('max_execution_time', -1);
ini_set('memory_limit', '2048M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "config.php";

$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);

$sql = "select *
        from parentpolicy;";
$records = array();
if ( $result=mysqli_query($conn,$sql) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
    {
        $obj->parentPolicyID = utf8_encode($obj->parentPolicyID);
        $obj->parentPolicyURL = utf8_encode($obj->parentPolicyURL);
        $obj->parentPolicyTitle = utf8_encode($obj->parentPolicyTitle);
        $obj->policyFamily = utf8_encode($obj->policyFamily);
        $records[] = $obj;
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

$afds = $db->policies;
foreach ($records as $record)
{
    echo $record->parentPolicyID."<br/>";
    $afds->save($record);
}
    
echo "</pre>";
echo "This is Done.";

mysqli_close($conn);  

?>