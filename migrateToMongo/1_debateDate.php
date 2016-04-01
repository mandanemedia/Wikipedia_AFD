<?php
require_once "config.php";
ini_set('MAX_EXECUTION_TIME', -1);

$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);

$sql = "select * from debateDatelist ; ";
$records = array();
if ( $result=mysqli_query($conn,$sql) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
        $records[] = $obj;

echo "<pre>";
print_r($records);
echo "</pre>";

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
    $debateDates->save($record);

mysqli_close($conn);  

?>