var margin = { top: 40, right: 0, bottom: 30, left: 40 };
var width = 1024 - margin.left - margin.right;
var height = 1024 - margin.top - margin.bottom;
var gridSize = Math.floor(width / 40);
var legendElementWidth = gridSize*2;
var colors = ["#FFFFFF", "#FF0000", "#FFFF00", "#006400"]; 
var colorsLegend = [1, 1.33, 1.66, 2, 2.33, 2.66, 3];
var colorText = ["Delete" , "", "", "", "", "", "Keep"];
var max_Keep = 0;
var legendY ;

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
    
      afdsMap.append("title")
            .text(function(d) { 
                return "Delete Outcome: "+(+d.percentageOutcome_delete/100).toFixed(0)+"%\nOther Outcome: "+(+d.percentageOutcome_other/100).toFixed(0)+"%\nKeep Outcome: "+(+d.percentageOutcome_keep/100).toFixed(0) +"%"; 
            });
                                        
      var legend = svg.selectAll(".legend")
          .data(colorsLegend)           
          .enter().append("g")          
          .attr("class", "legend");     
      legendY = height ;            
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
        .text("Outcome:")              
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
        .text("The Number of Keep votes")             
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
        .text("The Number of Delete votes")
        .attr("x", 15 - margin.left   )
        .attr("y", 10 - margin.top  + 60 );

}



//Start Of second Section
var deleteLayer =[];
var keepLayer = [];
var y0 = [];

var n = 0, // number of layers
    m = 0, // number of samples per layer
    yGroupMax = 0, 
    yStackMax = 0;

var layers = [];
var layer, x, y, xAxis, yAxis, rect;

var barchartWidth = 410;
var barchartHeight = 620;
var shiftX = 617;
var shiftY = 2;
var width_2 = barchartWidth - margin.left - margin.right,
    height_2 = barchartHeight - margin.top - margin.bottom;

var checkGrouped = true;

d3.json("data1_1.php", function(error, data) {
            data.forEach(function(d,i){
                    deleteLayer.push({"x":+d.x, "y":+d.y, "y0":0});
                    y0[i] = +d.y;
                });
            d3.json("data1_2.php", function(error, data) {
            data.forEach(function(d,i){
                    keepLayer.push({"x":+d.x, "y":+d.y, "y0":y0[i]});
                });
                
            //enforce to have same number of keep and delete
            
            layers.push(deleteLayer); 
            layers.push(keepLayer);
            
            n = 2; 
            mStart= 2;
            m = 25;
            yGroupMax = d3.max(layers, function(layer) { return d3.max(layer, function(d) { return d.y; }); });
            yStackMax = d3.max(layers, function(layer) { return d3.max(layer, function(d) { return d.y0 + d.y; }); });
        
            x = d3.scale.ordinal()
                .domain(d3.range(mStart, m))
                .rangeRoundBands([0, width_2], .08);
            
            y = d3.scale.linear()
                .domain([0, yStackMax])
                .range([height_2, 0]);
            
            yAxisValueStack = d3.scale.linear()
                .domain([0, yStackMax*500])
                .range([height_2, 0]);
                
            yAxisValueGroup = d3.scale.linear()
                .domain([0, yGroupMax*500])
                .range([height_2, 0]);
            
            color = d3.scale.linear()
                .domain([0, n - 1])
                .range(["rgb(255, 0, 0)", "rgb(0, 100, 0)"]);
            
            xAxis = d3.svg.axis()
                .scale(x)
                .tickSize(0)
                .tickPadding(6)
                .orient("bottom");
            
            yAxis = d3.svg.axis()
                .scale(yAxisValueGroup)
                .tickSize(0)
                .tickPadding(6)
                .orient("left")
                  .ticks(20)
                  .tickFormat(d3.format("s"));
            
            layer = svg.selectAll(".layer")
                .data(layers)
              .enter().append("g")
                .attr("class", "layer")
                .style("fill", function(d, i) { return color(i); });
            
            rect = layer.selectAll("rect")
                .data(function(d) { return d; })
                .enter().append("rect")
                .attr("class", "bordered2")
                .attr("x", function(d) {
                    return x(d.x)+shiftX; 
                })
                .attr("y", height_2+shiftY)
                .attr("width", x.rangeBand())
                .attr("height", 0);
            
            rect.transition()
                .delay(function(d, i) { return i * 10; })
                .attr("y", function(d) { return y(d.y0 + d.y)+shiftY; })
                .attr("height", function(d) { return y(d.y0) - y(d.y0 + d.y); });
            
            svg.append("g")
                .attr("class", "x axis")
                .attr("transform", "translate("+shiftX+"," + (height_2+shiftY) + ")")
                .call(xAxis);
                
            svg.append("g")
                .attr("class", "y axis")
                .attr("transform", "translate(" + (shiftX ) + ", "+shiftY+")")
                .call(yAxis);
            
            change();
            
            svg.append("text")                
                .attr("class", "legendTextMain")    
                .text("Outcome By:")      
                .attr("x", shiftX - 33 )
                .attr("y", 10+shiftY - 25);
                
            //Hover function
            svg.append("rect")
            .attr("id", "Stacked")
            .style("fill", "#888")
            .style("cursor", "pointer")
            .style("stroke-width", "2")
            .style("stroke", "#888")
            .attr("x", function (d){return  shiftX + 213 - 163;} )
            .attr("y", function (d){return  shiftY-25;} )
            .attr("height",function (d){return  12;} )
            .attr("width", function (d){return  12;})
            .on("mouseenter", function (d){
                checkGrouped = false;
                change();
            });
            svg.append("text")                
                .attr("class", "legendText")  
                .text("Stacked")            
                .attr("x", shiftX - 9 + 25 + 213 - 163 )
                .attr("y", 10+shiftY - 25);
                
            svg.append("rect")
            .attr("id", "Grouped")
            .style("fill", "#fff")
            .style("cursor", "pointer")
            .style("stroke-width", "2")
            .style("stroke", "#888")
            .attr("x", function (d){return  shiftX +285 - 163 ;} )
            .attr("y", function (d){return  shiftY-25;} )
            .attr("height",function (d){return  12;} )
            .attr("width", function (d){return  12;})
            .on("mouseenter", function (d){
                checkGrouped = true;
                change();
            });
            svg.append("text")                
                .attr("class", "legendText")    
                .text("Grouped")       
                .attr("x", shiftX - 9 + 25 +285 - 163)
                .attr("y", 10+shiftY - 25);
        
            });
            
            var bar1Shifty =  shiftY ;
            var bar1Shiftx =  shiftX + width_2 -15 ;
            
            var point1 = (bar1Shiftx - 3)+","+ (bar1Shifty + 6);
            var point2 = (bar1Shiftx    )+","+ (bar1Shifty + 1);
            var point3 = (bar1Shiftx + 3)+","+ (bar1Shifty + 6 );
            svg.append("polygon")
                .attr("points", point1 + " " + point2 + " " + point3 )
                .style("fill", "#666");
            
            svg.append("line")                
                .attr("x1", bar1Shiftx)   
                .attr("x2", bar1Shiftx)
                .attr("y1", bar1Shifty + 4)   
                .attr("y2", bar1Shifty + 50 )   
                .attr("stroke", "#666")        
                .attr("stroke-width", "1");
                
            svg.append("text")
            .style("writing-mode", "tb")
            .text("The Number of AfDs (only Delete and Keep Outcomes) ")
            .attr("x", bar1Shiftx   )
            .attr("y", bar1Shifty + 55 );
            
            
            point1 = (bar1Shiftx - 6 )+","+ (bar1Shifty + 3 );
            point2 = (bar1Shiftx - 1 )+","+ (bar1Shifty  );
            point3 = (bar1Shiftx - 6 )+","+ (bar1Shifty - 3  );
            svg.append("polygon")
                .attr("points", point1 + " " + point2 + " " + point3 )
                .style("fill", "#666");
            
            svg.append("line")                
                .attr("x1", bar1Shiftx - 50)   
                .attr("x2", bar1Shiftx - 4 ) 
                .attr("y1", bar1Shifty - 0 )   
                .attr("y2", bar1Shifty - 0 )   
                .attr("stroke", "#666")        
                .attr("stroke-width", "1");
                
            svg.append("text")
                .text("The Number of Votes ")
                .attr("x", bar1Shiftx - 178 )
                .attr("y", bar1Shifty +4 );
                
                                             
            svg.append("rect")                
                .attr("x", bar1Shiftx  - legendElementWidth + 10  )
                .attr("y", legendY ) 
                .style("opacity", 0.9)               
                .attr("width", legendElementWidth)
                .attr("height", gridSize / 2)   
                .style("fill", "rgb(0, 100, 0)");        
                                            
            svg.append("text")                
                .attr("class", "legendText")    
                .text("Keep")                 
                .attr("x", bar1Shiftx   - legendElementWidth + 20  )
                .attr("y", legendY + gridSize -2  );         
                                        
            svg.append("rect")                
                .attr("x", bar1Shiftx  - legendElementWidth - legendElementWidth + 5  )
                .attr("y", legendY ) 
                .style("opacity", 0.9)               
                .attr("width", legendElementWidth)
                .attr("height", gridSize / 2)   
                .style("fill", "rgb(255, 0, 0)");        
                                            
            svg.append("text")                
                .attr("class", "legendText")    
                .text("Delete")               
                .attr("x", bar1Shiftx   - legendElementWidth - legendElementWidth + 10  )
                .attr("y", legendY + gridSize -2  );
                
            svg.append("text")                
                .attr("class", "legendText")    
                .text("Outcome:")              
                .attr("x", bar1Shiftx   - legendElementWidth - legendElementWidth - 52)
                .attr("y", legendY + 10  );  
  });


function change() {
  if (checkGrouped == true ) 
  {
        svg.select("#Grouped").style("fill", "#fff");
        svg.select("#Stacked").style("fill", "#888");
        transitionGrouped();
        
        yAxis.scale(yAxisValueGroup);
        svg.selectAll("g .y.axis")
           .call(yAxis)
  }
  else  
  {
        svg.select("#Grouped").style("fill", "#888");
        svg.select("#Stacked").style("fill", "#fff");
        transitionStacked();
        
        yAxis.scale(yAxisValueStack);
        svg.selectAll("g .y.axis")
            .call(yAxis);
  }
}

function transitionGrouped() {
  y.domain([0, yGroupMax]);

  rect.transition()
      .duration(500)
      .delay(function(d, i) { return i * 10; })
      .attr("x", function(d, i, j) { 
        return shiftX + x(d.x) + x.rangeBand() / n * j; 
      })
      .attr("width", x.rangeBand() / n)
    .transition()
      .attr("y", function(d) { return y(d.y)+shiftY; })
      .attr("height", function(d) { return height_2 - y(d.y); });
}

function transitionStacked() {
  y.domain([0, yStackMax]);

  rect.transition()
      .duration(500)
      .delay(function(d, i) { return i * 10; })
      .attr("y", function(d) { return y(d.y0 + d.y)+shiftY; })
      .attr("height", function(d) { return y(d.y0) - y(d.y0 + d.y); })
    .transition()
      .attr("x", function(d) { 
        return shiftX + x(d.x); 
      })
      .attr("width", x.rangeBand());
}
//End Of second Section