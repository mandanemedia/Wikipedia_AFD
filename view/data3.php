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
//$sql .= " ORDER BY counter ";
    

//$sql .= "ORDER BY debateDate_Value";
//if(!empty($limit)) 
//    $sql .= " LIMIT $limit";
//if(!empty($offset) && !empty($limit)) 
//  $sql .= " OFFSET $offset;";
//$sql .= "group by date ";
//$sql .= " LIMIT 1000";

//Get the Date and store it in array
$records = array();
if ( $result=mysqli_query($conn,$sql) )
    while ( $obj=mysqli_fetch_object($result) )
        $records[] = $obj;

$records = convertEndResult_to_ColorCode($records);
$records = calculateCounter_for_ColorCode2($records);
$records = groupBy_CountAndDate($records);

print json_encode($records);
//echo get_JsonFormat_simple($records);
 
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

function calculateCounter_for_ColorCode2($givenRecords)
{     
    $outputRecords = array();
    $givenRecords2 = $givenRecords;
    $claculated_Records = array();
    for($i=0; $i < count($givenRecords) ; $i++)
    {
        $obj = $givenRecords[$i];
        for($j=$i+1 ; $j < count($givenRecords2) ; $j++)
        {
            if(!in_array( $j, $claculated_Records))
            {
                $obj2 = $givenRecords2[$j];
                if( (strcasecmp($obj->date, $obj2->date) == 0 ) &&  $obj->counter == $obj2->counter &&  $obj->color == $obj2->color)
                {
                    $claculated_Records[] = $j;
                    $obj->EndResult_DateCounter++;
                }
            }
        }
        if(!in_array( $i, $claculated_Records))
        {
            $outputRecords[] = $obj;
        }
    }
    
    return $outputRecords;
}

function groupBy_CountAndDate($givenRecords)
{     
    $newID = 0;
    $outputRecords = array();
    $claculated_Records = array();
    for($i=0; $i < count($givenRecords) ; $i++)
    {
        $obj = $givenRecords[$i];
        if($obj->color == 1 )
        {
            $obj->color_1 = $obj->EndResult_DateCounter;
            $obj->color_2 = 0;
            $obj->color_3 = 0;
        }
        else if($obj->color == 2 )
        {
            $obj->color_1 = 0;
            $obj->color_2 = $obj->EndResult_DateCounter;
            $obj->color_3 = 0;
        }
        else if($obj->color == 3 )
        {
            $obj->color_1 = 0;
            $obj->color_2 = 0;
            $obj->color_3 = $obj->EndResult_DateCounter;
        }
        for($j=$i+1 ; $j < count($givenRecords) ; $j++)
        {
            //if(!in_array( $j, $claculated_Records))
            {
                $obj2 = $givenRecords[$j];
                if( (strcasecmp($obj->date, $obj2->date) == 0 ) &&  $obj->counter == $obj2->counter )
                {
                    $claculated_Records[] = $j;
                    if($obj2->color == 1 )
                        $obj->color_1 += $obj2->EndResult_DateCounter;
                    else if($obj2->color == 2 )
                        $obj->color_2 += $obj2->EndResult_DateCounter;
                    else if($obj2->color == 3 )
                        $obj->color_3 += $obj2->EndResult_DateCounter;
                }
            }
        }
        if(!in_array( $i, $claculated_Records))
        {
            unset($obj->color);
            unset($obj->EndResult_DateCounter);
            $obj->total = ($obj->color_1+$obj->color_2+$obj->color_3 );
            if($obj->total !=0 )
                $obj->average_color = round((($obj->color_1*1)+($obj->color_2*2)+($obj->color_3*3))/$obj->total, 2);
            else
                $obj->average_color = 0;
            
            $obj->id = $newID;
            $newID++;
            $outputRecords[] = $obj;
        }
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