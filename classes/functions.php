<?php

   function getNextSibling($HTML_Anchor, $index)
   {
       if($index ==1 )
       {    
            //echo "<br/>__________________<br/>Before:".$HTML_Anchor." "; 
            //echo "<br/><br/>END :: getNextSibling(index:$index)<br/>".$HTML_Anchor->next_sibling()." ";
            return $HTML_Anchor->next_sibling();
       }
       else if($index > 1)
       {
            //echo "<br/>__________________<br/>getNextSibling(HTML_Anchor,index:$index)";
            return getNextSibling($HTML_Anchor->next_sibling(),--$index);
       }
       else
       {
            //echo "<br/>__________________<br/>Error";
            return null;
       }
       
   }
   
   
   function find_innder_dd($source)
   {
        preg_match("'<dd>(.*?)</dd>'si", $source, $match);
        return $match[0];
   }
   

    function closetags($html) {
        // for bug in presentation
        //if ( preg_match("/href=/i",$html) )
        //    $html .= "\"></a>";
            
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        return $html;
    } 
    
    function openDBConnection(&$conn, &$conn_NeedToClose)
    {
        if(empty($conn))
        {
            $conn_NeedToClose = true;
            $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
        }
    }
    
    function closeDBConnection(&$conn, &$conn_NeedToClose)
    {
        if($conn_NeedToClose)
        {
            if(is_resource($conn))
                mysqli_close($conn);
            $conn_NeedToClose = false;
            unset($conn);
        }
    }
?>