var data1 = [];
var data1Size;
var loadingMessage = document.getElementById( 'loadingMessage' );
var startDate = 0;
var endDate = 0;
var yArray;
var svg;
var layer;
var rect;
var texts;
var texts2;
var days;
//var days_test;
var svg;
var heatMap;
var displaySize = 0;
var orderOption = 0;
var maxTotal ;
var sizeScale;
var dayLabels;
var margin = { top: 20, right: 0, bottom: 10, left: 40 };
var width = 1020 - margin.left - margin.right;
var height = 2430 - margin.top - margin.bottom;
var gridSize = Math.floor(width / 34);
var legendElementWidth = gridSize*2;
var colors = ["#138808", "#7CFC00", "#FFFF00", "#FFBF00", "#FF0000"]; 

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
d3.select("#displaySize")
    .on("change", function() {
        if(eval(d3.select(this).property('value'))==0)
            displaySize = false;
        else
            displaySize = true;
        displayTotal_Size();
    });
d3.select("#orderOption")
    .on("change", function() {
        if(eval(d3.select(this).property('value'))==0)
            orderOption = 0;
        else if(eval(d3.select(this).property('value'))==1)
            orderOption = 1;
        else if(eval(d3.select(this).property('value'))==2)
            orderOption = 2;
        else
            orderOption = 3;
        order_Size();
    });


//http://synthesis.sbecker.net/articles/2012/07/16/learning-d3-part-6-scales-colors
// make snap of 3
// add size to data
// add sort to data
// try to use d3js instead of php for sort and the rest
// add more date sumerization such as 2 days, 4 days
function getData()
{
    startDate = eval(d3.select("#selectedDate_Start").property('value'));
    endDate = eval(d3.select("#selectedDate_End").property('value'));
    if(startDate <= endDate)
    {
        document.getElementById( 'loadingMessage' ).innerHTML = "Please wait, it is loading....";
        data1 = [];
        
        d3.json("data3.php?startID="+startDate+"&endID="+endDate, function(error, data) {
            //get the data from Database View PHP format
            data.forEach(function(d){
                data1.push({"index":+d.id, "day":d.date, "day_int":+d.date, "hour":+d.counter, "total":+d.total, "value": +d.average_color, "sort":d.date});
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
}
function isInArray(array, search)
{
    return array.indexOf(search) >= 0;
}

function remove_duplicates(arr) {
    var obj = {};
    for (var i = 0; i < arr.length; i++) {
        obj[arr[i]] = true;
    }
    arr = [];
    for (var key in obj) {
        arr.push(key);
    }
    return arr;
}


  
function updateData()
{
    var times = new Array();
    times.push("0");
    for(var i=1; i<40; i++)
        times.push(i.toString());
    
    days = new Array();
    //days_test = data1.map(function(d) { return d.day.toString(); });
  
    data1.forEach(function(d){
        if(!isInArray(days, d.day.toString()))
            days.push(d.day.toString());
    });
    
    data1.forEach(function(d){
        d.day_int  = +days.indexOf(d.day);
        d.sort  = +days.indexOf(d.day);
    });
    
    
    //overwrite to height
    height = days.length * ( gridSize ) + 60;
    /*data1.forEach(function(d){
        index++;
        if((index%4)==0)
            data2.push(d);
    });
    */
      var colorScale = d3.scale.quantile()
          .domain([1.00, 1.50, 2.00, 2.5, 3.00])
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
                .attr("id", function (d){ return "rect_"+d.index})
                .style("fill", colors[0])
                .attr("rx", function (d){
                    if(displaySize)
                        return sizeScale(d.total)/8;
                    else    
                        return 4;
                })
                .attr("ry",  function (d){
                    if(displaySize)
                        return sizeScale(d.total)/8;
                    else    
                        return 4;})
                .attr("x", function(d) { 
                    if(displaySize)
                        return (d.hour) * gridSize + ((gridSize- sizeScale(d.total))/2) ; 
                    else
                        return (d.hour) * gridSize  ; 
                })
                .attr("y", function(d) { 
                    if(displaySize)
                        return +d.sort * gridSize + ((gridSize - sizeScale(d.total))/2) ;
                    else
                        return +d.sort * gridSize ;
                })
                .attr("height", function (d){
                    if(displaySize)
                        return sizeScale(d.total);
                    else    
                        return gridSize;
                })
                .attr("width", function (d){
                    if(displaySize)
                        return sizeScale(d.total);
                    else    
                        return gridSize;
                });
    
      heatMap.transition().duration(1000)
          .style("fill", function(d) {
            return colorScale(d.value); 
          });
    
      heatMap.append("title").text(function(d) { return d.total; });
      
      
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

    //end of new section
    //yArray = data1[0].map(function(d) { return +d.y; });
}
function displayTotal_Size()
{
    heatMap.transition().duration(2000)
        .attr("rx", function (d){
            if(displaySize)
                return sizeScale(d.total)/8;
            else    
                return 4;
        })
        .attr("ry",  function (d){
            if(displaySize)
                return sizeScale(d.total)/8;
            else    
                return 4;})
        .attr("x", function(d) { 
            if(displaySize)
                return (d.hour) * gridSize + ((gridSize- sizeScale(d.total))/2) ; 
            else
                return (d.hour) * gridSize  ; 
        })
        .attr("y", function(d) { 
            if(displaySize)
                return +d.sort * gridSize + ((gridSize - sizeScale(d.total))/2) ;
            else
                return +d.sort * gridSize ;
        })
        .attr("height", function (d){
            if(displaySize)
                return sizeScale(d.total);
            else    
                return gridSize;
        })
        .attr("width", function (d){
            if(displaySize)
                return sizeScale(d.total);
            else    
                return gridSize;
        })
}

function order_Size()
{
    
    if( orderOption == 0) 
    {
       dayLabels.attr("display","block");
       data1.forEach(function(d){
            d.sort = +d.day_int;
        });
    }
    else if(orderOption==1)
    {
        dayLabels.attr("display","none");
       
        data1Size = new Array(0, d3.max(data1.map(function(d) { return +d.hour; })));
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            data1Size[index] = new Array();
            
        data1.forEach(function(d){
            data1Size[+d.hour].push(d);
        });
        
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            for(var i=0; i < data1Size[index].length; i++)
                for(var j=i+1; j < data1Size[index].length; j++)
                {
                    if( data1Size[index][i].total < data1Size[index][j].total )
                    {
                        var temp = data1Size[index][i];
                        data1Size[index][i] = data1Size[index][j];
                        data1Size[index][j] = temp;
                    }
                    else if ( data1Size[index][i].total == data1Size[index][j].total )
                    {
                        if( data1Size[index][i].value < data1Size[index][j].value)
                        {
                            var temp = data1Size[index][i];
                            data1Size[index][i] = data1Size[index][j];
                            data1Size[index][j] = temp;
                        }
                    }
                }
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            for(var i=0; i < data1Size[index].length; i++)
            {
                data1Size[index][i].sort = i;
            }
        
        
    }
    
    else if(orderOption==2)
    {
        dayLabels.attr("display","none");
        data1Size = new Array(0, d3.max(data1.map(function(d) { return +d.hour; })));
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            data1Size[index] = new Array();
            
        data1.forEach(function(d){
            data1Size[+d.hour].push(d);
        });
        
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            for(var i=0; i < data1Size[index].length; i++)
                for(var j=i+1; j < data1Size[index].length; j++)
                {
                    if( data1Size[index][i].value < data1Size[index][j].value )
                    {
                        var temp = data1Size[index][i];
                        data1Size[index][i] = data1Size[index][j];
                        data1Size[index][j] = temp;
                    }
                    else if ( data1Size[index][i].value == data1Size[index][j].value )
                    {
                        if( data1Size[index][i].total < data1Size[index][j].total)
                        {
                            var temp = data1Size[index][i];
                            data1Size[index][i] = data1Size[index][j];
                            data1Size[index][j] = temp;
                        }
                    }
                }
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            for(var i=0; i < data1Size[index].length; i++)
            {
                data1Size[index][i].sort = i;
            }
    }
    
    else if(orderOption==3)
    {
        dayLabels.attr("display","none");
        data1Size = new Array(0, d3.max(data1.map(function(d) { return +d.hour; })));
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            data1Size[index] = new Array();
            
        data1.forEach(function(d){
            data1Size[+d.hour].push(d);
        });
        
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            for(var i=0; i < data1Size[index].length; i++)
                for(var j=i+1; j < data1Size[index].length; j++)
                {
                    if( data1Size[index][i].value > data1Size[index][j].value )
                    {
                        var temp = data1Size[index][i];
                        data1Size[index][i] = data1Size[index][j];
                        data1Size[index][j] = temp;
                    }
                    else if ( data1Size[index][i].value == data1Size[index][j].value )
                    {
                        if( data1Size[index][i].total < data1Size[index][j].total)
                        {
                            var temp = data1Size[index][i];
                            data1Size[index][i] = data1Size[index][j];
                            data1Size[index][j] = temp;
                        }
                    }
                }
        for(var index=0; index <= d3.max(data1.map(function(d) { return +d.hour; })); index++ )
            for(var i=0; i < data1Size[index].length; i++)
            {
                data1Size[index][i].sort = i;
            }
    }
    
    for(var i=0; i < data1Size.length; i++)
    {
        var accumulated_y = 0;
        data1Size[i].forEach(function(d){
            d3.select("#rect_"+ d.index).transition()
            //.delay( (+d.index) * 10)
            //.duration(100)
            .duration(2000)
            .attr("y", function(d) {
                if(displaySize)
                {
                    /*if(orderOption!=0)
                    {
                        accumulated_y +=  sizeScale(d.total) + 2 ;
                        return accumulated_y - (sizeScale(d.total) + 2 );
                    }
                    else*/
                        return (+d.sort) * gridSize + ((gridSize - sizeScale(d.total))/2) ;
                }
                else
                    return (+d.sort) * gridSize ;
            }); 
        });
    }
}