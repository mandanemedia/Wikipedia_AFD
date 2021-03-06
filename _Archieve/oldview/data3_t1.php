<?php
require_once "config.php";
ini_set('MAX_EXECUTION_TIME', -1);

if(isset($_GET["startID"]))
    $startID = trim($_GET["startID"]);

if(isset($_GET["endID"]))
    $endID = trim($_GET["endID"]);


/*
select EndResult, counter, date, count(counter) 
from testing
GROUP BY date, EndResult, counter
ORDER BY counter
*/
//Make the Database Query
//$offset = 0; 
//$limit = 57;
$conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
$sql = "select  debateDate_Value as date, otherComment_CounterTime AS counter,  count(otherComment_CounterTime) AS EndResult_DateCounter, EndResult
        from afd
        inner join debatedatelist on afd.debateDateListID = debatedatelist.debateDateListID ";
if(!empty($startID) && !empty($endID)  ) 
    $sql .= " where afd.debateDateListID BETWEEN $startID AND $endID ";
else
    $sql .= " where afd.debateDateListID = 1 ";

$sql .= " GROUP BY date, EndResult, counter";
$sql .= " ORDER BY counter ";
    

//$sql .= "ORDER BY debateDate_Value";
//if(!empty($limit)) 
//    $sql .= " LIMIT $limit";
//if(!empty($offset) && !empty($limit)) 
//  $sql .= " OFFSET $offset;";
//$sql .= "group by date ";
$sql .= " LIMIT 1000";

//Get the Date and store it in array
$records = array();
if ( $result=mysqli_query($conn,$sql) )
    while ( $obj=mysqli_fetch_object($result) )
        $records[] = $obj;

$records = convertEndResult_to_ColorCode($records);
//$records = calculateCounter_for_ColorCode2($records);
echo get_JsonFormat_simple($records);
 
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

function convertEndResult_to_ColorCode($givenRecords)
{     
    $outputRecords = array();
    $id=0;
    foreach($givenRecords as $obj)
    {
        $newObject = new StdClass();
        $newObject->id = $id;
        foreach($obj as $key => $value)
        {   
            //display EndResult differently
            if( strcasecmp($key, "EndResult") ==0 )
            {
                $value = strip_tags($value);
                if( strcasecmp($value, "keep") !=0 && strcasecmp($value, "delete") )
                {
                    $newObject->color =  2;
                    //$output .= $comma_2.'"'."color"."\": \"2\"";
                }
                else
                {
                    if( strcasecmp($value, "keep") == 0)
                        $newObject->color =  1;
                        //$output .= $comma_2.'"'."color"."\": \"1\"";
                    else 
                        $newObject->color =  3;
                        //$output .= $comma_2.'"'."color"."\": \"3\"";
                }
            }
            //other key
            else
                $newObject->$key =  $value;
                //$output .= $comma_2.'"'.$key."\": \"".$value ."\"";
        }
        $outputRecords[] = $newObject;
        $id++;
    }
    return $outputRecords;
}

//echo in tsv format on screen in the simple format 
function get_tsv_simple($records)
{     
    $echo_key = false;
    foreach($records as $obj)
    {
        if(!$echo_key){
            foreach($obj as $key => $value)
            {
                $output .= "\"".$key."\" ";
                $echo_key=true;
            }
        }
        foreach($obj as $key => $value)
        {
            $output .= "\"". $value . "\"";
        }
    }
    return $output;
}

mysqli_close($conn);  

?>