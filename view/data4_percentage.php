<?php
require_once "config.php";
ini_set('MAX_EXECUTION_TIME', -1);

$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
$sql = "select *
        from visualization4_t0 
        union
        select *
        from visualization4_t1 
        union
        select *
        from visualization4_t2 
        union
        select *
        from visualization4_t3  
        union
        select *
        from visualization4_t4 ; ";

$records = array();
if ( $result=mysqli_query($conn,$sql) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
        $records[] = $obj;
 
print json_encode($records);

mysqli_close($conn);  

?>