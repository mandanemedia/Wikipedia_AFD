// Dimensions of sunburst.
var width = 750;
var height = 640;

var radius = 630;

// Breadcrumb dimensions: width, height, spacing, width of tip/tail.
var b = {
  w: 175, h: 30, s: 3, t: 10
};
var circleCenter = {x:((width-120 ) / 2)-20, y:((height-100) / 2)+20};
  
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
                                    return Math.sqrt(d.y ) - 305 ; 
                                else
                                    return Math.sqrt(d.y ) -360 ; 
                                        })
    .outerRadius(function(d) { return Math.sqrt(d.y + d.dy   ) -360 ; });

var json, csv;
var root;
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

  var path = vis.data([json]).selectAll("path")
      .data(nodes)
      .enter().append("svg:path")
      .attr("display", function(d) { return d.depth ? null : "none"; })
      .attr("d", arc)
      .attr("fill-rule", "evenodd")
      .style("fill", "white" )
      .style("fill", function(d,i) { return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
      })
      .style("opacity", 1)
      .style("cursor", "pointer")
      //.on("click", enableCenter)
      .on("mouseenter", enableCenter);
  var limitTextDisplay = 500;
  var radiusD = radius -400;
  var adjustR = { r1:radiusD-160 , r2:radiusD-70  , r3:radiusD-20 , r4:radiusD };
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
                            if(+d.depth==1) 
                                return d.name.replace("Wikipedia:","");
                            if((+d.depth== 3 && +d.value > 800) || (+d.depth== 2 && +d.value > 1600))
                                return d.name.replace("Wikipedia:","");
                            //if(+d.depth>0) return d.delete_;
                         })
                        .attr("transform", function (d) {
                            var r = 180 * ((d.x + d.dx / 2 - Math.PI / 2) / Math.PI);
                            return "rotate(" + r + ")"
                        });
                        
      
  // Add the mouseleave handler to the bounding circle.
  d3.select("#container").on("mouseleave", disableCenter);
  //d3.select("#explanation").on("click", disableCenter);
   
  // Get total size of the tree = value of root node from partition.
  totalSize = path.node().__data__.value;
 };

function showTitle(d) {
    d3.select("#titleHover")
      .text(d.name);
}

// Fade all but the current sequence, and show it in the breadcrumb trail.
function enableCenter(d) {

  var percentage = (100 * d.value / totalSize).toPrecision(3);
  var label = d.name ;
  var percentageString = percentage + "%";
  if (percentage < 0.1) {
    percentageString = "< 0.1%";
  }

  d3.select("#title")
      //.text(label.replace("Wikipedia:",""));
      .text(label);
  
  d3.select("#percentage")
      .text( percentageString);

  d3.select("#explanation")
      .style("visibility", "")

  var sequenceArray = getAncestors(d);
  updateBreadcrumbs(sequenceArray, percentageString);

  // Fade all the segments.
  d3.selectAll("path")
      .style("opacity", 0.3);

  // Then highlight only those that are an ancestor of the current segment.
  vis.selectAll("path")
      .filter(function(node) {
                return (sequenceArray.indexOf(node) >= 0);
              })
      .style("opacity", 1);
}

function disableCenter(d){
    // Hide the breadcrumb trail
  d3.select("#trail")
      .style("visibility", "hidden");

  // Deactivate all segments during transition.
  d3.selectAll("path").on("mouseover", null);

  // Transition each segment to full opacity and then reactivate it.
  d3.selectAll("path")
      .transition()
      .duration(1000)
      .style("opacity", 1);

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
  var trail = d3.select("#sequence").append("svg:svg")
      .attr("width", width)
      .attr("height", 50)
      .attr("id", "trail");
  // Add the label at the end, for the percentage.
  trail.append("svg:text")
    .attr("id", "endlabel")
    .style("fill", "#000");
}

// Generate a string that describes the points of a breadcrumb polygon.
function breadcrumbPoints(d, i) {
  var points = [];
  points.push("0,0");
  points.push(b.w + ",0");
  points.push(b.w + b.t + "," + (b.h / 2));
  points.push(b.w + "," + b.h);
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
  var entering = g.enter().append("svg:g");

  entering.append("svg:polygon")
      .attr("points", breadcrumbPoints)
      .style("fill", function(d,i) { return color_random(d.name.replace("Wikipedia:","")); //colors[d.name]; 
      })
  entering.append("svg:text")
      .attr("x", (b.w + b.t) / 2)
      .attr("y", b.h / 2)
      .attr("dy", "0.35em")
      .attr("text-anchor", "middle")
      .text(function(d) { return d.name ;  });

  // Set position for entering and updating nodes.
  g.attr("transform", function(d, i) {
    return "translate(" + i * (b.w + b.s) + ", 0)";
  });

  // Remove exiting nodes.
  g.exit().remove();

  // Now move and update the percentage at the end.
  d3.select("#trail").select("#endlabel")
      .attr("x", (nodeArray.length + 0.5) * (b.w + b.s))
      .attr("y", b.h / 2)
      .attr("dy", "0.35em")
      .attr("text-anchor", "middle")
      .text(percentageString);

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
    //added by lili 
  }
  return root;
};


