<!DOCTYPE html>
<html>
    <head>
        <style>
            body {
              font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
              margin: auto;
              position: relative;
              width: 960px;
              background-color: #eee;
            }
            h3 {
                margin: 0;
                padding: 0;
            }
            text {
              font: 10px sans-serif;
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
            rect.bordered2 {
                stroke: #fff;
                stroke-width:1px; 
                opacity: 0.9;  
            }
            text {
                font-size: 9pt;
                font-family: Consolas, courier;
                fill : #888888;
            }
            text.columnLabel {
                fill: green;
            }
            text.rowLabel {
                fill: red;
            }
            
            .axis path,
            .axis line {
              fill: none;
              stroke: #ccc;
              width: 1px;
              shape-rendering: crispEdges;
            }
            .y.axis g.tick:first-child
            {
                visibility: hidden;
            }
            .legendTextMain{
                text-shadow: #999 1px 1px ;
            }
        </style>
        <script src="d3.v3.min.js"></script>
    </head>
    <body>
    <h3 id="loadingMessage" >Please wait, it is loading....</h3>
    <div id="chart"></div>
    <!-- Note this is important that D3 script be in the body section !-->
    <script src="diagram1.js"></script>
    </body>
</html>