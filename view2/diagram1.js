var margin = { top: 20, right: 0, bottom: 10, left: 30 };
var width = 1024 - margin.left - margin.right;
var height = 2430 - margin.top - margin.bottom;
var gridSize = Math.floor(width / 40);
var legendElementWidth = gridSize*2;
var colors = ["#FFFFFF", "#FF0000", "#FFFF00", "#00FF00"]; 

getData();

//http://synthesis.sbecker.net/articles/2012/07/16/learning-d3-part-6-scales-colors
function getData()
{
    data1 = [];  
    d3.json("data1.php", function(error, data) {
        //get the data from Database View PHP format
        data.forEach(function(d){
            data1.push(d);
        });
        //load the data on the screen
        document.getElementById( 'loadingMessage' ).innerHTML = "&nbsp";
        //refine based on the target output
        //data1 = new Array(data1);
        maxTotal = d3.max(data1.map(function(d) { return +d.total; }));
        sizeScale = d3.scale.linear()
            .domain([1,maxTotal])
            .range([6,gridSize]);
        updateData();
    });
}
function isInArray(array, search)
{
    return array.indexOf(search) >= 0;
}
  
function updateData()
{
    var times = new Array();
    for(var i=1; i<=28; i++)
        times.push(i.toString());
    
    days = new Array();
    //days_test = data1.map(function(d) { return d.day.toString(); });
  
    data1.forEach(function(d){
        if(!isInArray(days, d.totalComments_delete.toString()))
            days.push(d.totalComments_delete.toString());
    });
    
    data1.forEach(function(d){
        d.day_int  = +days.indexOf(d.totalComments_delete);
        d.sort  = +days.indexOf(d.totalComments_delete);
    });
    
    
    //overwrite to height
    height = days.length * ( gridSize ) + 60;
    
      var colorScale = d3.scale.quantile()
          .domain([0,10000,20000,30000])
          .range(colors);
      
      
      d3.select("#orderOption").property( "value", "0" );  
      d3.select("#chart").select("svg").remove();
     
      svg = d3.select("#chart").append("svg")
          .attr("width", width + margin.left + margin.right)
          .attr("height", height + margin.top + margin.bottom )
          .style("background-color","#EEE")
          .append("g")
          .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    
      dayLabels = svg.selectAll(".dayLabel")
          .data(days)
          .enter().append("text")
            .text(function (d) { return d; })
            .attr("x", 0)
            .attr("y", function (d, i) { return i * gridSize; })
            .style("text-anchor", "end")
            .attr("transform", "translate(-6," + gridSize / 1.5 + ")")
            .attr("class", function (d, i) { return ((i < 0) ? "dayLabel mono axis axis-workweek" : "dayLabel mono axis"); });
    
      var timeLabels = svg.selectAll(".timeLabel")
          .data(times)
          .enter().append("text")
            .text(function(d) { return d; })
            .attr("x", function(d, i) { return i * gridSize; })
            .attr("y", 0)
            .style("text-anchor", "middle")
            .attr("transform", "translate(" + gridSize / 2 + ", -6)")
            .attr("class", function(d, i) { return ((i >= 4 && i <= 9) ? "timeLabel mono axis axis-worktime" : "timeLabel mono axis"); });
        
      heatMap = svg.selectAll(".hour")
          .data(data1)
          .enter().append("rect")
                .attr("class", "hour bordered")
                .attr("id", function (d){ return "rect_"+d.totalComments_delete+"_"+d.totalComments_keep })
                .style("fill", colors[0])
                .attr("rx", function (d){return 4;})
                .attr("ry",  function (d){return 4;})
                .attr("x", function(d) {return (+d.totalComments_keep-1) * gridSize ;})
                .attr("y", function(d) {return +d.sort * gridSize ; })
                .attr("height", function (d){ return gridSize; })
                .attr("width", function (d){return gridSize;});
    
      heatMap.transition().duration(2000)
          .style("fill", function(d) {
            var average = 0;
            if((+d.percentageOutcome_delete) > (+d.percentageOutcome_other)
                && (+d.percentageOutcome_delete) >(+d.percentageOutcome_keep))
                average = 10000 ;//* (+d.percentageOutcome_delete);
            else if ((+d.percentageOutcome_keep) > (+d.percentageOutcome_delete)
                && (+d.percentageOutcome_keep) >(+d.percentageOutcome_other))
                average = 30000 ;//* (+d.percentageOutcome_keep);
            else if ((+d.percentageOutcome_other) > (+d.percentageOutcome_delete)
                && (+d.percentageOutcome_other) >(+d.percentageOutcome_keep))
                average = 20000 ;//* (+d.percentageOutcome_other) ;
            return colorScale(average); 
          });
    
      heatMap.append("title").text(function(d) { return "D:"+d.percentageOutcome_delete+" O:"+d.percentageOutcome_other+" K:"+d.percentageOutcome_keep ; });
      
      
      var legend = svg.selectAll(".legend")
          .data(colors)
          .enter().append("g")
          .attr("class", "legend");
      var legendY = height-20;
      var legendX = 35;
      
      legend.append("rect")
        .attr("x", function(d, i) { return legendElementWidth * i - legendX; })
        .attr("y", legendY )
        .attr("width", legendElementWidth)
        .attr("height", gridSize / 2)
        .style("fill", function(d, i) { return colors[i]; });
    
      
      var colorText = ["Keep", "" , "", "" , "Delete"];
      legend.append("text")
        .attr("class", "mono")
        .text(function(d,i) { return colorText[i]; })
        .attr("x", function(d, i) { return legendElementWidth * i + (legendElementWidth/2) - 22 - legendX; })
        .attr("y", legendY + gridSize -2  );
        
      svg.append("text")
        .attr("class", "mono")
        .text("Each rectangle represents the average color for the number of participants on a specific date.")
        .attr("x", function(d ) { return  (legendElementWidth/2) - 28 - legendX; })
        .attr("y", legendY - 5  );

}