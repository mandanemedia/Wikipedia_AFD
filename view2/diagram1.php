<!DOCTYPE html>
<html>
    <head>
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
    <div id="chart"></div>
    <div class="sgvSection"></div>
    <!-- Note this is important that D3 script be in the body section !-->
    <script src="diagram1.js"></script>
    </body>
</html>