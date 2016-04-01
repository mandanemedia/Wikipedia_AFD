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
    
//$sql .= " ORDER BY otherComment_CounterTime desc";
    

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

//extra calculation in compare to Data1.php
//get min and max
$min = 0;
$max = 0;
foreach($records as $obj)
{
    if( $obj->counter > $max)
        $max = $obj->counter;
    if( $obj->counter < $min)
        $min = $obj->counter;
}
$records2 = array();
for($i=$min; $i<=$max; $i++)
{
    //$i -> id
    $counter_r = 0;
    $counter_g = 0;
    $counter_y = 0;
    foreach($records as $obj)
    {
        if((strcasecmp($obj->EndResult, "keep") == 0 )&& $obj->counter ==$i ) // 1 
            $counter_g++;
        else if((strcasecmp($obj->EndResult, "delete") == 0 )&& $obj->counter ==$i ) // 3 
            $counter_r++;
        else if((strcasecmp($obj->EndResult, "keep") != 0 ) && (strcasecmp($obj->EndResult, "delete") != 0 )&& $obj->counter ==$i ) // 3 
            $counter_y++;
    }
    if($counter_g>0 || $counter_y>0 || $counter_r>0) 
    {   
        $records2[] = array("counter"=>$i, "id"=>$counter_g, "color"=> 1 );
        $records2[] = array("counter"=>$i, "id"=>$counter_y, "color"=> 2 );
        $records2[] = array("counter"=>$i, "id"=>$counter_r, "color"=> 3 );
        $records2[] = array("counter"=>$i, "id"=>($counter_g+$counter_y+$counter_r), "color"=> 4 );
    }
}
//data1.push({"x":d.id, "y":d.counter, "color": d.color});

echo get_JsonFormat_simple($records2);
 
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