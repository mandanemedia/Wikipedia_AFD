
<!DOCTYPE html>
<meta charset="utf-8">
<style>
body{
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    font-size: 11px;
    font-weight: 400;
    position: relative;
    width: 860px;
    margin: auto;
    background: #eee;
}
#chart{
    position: relative;
    background: #eee;
    width: 860px;
    float: left;
    clear: both;
}
svg text{
	font-size:12px;
}
rect{
	shape-rendering:crispEdges;
}
</style>
<body>
    <div id="chart">
    </div>
<script src="http://d3js.org/d3.v3.min.js"></script>
<script src="biPartite.js"></script>
<script>
var sales_data;
d3.json("diagram5.json", function(error, json) {
    sales_data = json;
    var width = 860, height = 600, margin ={b:0, t:45, l:150, r:50};
    
    var svg = d3.select("#chart")
    	.append("svg").attr('width',width).attr('height',(height+margin.b+margin.t))
    	.append("g").attr("transform","translate("+ margin.l+","+margin.t+")");
    
    var data = [ 
    	{data:bP.partData(sales_data,3), id:'Sales', header:["Wikipedia's Category"," Wikipedia's Policy", "Wikipedia Article for Deletion (AfD)"]}
    ];
    
    svg.append("text")                  
        .text("*Each Policy is clickable - it redirects you to the page in Wikipedia.")          
        .attr("x", 330 )
        .attr("y", height - 10 )
        .attr("fill", "#777" );
    bP.draw(data, svg);

});
var givenData ;
</script>
</body>