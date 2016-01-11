<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Sequences sunburst</title>
    <script src="d3.v3.min.js"></script>
    <link rel="stylesheet" type="text/css" href="diagram3.css"/>
  </head>
  <body>
    <div id="main">
      <div id="sequence"></div>
      <div id="chart">
        <div id="explanation" style="visibility: hidden;">
          <span id="percentage"></span><br/>
          of visits begin with this sequence of pages
        </div>
      </div>
    </div>
    <div id="sidebar">
      <input type="checkbox" id="togglelegend"> Legend<br/>
      <div id="legend" style="visibility: hidden;"></div>
    </div>
    <script type="text/javascript" src="data3.js"></script>
    <script type="text/javascript" src="diagram3.js"></script>
    <script type="text/javascript">
      // Hack to make this example display correctly in an iframe on bl.ocks.org
      d3.select(self.frameElement).style("height", "700px");
  </script> 
  </body>
</html>
