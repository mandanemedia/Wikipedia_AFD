<?php
require_once "config.php";
ini_set('MAX_EXECUTION_TIME', -1);

if(isset($_GET["startID"]))
    $startID = trim($_GET["startID"]);

if(isset($_GET["endID"]))
    $endID = trim($_GET["endID"]);

//Make the Database Query
//$offset = 0; 
//$limit = 57;
$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
$sql = "select  afd.debateDateListID, AFDID as id, debateDate_Value as date, EndResult, otherComment_CounterTime AS counter 
        from afd
        inner join debatedatelist on afd.debateDateListID = debatedatelist.debateDateListID ";
if(!empty($startID) && !empty($endID)  ) 
    $sql .= " where afd.debateDateListID BETWEEN $startID AND $endID ";
else
    $sql .= " where afd.debateDateListID = 1 ";
    
$sql .= " ORDER BY otherComment_CounterTime desc";
    

//$sql .= "ORDER BY debateDate_Value";
//if(!empty($limit)) 
//    $sql .= " LIMIT $limit";
//if(!empty($offset) && !empty($limit)) 
//  $sql .= " OFFSET $offset;";

$sql .= " LIMIT 1000";

//Get the Date and store it in array
$records = array();
if ( $result=mysqli_query($conn,$sql) )
    while ( $obj=mysqli_fetch_object($result) )
        $records[] = $obj;

echo get_JsonFormat_EndResult($records);
 
//echo in json format on screen in the customized format
function get_JsonFormat_EndResult($records)
{     
    $output = "[";
    $comma_1 = "";
    foreach($records as $obj)
    {
        $output .= $comma_1."{";
        $comma_2 = "";
        foreach($obj as $key => $value)
        {   
            //display EndResult differently
            if( strcasecmp($key, "EndResult") ==0 )
            {
                $value = strip_tags($value);
                if( strcasecmp($value, "keep") !=0 && strcasecmp($value, "delete") )
                {
                    $output .= $comma_2.'"'.$key."\": \"$value\"";
                    $output .= $comma_2.'"'."color"."\": \"2\"";
                }
                else
                {
                    $output .= $comma_2.'"'.$key."\": \"".$value."\"";
                    if( strcasecmp($value, "keep") == 0)
                        $output .= $comma_2.'"'."color"."\": \"1\"";
                    else 
                        $output .= $comma_2.'"'."color"."\": \"3\"";
                    
                }
            }
            //other key
            else
                $output .= $comma_2.'"'.$key."\": \"".$value ."\"";
            $comma_2 = ", ";
        }
        $output .= "}";
        $comma_1 = ", \n";
    }
    $output .= "]";
    return $output;
}

//echo in json format on screen in the simple format 
function get_JsonFormat_simple($records)
{     
    $output = "[";
    $comma_1 = "";
    foreach($records as $obj)
    {
        $output .= $comma_1."{";
        $comma_2 = "";
        foreach($obj as $key => $value)
        {
            $output .= $comma_2.'"'.$key."\": \"". $value . "\"";
            $comma_2 = ", ";
        }
        $output .= "}";
        $comma_1 = ", \n";
    }
    $output .= "]";
    return $output;
}

mysqli_close($conn);  

?>