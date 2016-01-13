// Dimensions of sunburst.
var width = 960;
var height = 610;
var radius = 645;

// Breadcrumb dimensions: width, height, spacing, width of tip/tail.
var b = {
  w: 175, h: 24, s: 3, t: 10
};
var circleCenter = {x:300, y:300};
  
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
                .domain([0.1, 1.3])
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
    .attr("transform", "translate(" + circleCenter.x + "," + circleCenter.y + ")");


var partition = d3.layout.partition()
    .size([2 * Math.PI, radius * radius])
    .value(function(d) { return d.size; });

var arc = d3.svg.arc()
    .startAngle(function(d) { return d.x; })
    .endAngle(function(d) { return d.x + d.dx; })
    .innerRadius(function(d) {  if(d.depth == 1)
                                    return Math.sqrt(d.y ) - 292 ; 
                                else
                                    return Math.sqrt(d.y ) -360 ; 
                                        })
    .outerRadius(function(d) { return Math.sqrt(d.y + d.dy   ) -360 ; });

var json, csv;
var root;
var path;

var percentageData = [];
d3.json("data3_percentage.php", function(error, data) {
        //get the data from Database - PHP 
        data.forEach(function(d){
            percentageData.push(d);
        });
});

var givenData = []; 
d3.json("data3.php", function(error, data) {
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
      .enter()
      .append("a")
      .attr("xlink:href", function(d){ return "https://en.wikipedia.org#";} )
      .attr("target","_blank")
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
      .style("cursor", "zoom-in")
      .attr("id",function(d){
            return d.depth+"_"+d.name.replace(':','') ;
        })
      //.on("click", enableCenter)
      .on("mouseenter", enableCenter);
      
  var limitTextDisplay = 500;
  var radiusD = radius -400;
  var adjustR = { r1:radiusD-130 , r2:radiusD-70  , r3:radiusD-20 , r4:radiusD };
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
                            if(+d.depth==1) 
                                return 1; 
                            else if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1600))
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
  d3.select("#chart").on("mouseleave", disableCenter);
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
  if(isSet)
  {
      var percentage = (100 * d.value / totalSize).toPrecision(3);
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
      sequenceArray.unshift({  depth:  0,
                        name: percentageData[0]["policyTitle"],
                        policyTitle:  percentageData[0]["policyTitle"],
                        total : percentageData[0]["total"],
                        percentage_delete:percentageData[0]["percentage_delete"],
                        percentage_keep:percentageData[0]["percentage_keep"],
                        percentage_other:percentageData[0]["percentage_other"],
                        percentage_total:1.00 } );
                        
      updateBreadcrumbs(sequenceArray, percentageString);
    
      // Fade all the segments.
      d3.selectAll("path")
          .style("opacity", 0.4);
      d3.selectAll(".pathlabel")
        .style("opacity",function(d){
                            if(+d.depth==1) 
                                return 0.3 
                            else if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1600))
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
      .duration(1000)
      .style("opacity", 1);
      
  d3.selectAll(".pathlabel")
      .transition()
      .duration(1000)
        .style("opacity",function(d){
                            if(+d.depth==1) 
                                return 1; 
                            else if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1600))
                                return 1;
                            else
                                return 0.01;
                        })
      .style("fill", "#777");

  d3.select("#explanation")
      .style("visibility", "hidden");
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

  entering.append("a")
            .attr("xlink:href", function(d){ return "https://en.wikipedia.org"+d.policyURL;} )
            .attr("target","_blank")
              .style("cursor", function(d){
                if(+d.depth >1)
                    return "zoom-in";
                else
                    return "default";
              })
              .append("svg:polygon")
              .attr("points", breadcrumbPoints)
              .style("fill", function(d,i) { 
                            if(+d.depth>0)
                                return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                            else
                                return "#ddd";
              })
              .style("opacity", 0.5);
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
            .text("Percentage of mentioned policies in AfDs:")          
            .attr("x", -105 )
            .attr("y", circleCenter.y );
            
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
                        d["percentage_total"] = (d.value / totalSize).toPrecision(3) ;
                     }
            }
            var url = d.policyURL;
            d3.select(this.parentNode)
              .attr("xlink:href", function(d){ return "https://en.wikipedia.org"+url;} );
        });
            
            
        path.append("title")
            .text(function(d) { return  d.policyTitle; });
        
        
        
        colorCodeOfPath();
    }
}

var distance = 21;
var heightRec = 16;
var baseX = 399 ;
var baseYText = -288;
var baseYtotal = baseYText + 122 ;
var baseYDelete = baseYtotal + 122 ;
var baseYOther = baseYDelete + 122 ;
var baseYKeep = baseYOther + 122 ;
var opacityTextBackground = 0.50;
    var percentageScale = d3.scale.linear()
        .domain([0.0,0.99])
        .range([0.5,250]);

function colorCodeOfPath()
{
    var startX = - circleCenter.x + 5 ;
    var startY = circleCenter.y -10 - 65;
    var strokeColor = "rgb(136, 136, 136)";
    var distance2 = distance + 1;
    
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Hover Coding")          
        .attr("x", startX    )
        .attr("y", startY - 6 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Normal")            
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*0 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Delete")          
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*1 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Other")          
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*2 );
    vis.append("text")                
        .attr("class", "legendText")    
        .text("Keep")          
        .attr("x", startX + heightRec + 3 )
        .attr("y", startY + 12 + distance2*3 );
            
    //All 
    vis.append("rect")
        .attr("class", "colorCodeOfPath")
        .style("fill", "#ddd")
        .style("cursor", "pointer")
        .style("stroke-width", "1")
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
            path.style("fill", function(d,i) {
                return color_random(d.name.replace("Wikipedia:",""));
            });
        });
    
    //Delete 
    vis.append("rect")
        .attr("class", "colorCodeOfPath")
        .style("fill", "red")
        .style("cursor", "pointer")
        .style("stroke-width", "1")
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY+distance2;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
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
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY+distance2+distance2;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
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
        .style("stroke", function (d){return  strokeColor;})
        .attr("x", function (d){return  startX;} )
        .attr("y", function (d){return  startY+distance2+distance2+distance2;} )
        .attr("height",function (d){return  heightRec;} )
        .attr("width", function (d){return  heightRec;})
        .on("mouseenter", function (d){
            path.style("fill", function(d,i) {
                return colorScaleKeep(+d.percentage_keep);
            });
        });
}

var ps = {
  sx:baseX - (heightRec*3) -3 , 
  sy:baseYText+5 , 
  w: (heightRec*3) , 
  h: heightRec, 
  dy: heightRec/2, 
  dx: 0, 
  d:distance,
  wLine: 100,
  hLine: 1
};

var inistialText =  false;
/*
|\/|
|  |___
|   ___|
 \/
*/
function calculatePoint(d, i) {
  var points = [];
  var space = ps.d*i;
  points.push((ps.sx) +","+(ps.sy+space)); // 1
  points.push((ps.sx + (ps.w/2)) + "," + (ps.sy+ps.dy+space)); //2
  points.push((ps.sx + ps.w) + "," +(ps.sy+space)); //3
  points.push((ps.sx + ps.w)+ "," + (ps.sy+ps.h+space-ps.hLine) ); //4
  points.push((ps.sx + ps.w+ps.wLine)+ "," + (ps.sy+ps.h+space-ps.hLine) ); //5
  points.push((ps.sx + ps.w+ps.wLine)+ "," + (ps.sy+ps.h+space) ); //6
  points.push((ps.sx + ps.w)+ "," + (ps.sy+ps.h+space) ); //7
  points.push((ps.sx + (ps.w/2)) + "," + (ps.sy+ps.h+ps.dy+space) ); //8
  points.push(ps.sx+"," + (ps.sy+ps.h+space));//9
  return points.join(" ");
}
var rectData; 
// Added 
function displayBarChar(d)
{
    rectData = getAncestors(d);
    rectData.unshift({  depth:  0,
                        name: percentageData[0]["policyTitle"],
                        policyURL:  "#",
                        policyTitle:  percentageData[0]["policyTitle"],
                        total : percentageData[0]["total"],
                        percentage_delete:percentageData[0]["percentage_delete"],
                        percentage_keep:percentageData[0]["percentage_keep"],
                        percentage_other:percentageData[0]["percentage_other"],
                        percentage_total:1.00 } );
    
    vis.selectAll(".barChartTextArrow").remove();
    vis.selectAll(".barChartTextArrow")
        .data(rectData)
        .enter()
        .append("svg:polygon")
        .attr("class", "barChartTextArrow")
        .attr("points", calculatePoint)
        .style("stroke-width", "1")
        .style("stroke", "#888")
        .style("fill", function(d,i) { 
                if(+d.depth>0)
                        return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
                    else
                        return "#ddd";
      });
    
    
    if(!inistialText)
    {
        inistialText = true;
        vis.append("text")                
            .attr("class", "legendText")    
            .text("Policies' Title")          
            .attr("x", -circleCenter.x   )
            .attr("y", -circleCenter.y +10);
        vis.append("text")                
            .attr("class", "legendText")    
            .text("Policies' Shortcuts:")          
            .attr("x", baseX - (heightRec+heightRec+ heightRec) - 4 )
            .attr("y", baseYText );
        vis.append("text")                
            .attr("class", "legendText")    
            .text("Percentage of mentioned policies in AfDs:")          
            .attr("x", baseX - (heightRec+heightRec+ heightRec) - 4 )
            .attr("y", baseYtotal );
        vis.append("text")                
            .attr("class", "legendText")    
            .text("Delete - %:")          
            .attr("x", baseX - (heightRec+heightRec+ heightRec) - 4 )
            .attr("y", baseYDelete  );
        vis.append("text")                
            .attr("class", "legendText")    
            .text("Other - %:")          
            .attr("x", baseX - (heightRec+heightRec+ heightRec) - 4 )
            .attr("y", baseYOther );
        vis.append("text")                
            .attr("class", "legendText")    
            .text("Keep - % :")          
            .attr("x", baseX - (heightRec+heightRec+ heightRec) - 4 )
            .attr("y", baseYKeep );
    }
    
    //Title Section   
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
                  .attr("y", function(d, i ) {return baseYText + 12 + distance*i; })
                  .text(function(d) { return d.name ;  })
    vis.selectAll(".barChartTotal").remove();
    vis.selectAll(".barChartTotal")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartTotal")
                .style("fill", "#ddd")
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYtotal + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_total));})
                .append("title").text(function(d) { return  d.name+"\nTitle: "+d.policyTitle; });
    vis.selectAll(".barChartLegend2").remove();
    vis.selectAll(".barChartLegend2")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend2")
                .style("fill", function (d){  
                    if(+d.depth>0)
                        return color_random(d.name.replace("Wikipedia:",""));
                    else
                        return "#EEE";
                })
                .style("stroke-width", "1")
                .style("stroke", "#888")
                .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) - 3 ;})
                .attr("y", function(d, i ) {return baseYtotal + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){ return heightRec + heightRec+ heightRec; })
                .style("opacity", opacityTextBackground);
    vis.selectAll(".barChartLegendText2").remove();
    vis.selectAll(".barChartLegendText2")
                  .data(rectData)
                  .enter()
                  .append("svg:text")
                  .attr("class", "barChartLegendText2")
                  .style("fill", "#222")
                  .attr("dy", "0.38em")
                  .attr("text-anchor", "right")
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) ;})
                  .attr("y", function(d, i )  {return baseYtotal + 12 + distance*i; })
                  .text(function(d) {  if(+d.percentage_total == 1.00)
                                            return (+(d.percentage_total*100)).toFixed(1)+"%";
                                       else
                                            return (+(d.percentage_total*100)).toFixed(2)+"%";  
                                                });
     
    
    vis.selectAll(".line_dash").remove();
    vis.append("line")
                .attr("class", "line_dash")
                .attr("x1", baseX+percentageScale(+(rectData[0].percentage_delete))+3)
                .attr("y1", baseYDelete+ 8)
                .attr("x2", baseX+percentageScale(+(rectData[0].percentage_delete))+3)
                .attr("y2", baseYDelete + (rectData.length*distance))
                .attr("stroke-width", 1)
                .style("stroke-dasharray", ("3, 3"))
                .attr("stroke", "#bbb");
    vis.append("line")
                .attr("class", "line_dash")
                .attr("x1", baseX+percentageScale(+(rectData[0].percentage_other))+3)
                .attr("y1", baseYOther+ 8)
                .attr("x2", baseX+percentageScale(+(rectData[0].percentage_other))+3)
                .attr("y2", baseYOther + (rectData.length*distance))
                .attr("stroke-width", 1)
                .style("stroke-dasharray", ("3, 3"))
                .attr("stroke", "#bbb");
    vis.append("line")
                .attr("class", "line_dash")
                .attr("x1", baseX+percentageScale(+(rectData[0].percentage_keep))+3)
                .attr("y1", baseYKeep + 8)
                .attr("x2", baseX+percentageScale(+(rectData[0].percentage_keep))+3)
                .attr("y2", baseYKeep + (rectData.length*distance))
                .attr("stroke-width", 1)
                .style("stroke-dasharray", ("3, 3"))
                .attr("stroke", "#bbb");
                
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
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYDelete + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_delete));})
                .append("title").text(function(d) { return  d.name+"\nTitle: "+d.policyTitle; });
    vis.selectAll(".barChartLegend3").remove();
    vis.selectAll(".barChartLegend3")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend3")
                .style("fill", function (d){  
                    if(+d.depth>0)
                        return color_random(d.name.replace("Wikipedia:",""));
                    else
                        return "#EEE";
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
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) ;})
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
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYOther + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_other));})
                .append("title").text(function(d) { return  d.name+"\nTitle: "+d.policyTitle; });
    vis.selectAll(".barChartLegend4").remove();
    vis.selectAll(".barChartLegend4")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend4")
                .style("fill", function (d){  
                    if(+d.depth>0)
                        return color_random(d.name.replace("Wikipedia:",""));
                    else
                        return "#EEE";
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
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) ;})
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
                .attr("x", function(d, i ) {return baseX  ;})
                .attr("y", function(d, i ) {return baseYKeep + 5 + distance*i; })
                .attr("height", function (d){ return heightRec; })
                .attr("width", function (d){return  percentageScale(+(d.percentage_keep));})
                .append("title").text(function(d) { return  d.name+"\nTitle: "+d.policyTitle; });
    vis.selectAll(".barChartLegend5").remove();
    vis.selectAll(".barChartLegend5")
                .data(rectData)
                .enter()
                .append("rect")
                .attr("class", "barChartLegend5")
                .style("fill", function (d){  
                    if(+d.depth>0)
                        return color_random(d.name.replace("Wikipedia:",""));
                    else
                        return "#EEE";
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
                  .attr("x", function(d, i ) {return baseX - (heightRec+heightRec+ heightRec) ;})
                  .attr("y", function(d, i )  {return baseYKeep + 12 + distance*i; })
                  .text(function(d) { return (+(d.percentage_keep*100)).toFixed(2)+"%";  });
                
                
}




