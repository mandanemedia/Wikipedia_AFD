// Dimensions of sunburst.
var width = 970;
var height = 620;
var radius = 690;

// Breadcrumb dimensions: width, height, spacing, width of tip/tail.
var b = {
  w: 175, h: 24, s: 3, t: 10
};
var circleCenter = {x:300, y:275};
  
// Mapping of step names to colors.
var colors = {
  "Non_Notability": "#5687d1",
  "Notability": "#7b615c",
  "Other": "#de783b",
  "Wikipedia:N": "#6ab975",
  "Wikipedia:BIO": "#a173d1",
  "Wikipedia:ATH": "#6a3975",
  "Wikipedia:NOT": "#b17301",
  "Wikipedia:RS": "#c1a3d1"
};

var color_random = d3.scale.category20c();
var colorScaleDelete = d3.scale.linear()
                .domain([0.1, 1.4])
                .range(["#FFF", "Red"]);
var colorScaleOther = d3.scale.linear()
                .domain([0.1, 1])
                .range(["#FFF", "Yellow"]);
var colorScaleKeep = d3.scale.linear()
                .domain([0.1, .9])
                .range(["#FFF", "Green"]);
        
//http://bl.ocks.org/aaizemberg/78bd3dade9593896a59d
// Total size of all segments; we set this later, after loading the data.
var totalSize = 0; 

var vis = d3.select("#chart").append("svg:svg")
    .attr("width", width)
    .attr("height", height)
    .append("svg:g")
    .attr("id", "container")
    .attr("transform", "translate(" + circleCenter.x + "," + (circleCenter.y+60) + ")");


var partition = d3.layout.partition()
    .size([2 * Math.PI, radius * radius])
    .value(function(d) { return d.size; });

var arc = d3.svg.arc()
    .startAngle(function(d) { return d.x; })
    .endAngle(function(d) { return d.x + d.dx; })
    .innerRadius(function(d) {  if(d.depth == 1)
                                    return Math.sqrt(d.y ) - 285 ;  //0
                                else if(d.depth == 2) 
                                    return Math.sqrt(d.y ) - 310  ; //1
                                else if(d.depth == 3)
                                    return Math.sqrt(d.y ) - 360 ; //2
                                else
                                    return Math.sqrt(d.y ) - 365  ; //3
                                        })
    .outerRadius(function(d) { 
                                if(d.depth == 1)
                                    return Math.sqrt(d.y + d.dy ) - 310 ; //1
                                else if(d.depth == 2)
                                    return Math.sqrt(d.y + d.dy ) - 360  ; //2
                                else if(d.depth == 3)
                                    return Math.sqrt(d.y + d.dy ) - 365 ; //3
                                else
                                    return Math.sqrt(d.y + d.dy  ) -360  ; //4
        
        //return Math.sqrt(d.y + d.dy   ) -360 ; 
        });

var json, csv;
var root;
var path;

var percentageData = [];
d3.json("data4_percentage.php", function(error, data) {
        //get the data from Database - PHP 
        data.forEach(function(d){
            percentageData.push(d);
        });
});

var givenData = []; 
d3.json("data4.php", function(error, data) {
        //get the data from Database - PHP 
        data.forEach(function(d){
            givenData.push(d);
        });
        json = buildHierarchy(givenData);
        createVisualization(json);
});
 

// Main function to draw and set up the visualization, once we have the data.
function createVisualization(json) {

  // Basic setup of page elements.
  initializeBreadcrumbTrail();

  // Bounding circle underneath the sunburst, to make it easier to detect
  // when the mouse leaves the parent g.
  vis.append("svg:circle")
      .attr("r", radius )
      .style("opacity", 0);

  // For efficiency, filter nodes to keep only those large enough to see.
  var nodes = partition.nodes(json)
      .filter(function(d) {
      return (d.dx > 0.005); // 0.005 radians = 0.29 degrees
      });

   path = vis.data([json]).selectAll("path")
      .data(nodes)
      .enter()/*
      .append("a")
      .attr("xlink:href", function(d){ return "https://en.wikipedia.org#";} )
      .attr("target","_blank")*/
      .append("svg:path")
      .attr("display", function(d) { return d.depth ? null : "none"; })
      .attr("d", arc)
      .attr("fill-rule", "evenodd")
      .style("fill", "white" )
      .style("stroke", "#ddd" )
      .style("stroke-width", "1" ) 
      .style("fill", function(d,i) {
            return color_random(d.name.replace("Wikipedia:","")); //colors[d.name];
      })
      .style("opacity", 1)
      .style("cursor", "pointer")
      .attr("id",function(d){
            return d.depth+"_"+d.name.replace(':','') ;
        })
      //.on("click", enableCenter)
      .on("mouseenter", enableCenter);
      
  var limitTextDisplay = 500;
  var radiusD = radius -400;
  var adjustR = { r1:radiusD-185 , r2:radiusD-75  , r3:radiusD-94 , r4:radiusD-85  };
  var text = vis.selectAll("text")
                        .data(nodes)
                        .enter()
                        .append("text")
                        .attr("text-anchor", "left")
                        .attr("alignment-baseline", "middle")
                        .attr("class", "pathlabel" )
                        .attr("x", function (d) { 
                            if(d.depth == 1)
                                return adjustR.r1 / Math.PI * d.depth;
                            else if(d.depth == 2)
                                return adjustR.r2 / Math.PI * d.depth;
                            else if(d.depth == 3)
                                return adjustR.r3 / Math.PI * d.depth;
                            else 
                                return adjustR.r4 / Math.PI * d.depth;
                        })
                        .attr("y", function(d) { return d.cy; })
                        .text(function (d) {
                                return d.name;
                         })
                        .style("opacity",function(d){
                            if(+d.depth==1 && +d.value > 1800) 
                                return 1; 
                            else if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1000))
                                return 1;
                            else if((+d.depth== 4 && +d.value > 342))
                                return 1;
                            else
                                return 0.01;
                        })
                        .attr("transform", function (d) {
                            var r = 180 * ((d.x + d.dx / 2 - Math.PI / 2) / Math.PI);
                            return "rotate(" + r + ")"
                        })
                        .on("mouseenter", enableCenter);
      
  // Add the mouseleave handler to the bounding circle.
  //d3.select("#chart").on("mouseleave", disableCenter);
  //d3.select("#explanation").on("click", disableCenter);
   
  // Get total size of the tree = value of root node from partition.
  totalSize = path.node().__data__.value;
  
  //set all extra attributes to the arces     
  setTimeout(setExtraAttributes, 500)
 
 }

// Fade all but the current sequence, and show it in the breadcrumb trail.
function enableCenter(d) {
  //in case of delay from database, this is to double check
  setExtraAttributes();  
  if(isSet && d.name != "root")
  {
      var percentage = (100 * d.value / totalSize).toPrecision(2);
      var label = d.name ;
      var percentageString = percentage + "%";
      if (percentage < 0.1) {
        percentageString = "< 0.1%";
      }
    
      /*d3.select("#title")
          .text(label);*/
      
      d3.select("#percentage")
          .text( percentageString);
    
      d3.select("#explanation")
          .style("visibility", "")
    
      var sequenceArray = getAncestors(d);
                        
      //updateBreadcrumbs(sequenceArray, percentageString);
    
      // Fade all the segments.
      d3.selectAll("path")
          .style("opacity", 0.4);
      d3.selectAll(".pathlabel")
                        .style("opacity",function(d){
                            if(+d.depth==1 && +d.value > 1800) 
                                return 0.3 ; 
                            else if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1000))
                                return 0.3 ;
                            else if((+d.depth== 4 && +d.value > 342))
                                return 0.3;
                            else
                                return 0.01;
                        })
    
      // Then highlight only those that are an ancestor of the current segment.
      vis.selectAll("path")
          .filter(function(node) {
                    return (sequenceArray.indexOf(node) >= 0);
                  })
          .style("opacity", 1);
      vis.selectAll(".pathlabel")
          .filter(function(node) {
                    return (sequenceArray.indexOf(node) >= 0);
                  })
          .style("opacity", 1)
          .style("fill", "#111");
    
      displayBarChar(d);
    }      
}

function disableCenter(d){
  // Hide the breadcrumb trail
  //d3.select("#trail")
  //    .style("visibility", "hidden");

  // Deactivate all segments during transition.
  d3.selectAll("path").on("mouseover", null);

  // Transition each segment to full opacity and then reactivate it.
  d3.selectAll("path")
      .transition()
      //.duration(1000)
      .style("opacity", 1);
      
  d3.selectAll(".pathlabel")
      .transition()
      //.duration(1000)
                        .style("opacity",function(d){
                            if(+d.depth==1 && +d.value > 1800) 
                                return 1; 
                            else if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1000))
                                return 1;
                            else if((+d.depth== 4 && +d.value > 342))
                                return 1;
                            else
                                return 0.01;
                        })
      .style("fill", "#777");

  d3.select("#explanation")
      .style("visibility", "hidden");
  
  displayBarChar("");
  //updateBreadcrumbs([],"");
}

// Given a node in a partition layout, return an array of all of its ancestor
// nodes, highest first, but excluding the root.
function getAncestors(node) {
  var path = [];
  var current = node;
  while (current.parent) {
    path.unshift(current);
    current = current.parent;
  }
  return path;
}

function initializeBreadcrumbTrail() {
    
  // Add the svg area.
  var trail = d3.select("#sequence")
      .append("svg:svg")
      .attr("width", width)
      .attr("height", b.h)
      .attr("id", "trail");
  // Add the label at the end, for the percentage.
  trail.append("svg:text")
    .attr("id", "endlabel")
    .style("fill", "#000");
}
var charToPix = 6.2;
var charPadding = 20;

// Generate a string that describes the points of a breadcrumb polygon.
function breadcrumbPoints(d, i) {
  var points = [];
  points.push("0,0");
  points.push(d.policyTitle.length*charToPix+charPadding + ",0");
  points.push(d.policyTitle.length*charToPix+charPadding + b.t + "," + (b.h / 2));
  points.push(d.policyTitle.length*charToPix+charPadding + "," + b.h);
  points.push("0," + b.h);
  if (i > 0) { // Leftmost breadcrumb; don't include 6th vertex.
    points.push(b.t + "," + (b.h / 2));
  }
  return points.join(" ");
}
// Update the breadcrumb trail to show the current sequence and percentage.
function updateBreadcrumbs(nodeArray, percentageString) {

  // Data join; key function combines name and depth (= position in sequence).
  var g = d3.select("#trail")
      .selectAll("g")
      .data(nodeArray, function(d) { return d.name + d.depth; });

  // Add breadcrumb and label for entering nodes.
  var entering = g.enter()
                  .append("svg:g");

  entering/*.append("a")
            .attr("xlink:href", function(d){ return "https://en.wikipedia.org"+d.policyURL;} )
            .attr("target","_blank")*/
              .style("cursor", function(d){
                //if(+d.depth >1)
                //    return "zoom-in";
                //else
                    return "default";
              })
              .append("svg:polygon")
              .attr("points", breadcrumbPoints)
              .style("fill", function(d,i) { 
                            if(+d.depth>0)
                                return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                            else
                                return "#ddd";
              });
          entering.append("svg:text")
              .attr("x",function(d){ return (d.policyTitle.length*charToPix+charPadding + b.t) / 2;  })
              .attr("y", b.h / 2)
              .attr("dy", "0.40em")
              .attr("text-anchor", "middle")
              .attr("font-size", "12")
              .style("fill", "#333")
              .style("font-weight", "500")
              .text(function(d) { return d.policyTitle ;  });

  // Set position for entering and updating nodes.
  g.attr("transform", function(d, i) {
    var xShift = 0;
    for (var j = 0; j < i; j++) {
        xShift += nodeArray[j].policyTitle.length*charToPix+charPadding + b.s;
    }
    return "translate(" + xShift + ", 0)";
  });
  // Remove exiting nodes.
  g.exit().remove();
  
  // Make the breadcrumb trail visible, if it's hidden.
  d3.select("#trail")
      .style("visibility", "");
}

// Take a 2-column CSV and transform it into a hierarchical structure suitable
// for a partition layout. The first column is a sequence of step names, from
// root to leaf, separated by hyphens. The second column is a count of how 
// often that sequence occurred.
function buildHierarchy(csv) {
  root = {"name": "root", "children": []};
  for (var i = 0; i < csv.length; i++) {
    var sequence = csv[i]['x0'];
    var size = +csv[i]['x1'];
    var delete_ = csv[i]['delete'];
    var other_ = csv[i]['other'];
    var keep_ = csv[i]['keep'];
    if (isNaN(size)) { // e.g. if this is a header row
      continue;
    }
    var parts = sequence.split("@");
    var currentNode = root;
    for (var j = 0; j < parts.length; j++) {
          var children = currentNode["children"];
          var nodeName = parts[j];
          var childNode;
          if (j + 1 < parts.length) {
               // Not yet at the end of the sequence; move down the tree.
             	var foundChild = false;
             	for (var k = 0; k < children.length; k++) {
                 	  if (children[k]["name"] == nodeName) {
                     	    childNode = children[k];
                     	    foundChild = true;
                     	    break;
                 	  }
             	}
              // If we don't already have a child node for this branch, create it.
             	if (!foundChild) {
             	      childNode = {"name": nodeName, "children": []};
             	      children.push(childNode);
             	}
             	currentNode = childNode;
          } else {
             	// Reached the end of the sequence; create a leaf node.
             	      childNode = {"name": nodeName, "size": size, "title": title, "delete_": delete_, "other_": other_, "keep_": keep_ };
             	      children.push(childNode);
          }
    }
  }
  return root;
};


// in the case percentageData has been not loaded yet
var isSet = false;
function setExtraAttributes()
{
    if(!isSet)
    {
        
        vis.append("text")                
            .attr("class", "legendText2")   
            .text("Percentage of mentioned Categories in AfDs:")          
            .attr("x", -105 )
            .attr("y", circleCenter.y - 5 );
            
        path.each(function(d,i){
            //console.log(d.name+"@"+d.depth);
            for (var i = 0; i < percentageData.length; i++) {
                isSet = true;
                if( percentageData[i]["tier"] == +d.depth)
                     if( percentageData[i]["policyID"] == d.name)
                     {
                        d["policyTitle"] = percentageData[i]["policyTitle"];
                        d["policyURL"] = percentageData[i]["policyURL"];
                        d["total"] = percentageData[i]["total"];
                        d["percentage_otherComment"] = percentageData[i]["percentage_otherComment"];
                        d["percentage_delete"] = percentageData[i]["percentage_delete"];
                        d["percentage_other"] = percentageData[i]["percentage_other"];
                        d["percentage_keep"] = percentageData[i]["percentage_keep"];
                        d["percentage_total"] = (d.value / totalSize).toPrecision(2) ;
                     }
            }
            /*var url = d.policyURL;
            d3.select(this.parentNode)
              .attr("xlink:href", function(d){ return "https://en.wikipedia.org"+url;} );*/
        });
            
            
        path.append("title")
            .text(function(d) { return  d.policyTitle; });
        
        
        
        colorCodeOfPath();
    }
}


var distance = 110;
var heightRec = 16;
var baseX = 399 ;
var spanSpace = 20;
var baseYText = -310;
var baseYLabel = baseYText ;
var baseYShortcut = baseYLabel + spanSpace ;
var baseYDelete = baseYShortcut + spanSpace ;
var baseYOther = baseYDelete + spanSpace ;
var baseYKeep = baseYOther + spanSpace ;
var opacityTextBackground = 1;
var trafficLightOpacity = 0.75;
var percentageScale = d3.scale.linear()
    .domain([0.0,0.99])
    .range([0.5,250]);

function colorCodeOfPath()
{
    var startX = baseX - 52 ;
    var startY = circleCenter.y  - 100;
    var strokeColor = "rgb(136, 136, 136)";
    var distance2 = 20;
    
    vis.append("text")                
        .attr("class", "legendTextTitle")    
        .text("Mouse Over each of the below rectanges for different color coding:")          
        .attr("x", startX    )
        .attr("y", startY - 6 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Delete votes")          
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*0 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Other votes")          
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*1 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Keep votes")          
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*2 );
    
    //Delete 
    vis.append("rect")
        .attr("class", "colorCodeOfPath")
        .style("fill", "red")
        .style("cursor", "pointer")
        .style("stroke-width", "1")
        .style("opacity", trafficLightOpacity)
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
            disableCenter(d);
            path.style("fill", function(d,i) {
                return colorScaleDelete(+d.percentage_delete);
            });
        });
        
    //other 
    vis.append("rect")
        .attr("class", "colorCodeOfPath")
        .style("fill", "yellow")
        .style("cursor", "pointer")
        .style("stroke-width", "1")
        .style("opacity", trafficLightOpacity)
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY+distance2;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
            disableCenter(d);
            path.style("fill", function(d,i) {
                return colorScaleOther(+d.percentage_other);
            });
        });  
          
    //keep 
    vis.append("rect")
        .attr("class", "colorCodeOfPath")
        .style("fill", "green")
        .style("cursor", "pointer")
        .style("stroke-width", "1")
        .style("opacity", trafficLightOpacity)
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY+distance2+distance2;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
            disableCenter(d);
            path.style("fill", function(d,i) {
                return colorScaleKeep(+d.percentage_keep);
            });
        });
        
    //Reset 
    vis.append("rect")
        .attr("class", "colorCodeOfPath")
        .style("fill", "#555")
        .style("cursor", "pointer")
        .style("stroke-width", "1")
        .style("stroke", "#ddd")
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY+distance2+distance2+distance2;} )
        .attr("height",function (d){return  heightRec + 5 ;} )
        .attr("width", function (d){return  (7*heightRec) + 2;})
        .on("mouseenter", function (d){
            disableCenter(d);
            path.style("fill", function(d,i) {
                return color_random(d.name.replace("Wikipedia:",""));
            });
        });
    vis.append("text")                
        .attr("class", "legendTextReset")    
        .text("Reset to default")            
        .attr("x", startX + heightRec - 1  )
        .attr("y", startY + 12 + distance2*3 + 3)
        .on("mouseenter", function (d){
            disableCenter(d);
            path.style("fill", function(d,i) {
                return color_random(d.name.replace("Wikipedia:",""));
            });
        });
}

var inistialText =  false;
var rectData; 
function displayBarChar(d)
{
    if(!inistialText)
    {
        inistialText = true;
        /*vis.append("text")                
            .attr("class", "legendText")    
            .text("Details percentage of each Policy:")          
            .attr("x", baseX - (heightRec+heightRec+ heightRec) - 4 )
            .attr("y", baseYText );*/
    }
    rectData = getAncestors(d);
    
    
    //Title Section
    /*vis.selectAll(".barChartShortCut").remove();
    vis.selectAll(".barChartShortCut")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartShortCut")
                .style("fill", function(d,i) { 
                            if(+d.depth>0)
                                    return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                                else
                                    return "#ddd";
                })
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) - 3 ;})
                .attr("y", function(d, i ) {return baseYLabel + distance*i + 20; })
                .attr("height", function (d){ return 1; })
                .attr("width", function (d){ return heightRec * 16; })
                .style("opacity", opacityTextBackground); 
    vis.selectAll(".barChartTextLabel").remove();
    vis.selectAll(".barChartTextLabel")
                  .data(rectData)
                  .enter()
                  .append("a")
                  .attr("xlink:href", function(d){ return "https://en.wikipedia.org"+d.policyURL;} )
                  .attr("target","_blank")
                  .append("svg:text")
                  .attr("class", "barChartTextLabel")
                  .style("cursor", "pointer")
                  .attr("dy", "0.45em")
                  .attr("text-anchor", "left")
                  .attr("x", function(d, i ) {return baseX - 52 ;})
                  .attr("y", function(d, i ) {return baseYLabel + 10 + distance*i; })
                  .text(function(d) { return "Policy Title: "+ d.policyTitle ;  });*/  
                  
    //Shortcut Section              
    vis.selectAll(".barChartTitle").remove();
    vis.selectAll(".barChartTitle")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartTitle")
                .style("fill", function(d,i) { 
                            if(+d.depth>0)
                                    return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                                else
                                    return "#ddd";
                })
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec)-3 ;})
                .attr("y", function(d, i ) {return baseYShortcut + 5 + distance*i ; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){ return heightRec + heightRec+ heightRec; })
                .style("opacity", opacityTextBackground);
    vis.selectAll(".barChartText").remove();
    vis.selectAll(".barChartText")
                  .data(rectData)
                  .enter()
                  .append("a")
                  .attr("xlink:href", function(d){ return "https://en.wikipedia.org"+d.policyURL;} )
                  .attr("target","_blank")
                  .append("svg:text")
                  .attr("class", "barChartText")
                  .style("cursor", function(d){
                    if(+d.depth >1)
                        return "zoom-in";
                    else
                        return "default";
                  })
                  .attr("dy", "0.45em")
                  .attr("text-anchor", "left")
                  .attr("x", function(d, i ) {return baseX  ;})
                  .attr("y", function(d, i ) {return baseYShortcut + 12 + distance*i; })
                  .text(function(d) { 
                        return d.name + " ( "+ (+(d.percentage_total*100)).toFixed(2)+"% )";
                  });
    
    
    //Delete Section                        
    vis.selectAll(".barChartDelete").remove();
    vis.selectAll(".barChartDelete")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartDelete")
                .style("fill", "red")
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .style("opacity", trafficLightOpacity)
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYDelete + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_delete));})
                .append("title").text(function(d) { return "Delete Votes containing "+d.name + " category : " +((d.percentage_delete)*100).toFixed(2)+"%"; });
    vis.selectAll(".barChartLegend3").remove();
    vis.selectAll(".barChartLegend3")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend3")
                .style("fill", function(d,i) { 
                            /*if(+d.depth>0)
                                    return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                                else*/
                                    return "#eee";
                })
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) - 3 ;})
                .attr("y", function(d, i ) {return baseYDelete + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){ return heightRec + heightRec+ heightRec; })
                .style("opacity", opacityTextBackground);
    vis.selectAll(".barChartLegendText3").remove();
    vis.selectAll(".barChartLegendText3")
                  .data(rectData)
                  .enter()
                  .append("svg:text")
                  .attr("class", "barChartLegendText3")
                  .style("fill", "#222")
                  .attr("dy", "0.38em")
                  .attr("text-anchor", "right")
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec - 3) ;})
                  .attr("y", function(d, i )  {return baseYDelete + 12 + distance*i; })
                  .text(function(d) { return (+(d.percentage_delete*100)).toFixed(2)+"%";  });
     
    
    //Other Section             
    vis.selectAll(".barChartOther").remove();
    vis.selectAll(".barChartOther")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartOther")
                .style("fill", "yellow")
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .style("opacity", trafficLightOpacity)
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYOther + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_other));})
                .append("title").text(function(d) { return "Other Votes containing "+d.name + " category : " +((d.percentage_other)*100).toFixed(2)+"%"; });
    vis.selectAll(".barChartLegend4").remove();
    vis.selectAll(".barChartLegend4")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend4")
                .style("fill", function(d,i) { 
                            /*if(+d.depth>0)
                                    return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                                else*/
                                    return "#eee";
                })
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) - 3 ;})
                .attr("y", function(d, i ) {return baseYOther + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){ return heightRec + heightRec+ heightRec; })
                .style("opacity", opacityTextBackground);
    vis.selectAll(".barChartLegendText4").remove();
    vis.selectAll(".barChartLegendText4")
                  .data(rectData)
                  .enter()
                  .append("svg:text")
                  .attr("class", "barChartLegendText4")
                  .style("fill", "#222")
                  .attr("dy", "0.38em")
                  .attr("text-anchor", "right")
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec - 3) ;})
                  .attr("y", function(d, i )  {return baseYOther + 12 + distance*i; })
                  .text(function(d) { return (+(d.percentage_other*100)).toFixed(2)+"%";  });
    
    
    //Keep Section                                  
    vis.selectAll(".barChartKeep").remove();
    vis.selectAll(".barChartKeep")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartKeep")
                .style("fill", "green")
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .style("opacity", trafficLightOpacity)
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYKeep + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_keep));})
                .append("title").text(function(d) { return "Keep Votes containing "+d.name + " category : " +((d.percentage_keep)*100).toFixed(2)+"%"; });
    vis.selectAll(".barChartLegend5").remove();
    vis.selectAll(".barChartLegend5")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend5")
                .style("fill", function(d,i) { 
                            /*if(+d.depth>0)
                                    return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                                else*/
                                    return "#eee";
                })
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) - 3 ;})
                .attr("y", function(d, i ) {return baseYKeep + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){ return heightRec + heightRec+ heightRec; })
                .style("opacity", opacityTextBackground);
    vis.selectAll(".barChartLegendText5").remove();
    vis.selectAll(".barChartLegendText5")
                  .data(rectData)
                  .enter()
                  .append("svg:text")
                  .attr("class", "barChartLegendText5")
                  .style("fill", "#222")
                  .attr("dy", "0.38em")
                  .attr("text-anchor", "right")
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec - 3) ;})
                  .attr("y", function(d, i )  {return baseYKeep + 12 + distance*i; })
                  .text(function(d) { return (+(d.percentage_keep*100)).toFixed(2)+"%";  });
     
     
    
    
     /*console.log(rectData[rectData.length-1]);
     vis.selectAll(".guideLine").remove();
     vis.append("line")
        .attr("x1", 20)
        .attr("y1", 0)
        .attr("class", "guideLine")
        .attr("x2", baseX - (heightRec+heightRec+ heightRec) - 3)
        .attr("y2", baseYLabel + distance*(rectData.length-1) + 20)
        .attr("stroke-width", 1)
        .style("stroke-dasharray", ("3, 3"))
        .attr("stroke", "#bbb"); */           
        
}

