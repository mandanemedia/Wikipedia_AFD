var margin = { top: 40, right: 0, bottom: 20, left: 40 };
var width = 1024 - margin.left - margin.right;
var height = 1024 - margin.top - margin.bottom;
var heightAdded = 150;
var gridSize = Math.floor(width / 40); //42
var maxAfDsPercentage = 70.20;
var legendPercentageAdjust = maxAfDsPercentage/391.77;
var legendElementWidth = gridSize + 1 ; 
var colors = ["#dddddd", //White 0.00
              "#FFFFCC", "#D9F7D4", "#B2F0DB", "#8CE8E3", "#66E0EB", "#40D9F2", "#19D1FA", 
              "#15B2FB", "#1292FC", "#3D54ED", //Blue
               "#473FCB", "#522AAA", "#5C1588", "#660066", //Red
               "#520052", "#3D003D", "#240024", "#000000"];  // Purple 0.9 0.95 0.99
var colorsLegend = [0,
                    0.0001, 0.0003, 0.0004, 0.0005, 0.0007, 0.0009, 
                    0.001, 0.002, 0.003, 0.004, 0.05, 0.07, 0.09,
                    0.1, 0.2, 0.6, 0.7, 0.8, 0.9999];
var colorText = ["Delete" , "", "", "Other", "", "", "Keep"];
var max_Keep = 0;

getData();
//http://www.w3schools.com/tags/ref_colormixer.asp
//http://synthesis.sbecker.net/articles/2012/07/16/learning-d3-part-6-scales-colors
function getData()
{
    giveData = [];  
    d3.json("data1.php", function(error, data) {
        //get the data from Database from PHP page
        data.forEach(function(d){
            d.average = ((+d.percentageOutcome_delete)*1 + (+d.percentageOutcome_other)*2 + (+d.percentageOutcome_keep)*3 ) /10000;
            d.visualPercentage = (+d.percentageAfDs)/ 1792;
            //if(+d.totalAFDs>0)
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
    height = row_delete_labelData.length * ( gridSize ) + margin.bottom ;
    var width2 = row_delete_labelData.length * ( gridSize ) + margin.right ;
     
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
        .domain(colorsLegend)
        .range(colors);
    
    afdsMap.transition().duration(2000)
        .style("fill", function(d) {
            return colorScale(+d.visualPercentage); 
        });
    
      afdsMap.append("title").text(function(d) { return  Math.round((+d.visualPercentage* maxAfDsPercentage*100).toFixed(2)) +" AfDs"; });
                                        
      var legend = svg.selectAll(".legend")
          .data(colorsLegend)           
          .enter().append("g")          
          .attr("class", "legend");     
      var legendY = 0 ;            
      var legendX = width2  + gridSize - 20 ;                  
                  
                                        
      /*legend.append("rect")             
        .attr("x", legendX )  
        .attr("y",  function(d, i) { return legendElementWidth * i + legendY; })          
        .attr("width", legendElementWidth - 10 )
        .attr("height", gridSize- 1)   
        .style("fill", function(d, i) { 
            return colorScale(+colorsLegend[i]); 
        }); */
        
      var legendWidthScale = d3.scale.linear()
        .domain([ 0, 1, 10, 7020])
        .range([2, 4, 8, 280]);
        
      var legendXScale = d3.scale.linear()
        .domain([ 0,    3,      4,       5,     14, 28, 350, 700, 1400, 7020])
        .range([ -20,   -20,    -17,     -17,     -10 , -10 , 10,  25,  62,   277]);
      
      legend.append("text")             
        .attr("class", "legendLabel") 
        .attr("text-anchor", "end" )   
        .text(function(d,i) {   return Math.round((colorsLegend[i]*100).toFixed(2)*maxAfDsPercentage) +" AfDs"; })
        .attr("x",  function(d, i) {
            return legendX +147+ legendXScale(+(Math.round((colorsLegend[i]*100).toFixed(2)*maxAfDsPercentage)));
        })
        .attr("y", function(d, i) { return - 2+legendElementWidth + legendElementWidth * i + legendY - (gridSize/3); });
      
      legend.append("text")             
        .attr("class", "legendLabel")  
        .text(function(d,i) {   if((colorsLegend[i]*100).toFixed(2)<60)
                                    return "%0"+(colorsLegend[i]*100*legendPercentageAdjust).toFixed(3);
                                else 
                                    return "%"+(colorsLegend[i]*100*legendPercentageAdjust).toFixed(3); })
        .attr("x", legendX + 20  )
        .attr("y", function(d, i) { return - 2+legendElementWidth + legendElementWidth * i + legendY - (gridSize/3); });
      
      
      legend.append("rect")             
        .attr("x", legendX + 80 )  
        .attr("y",  function(d, i) { return legendElementWidth * i + legendY ; })          
        .attr("width", function(d, i) {
            //if(i==0)
            //    return 0;
            return legendWidthScale(+(Math.round((colorsLegend[i]*100).toFixed(3)*maxAfDsPercentage)));
        })
        .attr("height", gridSize- 1)   
        .style("fill", function(d, i) { 
            return colorScale(+colorsLegend[i]); 
        }); 
       
                                        
      svg.append("text")                
        .attr("class", "legendText")    
        .text("AfDs' Population ")  // .text("Population  -->  Percentage")             
        .attr("x", legendX + 20 )
        .attr("y", legendY - 5  );    
      
      svg.append("text")                
        .attr("class", "legendText")    
        .text("Total Population:")              
        .attr("x", legendX + 18 )
        .attr("y", legendY + 535  );
        
      svg.append("text")                
        .attr("class", "legendText")    
        .text("%100.00 = 39177 AfDs")              
        .attr("x", legendX + 18 )
        .attr("y", legendY + 550  );  
                                         
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
        .text("Keep votes")             
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
        .text("Delete votes")
        .attr("x", 15 - margin.left   )
        .attr("y", 10 - margin.top  + 60 );

}