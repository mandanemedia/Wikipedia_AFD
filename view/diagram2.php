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
          background-color: #CCC;
        }
        text {
          font: 10px sans-serif;
        }
        
        .axis path,
        .axis line {
          fill: none;
          stroke: #000;
          shape-rendering: crispEdges;
        }
        
        form {
          position: absolute;
          right: 10px;
          top: 10px;
        }
        .chart div {
            font: 10px sans-serif;
            background-color: steelblue;
            text-align: right;
            padding: 3px;
            margin: 1px;
            color: white;
        }
        .shadow {
            -webkit-filter: drop-shadow( 3px -1px 1px #000 );
                    filter: drop-shadow( 3px -1px 1px #000 ); /* Same syntax as box-shadow */
            background-color: #EEE;
            border: 1px solid #AAA;
        }
        .sgvSection
        {   
            background-color: #EEE
            width: 950px;
            height: 580px;
            float:left;
            clear:both;
            margin-bottom: 20px;
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
                    echo "<option value=\"$obj->id\">$obj->date</option>";
            ?>
        </select>
        
        <b>Sorting By:</b> <select id="sortBy">
            <option value="0">Actual Occurrence</option>
            <option value="1">No. of Participants</option>
            <option value="2">Colour</option>
        </select>
    </form>
    <div class="sgvSection"></div>
    <!-- Note this is important that D3 script be in the body section !-->
    <script src="diagram2.js"></script>
    <h4>*Each bar represents an AfD.</h4>
    </body>
</html>