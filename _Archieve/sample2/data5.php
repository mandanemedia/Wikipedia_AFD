<?php
require_once "config.php";
ini_set('MAX_EXECUTION_TIME', -1);

$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);

$sql = "select * from matrix_nodes ; ";
$records = array();
if ( $result=mysqli_query($conn,$sql) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
        $records[] = $obj;

$sql2 = "select * from matrix_snakey ; ";
$records2 = array();
if ( $result=mysqli_query($conn,$sql2) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
        $records2[] = $obj;

echo '{
   "nodes" :';
    print json_encode($records);
echo ',
   "links" :[';
    $closeTag = false;
    if ( $result=mysqli_query($conn,$sql2) or die(mysqli_error($conn)) )
        while ( $obj=mysqli_fetch_object($result) )
        {
            if($closeTag)
                echo "},{";
            else
                echo "{";
                
                echo "\"source\":".$obj->source." ,";
                echo "\"target\":".$obj->target." ,";
                echo "\"value\":".$obj->value;
            $closeTag = true;
        }
        echo "}]";
echo '}';

mysqli_close($conn);  

?>