<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            body {
              font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
              margin: auto;
              position: relative;
              width: 960px;
              background-color: #ddd;
            }
            text {
              font: 10px sans-serif;
            }
            
            form {
                margin-bottom: 20px;
                width: auto;
                float: right;
            }
            #chart
            {   
                width: 950px;
                height: 580px;
                float:left;
                clear:both;
            }
            rect.bordered {
                stroke: #aaa;
                stroke-width:1px;   
            }
        
            text.mono {
                font-size: 9pt;
                font-family: Consolas, courier;
                fill: #aaa;
            }
        
            text.axis-workweek {
                fill: #000;
            }
            text.axis-worktime {
                fill: #000;
            }
        </style>
        <script src="d3.v3.min.js"></script>
    </head>
    <body>
    <h3 id="loadingMessage" >Please wait, it is loading....</h3>
    <form>
        <b>Start Date:</b> <select id="selectedDate_Start">
            <?php
            require_once "config.php";
            
            //Make the Database Query
            $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            $sql = "select  debateDateListID as id, debateDate_Value as date
                    from debatedatelist
                    where debatedatelistID < 70 ;";
            
            if ( $result=mysqli_query($conn,$sql) )
                while ( $obj=mysqli_fetch_object($result) )
                    echo "<option value=\"$obj->id\">$obj->date</option>";
            ?>
        </select>
        <b>End Date:</b> <select id="selectedDate_End">
            <?php
            
            //Make the Database Query
            $conn = new mysqli(DB_Host, DB_User, DB_Password, DB_Name) or die("Connection failed: " . $conn->connect_error);
            $sql = "select  debateDateListID as id, debateDate_Value as date
                    from debatedatelist
                    where debatedatelistID < 70 ;";
            
            if ( $result=mysqli_query($conn,$sql) )
                while ( $obj=mysqli_fetch_object($result) )
                if($obj->id == 11)
                    echo "<option value=\"$obj->id\" selected=\"selected\">$obj->date</option>";
                    
                else
                    echo "<option value=\"$obj->id\">$obj->date</option>";
            ?>
        </select>
        <b>Display Size:</b> <select id="displaySize">
            <option value="0">No</option>
            <option value="1">Yes</option>
        </select>
        <b>Order by:</b> <select id="orderOption">
            <option value="0">Actual Occurrence</option>
            <option value="1">Size</option>
            <option value="2">Color</option>
            <option value="3">Color-Reverse</option>
        </select>
        
    </form>
    <div id="chart"></div>
    <div class="sgvSection"></div>
    <!-- Note this is important that D3 script be in the body section !-->
    <script src="diagram3.js"></script>
    </body>
</html>