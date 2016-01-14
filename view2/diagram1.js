var margin = { top: 40, right: 0, bottom: 30, left: 40 };
var width = 1024 - margin.left - margin.right;
var height = 1024 - margin.top - margin.bottom;
var gridSize = Math.floor(width / 40);
var legendElementWidth = gridSize*2;
var colors = ["#FFFFFF", "#FF0000", "#FFFF00", "#006400"]; 
var colorsLegend = [1, 1.33, 1.66, 2, 2.33, 2.66, 3];
var colorText = ["Delete" , "", "", "Other", "", "", "Keep"];
var max_Keep = 0;

getData();

//http://synthesis.sbecker.net/articles/2012/07/16/learning-d3-part-6-scales-colors
function getData()
{
    giveData = [];  
    d3.json("data1.php", function(error, data) {
        //get the data from Database from PHP page
        data.forEach(function(d){
            d.average = ((+d.percentageOutcome_delete)*1 + (+d.percentageOutcome_other)*2 + (+d.percentageOutcome_keep)*3 ) /10000;
            giveData.push(d);
        });
        //load the data on the screen
        document.getElementById( 'loadingMessage' ).innerHTML = "&nbsp";
        updateData();
    });
}
function isInArray(array, search)
{
    return array.indexOf(search) >= 0;
}
  
function updateData()
{
    max_Keep = d3.max(giveData.map(function(d) { return +d.totalComments_keep; }))
    var column_keep_labelData = new Array();
    for(var i=1; i<=max_Keep; i++)
        column_keep_labelData.push(i.toString());
    
    row_delete_labelData = new Array();
    giveData.forEach(function(d){
        if(!isInArray(row_delete_labelData, d.totalComments_delete.toString()))
            row_delete_labelData.push(d.totalComments_delete.toString());
    });
    
    //overwrite to height
    height = row_delete_labelData.length * ( gridSize ) + margin.bottom;
     
    svg = d3.select("#chart").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom )
        .style("background-color","#eee")
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");
    
    var rowLabels = svg.selectAll(".rowLabel")
        .data(row_delete_labelData)
        .enter().append("text")
            .text(function (d) { return d; })
            .attr("x", 0)
            .attr("y", function (d, i) { return i * gridSize; })
            .style("text-anchor", "end")
            .attr("transform", "translate(-6," + gridSize / 1.5 + ")")
            .attr("class", function (d, i) { return "rowLabel"; });
    
    var columnLabels = svg.selectAll(".columnLabel")
        .data(column_keep_labelData)
        .enter().append("text")
            .text(function(d) { return d; })
            .attr("x", function(d, i) { return i * gridSize; })
            .attr("y", 0)
            .style("text-anchor", "middle")
            .attr("transform", "translate(" + gridSize / 2 + ", -6)")
            .attr("class", function(d, i) { return "columnLabel"; });
        
    var afdsMap = svg.selectAll(".afd")
        .data(giveData)
        .enter().append("rect")
            .attr("class", "afd bordered")
            .attr("id", function (d){ return "rect_"+d.totalComments_delete+"_"+d.totalComments_keep })
            .style("fill", colors[0])
            .attr("rx", function (d){return 4;})
            .attr("ry",  function (d){return 4;})
            .attr("x", function(d) {return (+d.totalComments_keep-1) * gridSize ;})
            .attr("y", function(d) {return (+d.totalComments_delete-1) * gridSize ; })
            .attr("height", function (d){ return gridSize; })
            .attr("width", function (d){return gridSize;});
    
    var colorScale = d3.scale.linear()
        .domain([0, 1,  2,  3])
        .range(colors);
    
    afdsMap.transition().duration(2000)
        .style("fill", function(d) {
            var average = 0;
            if ((+d.totalAFDs) > 0 )
               average = (+d.average);
            return colorScale(average); 
        });
    
      afdsMap.append("title").text(function(d) { return "A:"+d.average+" D:"+d.percentageOutcome_delete+" O:"+d.percentageOutcome_other+" K:"+d.percentageOutcome_keep ; });
                                        
      var legend = svg.selectAll(".legend")
          .data(colorsLegend)           
          .enter().append("g")          
          .attr("class", "legend");     
      var legendY = height ;            
      var legendX = 0;                  
                                        
      var colorScalelegend = d3.scale.linear()
        .domain([1,  2,  3])            
        .range(colors);                 
                                        
      legend.append("rect")             
        .attr("x", function(d, i) { return legendElementWidth * i - legendX; })
        .attr("y", legendY )            
        .attr("width", legendElementWidth)
        .attr("height", gridSize / 2)   
        .style("fill", function(d, i) { 
            return colorScale(+colorsLegend[i]); 
        });                             
                                        
      legend.append("text")             
        .attr("class", "legendLabel")   
        .text(function(d,i) { return colorText[i]; })
        .attr("x", function(d, i) { return legendElementWidth * i + (legendElementWidth/2) - 18 - legendX; })
        .attr("y", legendY + gridSize -2  );
                                        
      svg.append("text")                
        .attr("class", "legendText")    
        .text("Outcomes:")              
        .attr("x", function(d ) { return  (legendX); })
        .attr("y", legendY - 5  );      
                                        
      svg.append("rect")                
        .attr("x", max_Keep * gridSize - legendElementWidth  )
        .attr("y", legendY )            
        .attr("width", legendElementWidth)
        .attr("height", gridSize / 2)   
        .style("fill", "white");        
                                        
      svg.append("text")                
        .attr("class", "legendText")    
        .text("No AfD")                 
        .attr("x", max_Keep * gridSize - legendElementWidth + 3 )
        .attr("y", legendY + gridSize -2  );
                                        
      svg.append("line")                
        .attr("x1", 15 - margin.left)   
        .attr("x2", 10 - margin.left + 50)
        .attr("y1", 10 - margin.top )   
        .attr("y2", 10 - margin.top)    
        .attr("stroke", "green")        
        .attr("stroke-width", "1");     
                                        
     var point1 = (10 - margin.left + 50)+","+ (10 - margin.top - 3);
     var point2 = (10 - margin.left + 55)+","+ (10 - margin.top );
     var point3 = (10 - margin.left + 50)+","+ (10 - margin.top + 3);
     svg.append("polygon")              
        .attr("points", point1 + " " + point2 + " " + point3 )
        .style("fill", "green");        
                                        
     svg.append("text")                 
        .text("Number of Keep votes")             
        .attr("x", margin.left - 10  )  
        .attr("y", 10 - margin.top + 5);
    
    
    svg.append("line")
        .attr("x1", 15 - margin.left)
        .attr("x2", 15 - margin.left)
        .attr("y1", 9 - margin.top )
        .attr("y2", 10 - margin.top  + 50)
        .attr("stroke", "red")
        .attr("stroke-width", "1");
        
     var point1 = (15 - margin.left - 3)+","+ (10 - margin.top  + 50 );
     var point2 = (15 - margin.left    )+","+ (10 - margin.top  + 55 );
     var point3 = (15 - margin.left + 3)+","+ (10 - margin.top  + 50);
     svg.append("polygon")
        .attr("points", point1 + " " + point2 + " " + point3 )
        .style("fill", "red");
    
     svg.append("text")
        .style("writing-mode", "tb")
        .text("Number of Delete votes")
        .attr("x", 15 - margin.left   )
        .attr("y", 10 - margin.top  + 60 );

}