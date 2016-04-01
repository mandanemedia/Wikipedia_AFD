<?php
/*
http://bl.ocks.org/NPashaP/raw/9796212/8ed537693ddcb720b79cae93d979385c3c3b08c3/
*/
require_once "config.php";
ini_set('MAX_EXECUTION_TIME', -1);

$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);

$sql2 = "select * from matrix_snakey2 ; ";
$records2 = array();
if ( $result=mysqli_query($conn,$sql2) or die(mysqli_error($conn)) )
    while ( $obj=mysqli_fetch_object($result) )
        $records2[] = $obj;

//echo '{';
/*echo '"nodes" :';
    print json_encode($records);
echo ',
   "links" :';*/
echo '[';
    $closeTag = false;
    if ( $result=mysqli_query($conn,$sql2) or die(mysqli_error($conn)) )
        while ( $obj=mysqli_fetch_object($result) )
        {
            if($closeTag)
                echo "],[";
            else
                echo "[";
                
                echo '"'.$obj->category.'",';
                echo '"'.$obj->policy.'",';
                echo $obj->value.',3';
                //echo '"'.$obj->policyTitle.'"';
            $closeTag = true;
        }
        echo "]";
echo ']';

mysqli_close($conn);  

?>