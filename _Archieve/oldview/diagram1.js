var data1 = [];
var loadingMessage = document.getElementById( 'loadingMessage' );
var startDate = 0;
var endDate = 0;
var n = 1; // number of layers
var m = 57; // number of samples per layer;
var barHeight = 10;
var barWidth = 12;
var textAdjustmentOnBar= 4;
var margin = {top: 10, right: 10, bottom: 0, left: 10};
var yArray;
var svg;
var layer;
var rect;
var texts;
var texts2;
   
getData();

//On change of SeletedDate-Dropdown, a message would load in loadingmessage and New data are feacthed from other php page
//Then ask to update the database
d3.select("#selectedDate_Start")
    .on("change", function() {
        getData();
    });
d3.select("#selectedDate_End")
    .on("change", function() {
        getData();
    });
d3.select("#sortBy")
    .on("change", function() {
        /* 
        0-> Actual Occurrence
        1-> No. of Participants
        2-> Colour
        */
        if(eval(d3.select(this).property('value'))==0)
            sortByActualOccurrence();
        else if(eval(d3.select(this).property('value'))==1)
            sortByNoOfParticipants();
        else if(eval(d3.select(this).property('value'))==2)
            sortByColour();
    });
    
function getData()
{
    startDate = eval(d3.select("#selectedDate_Start").property('value'));
    endDate = eval(d3.select("#selectedDate_End").property('value'));
    if(startDate <= endDate)
    {
        document.getElementById( 'loadingMessage' ).innerHTML = "Please wait, it is loading....";
        data1 = [];
        d3.select("body")
           .select(".sgvSection")
           .select("svg").remove();
        
        d3.select("#sortBy").property( "value", "0" );  
        d3.json("data1.php?startID="+startDate+"&endID="+endDate, function(error, data) {
            //get the data from Database View PHP format
            data.forEach(function(d){
                data1.push({"x":d.id, "date":d.date, "y":d.counter, "color": d.color});
            });
            //load the data on the screen
            document.getElementById( 'loadingMessage' ).innerHTML = "&nbsp";
            //refine based on the target output
            data1 = new Array(data1);
            updateData();
        });
    }
}
    
function updateData()
{
    /* data1 = {[{"x": "1", "id": "1", "color": "2", "y": "2"}, 
                {"z": "1", "id": "2", "color": "2", "y": "5"}, 
                {"z": "1", "id": "3", "color": "2", "y": "6"}, 
                {"z": "1", "id": "4",   "color": "2", "y": "5"}]}*/

    //get the y attribute and set them a new array
    // yArray = ["2", "5", "6", "5"];
    
    yArray = data1[0].map(function(d) { return +d.y; });
    
    //var maxDataHeight = Math.max.apply( Math, yArray ) * ( barHeight );
    var maxDataHeight = d3.max(yArray) * ( barHeight );
    var width = data1[0].length * barWidth;
    var height = maxDataHeight  + 20;
    if(width<900) width = 900;
    
    // set the height and width as well as the margins
    
    // First of All we need a contaioner to hole our svg
    // Second append g mean append a group and adjust the group to the middle of screen
    
    svg = d3.select("body")
            .select(".sgvSection")
        .append("svg")
            .attr("class", "shadow")
            .attr("width", width + margin.left + margin.right + 30 )
            .attr("height", height + margin.top + margin.bottom + 60)
            .style("background-color","#EEE")
            //.style("border", "1px solid #AAA")
            .append("g")
                .attr("transform", "translate(" + (margin.left+20) + "," + (margin.top+50) + ")");
        
    var xAxisScale = d3.scale.linear()
                            .domain([0,yArray.length])
                            .range([0,width]);
    var yAxisScale = d3.scale.linear()
                            .domain([0,d3.max(yArray) ])
                            .range([0,height]);

    var xAxis = d3.svg.axis()
                            .scale(xAxisScale); 
    var yAxis = d3.svg.axis()
                            .scale(yAxisScale); 
                            
    var xAxisGroup = svg.append("g")
                              .call(xAxis)
                              .attr("transform", "translate(" + 0 + "," + (height-10) + ")");
    
    var yAxisGroup = svg.append("g")
                              .call(yAxis)
                              .attr("transform", "translate(" + (-7) + "," + (height-10) + ") rotate (-90)")
                              .selectAll("text")
                                    .attr("transform", "rotate(180)" );;
       
    // Select all layer 
    // d in the function is refering to data
    // i refering to the index
    // for example is our data=[20,10,40] for the first item d=20 and i=0;
    layer = svg.selectAll(".layer")
        .data(data1)
      .enter().append("g")
        .attr("class", "layer")
        .style("fill", "black");
    
    rect = layer.selectAll("rect")
        .data(function(d) { return d; })
      .enter().append("rect")
        .attr("x", function(d,i) { return i*barWidth; })
        .attr("y", maxDataHeight + 10 )
        .attr("width", barWidth-2 )
        .attr("height", 0 )
        .style("fill", function(d) {
            if(d.color == 1)        
                return "green";
            else if(d.color == 2)    
                return "red"; 
            else if (d.color == 3)   
                return "yellow";
            else    
                return "black";
        })
        .style("stroke", "#000")
        .style("stroke-width", "0px")
        .attr("id", function(d, i) { return "rect_"+i;});
                        
    rect.transition()
        .delay(function(d, i) { return i * 20; })
        .attr("y", function(d) { return maxDataHeight - d.y * barHeight + 10; })
        .attr("height", function(d) { return d.y * barHeight ; });
    
   /*rect.append("text")
            .text( function(d, i){ return d.date;});*/
    
    texts = layer.append("g")
            .selectAll("text")
            .data(function(d) { return d;})
        .enter()
            .append("text")
            .style("display", "none")
            .style("font-weight","bold")
            .attr("id", function(d, i) { return "text_"+i;});
            
    texts.transition()
            .delay(function(d, i) { return i * 25; })
            .attr("x", function(d,i) { return i*barWidth+4; })
            .attr("y", function(d) { return maxDataHeight - d.y * barHeight - 50; })
            .text( function(d, i){ return d.date;})
            .style("writing-mode", "tb");
    
    texts2 = layer.append("g")
            .selectAll("text")
            .data(function(d) { return d;})
        .enter()
            .append("text")
            .style("display", "none")
            .style("font-size","8px")
            .style("font-weight","bold")
            .attr("id", function(d, i) { return "text2_"+i;});
            
    texts2.transition()
            .delay(function(d, i) { return i * 25; })
            .attr("x", function(d,i) { return i*barWidth+textAdjustmentOnBar; })
            .attr("y", function(d) { return maxDataHeight - (d.y * barHeight)/2 + textAdjustmentOnBar; })
            .text( function(d, i){ return d.y;})
            .style("writing-mode", "tb");
    
    rect.on("mouseover", function(d,i) {
            d3.select(this).style("fill", "#FFF")
                            .style("stroke-width", "1px");
            d3.select("#text_"+i).style("display", "block");
            d3.select("#text2_"+i).style("display", "block");
        })
        .on("mouseout",  function(d,i) {
            d3.select(this).style("fill", function(d) {
                if(d.color == 1)        
                    return "green";
                else if(d.color == 2)    
                    return "red"; 
                else if (d.color == 3)   
                    return "yellow";
                else    
                    return "black";
            }).style("stroke-width", "0px");
            d3.select("#text_"+i).style("display", "none");
            d3.select("#text2_"+i).style("display", "none");
        });
}
function sortByActualOccurrence() {
        
	rect.sort(function(a,b){
	   return b.x-a.x;
	});
    sort_XAdjustment();
}  
function sortByNoOfParticipants() {
        
	rect.sort(function(a,b){
	   if(a.y==b.y)
            return a.color - b.color;
	   return b.y-a.y;
	});
    sort_XAdjustment();
} 
function sortByColour(){
    rect.sort(function(a,b){
       if(a.color==b.color)
            return b.y-a.y;
	   return a.color - b.color;
	});
    sort_XAdjustment();
}
function sort_XAdjustment()
{
    rect.transition()
    .delay(function (d, i) {
        return i * 50;
    })
    .duration(500)
    .attr("x", function(d,i) {
        var initailID = d3.select(this).attr("id").substring(5);
        d3.select("#text_"+initailID).attr("x", function(d) { return i*barWidth+textAdjustmentOnBar; });
        d3.select("#text2_"+initailID).attr("x", function(d) { return i*barWidth+textAdjustmentOnBar; });
        return i*barWidth;
    });
}