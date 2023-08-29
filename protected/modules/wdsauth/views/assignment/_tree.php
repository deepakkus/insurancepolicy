<?php

/* @var $name string */
/* @var $data array */

?>

<p class="lead"><u>Heirarchy tree for: <em><?php echo $name; ?></em></u></p>

<svg width="1200" height="600" version="1.1" xmlns="http://www.w3.org/2000/svg"></svg>

<!--<svg viewbox="0 0 1200 600" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg"></svg>-->

<script type="text/javascript">

    var authData = <?php echo json_encode($data); ?>;

    var svg = d3.select("svg");
    var width =+ svg.attr("width");
    var height =+ svg.attr("height");
    var g = svg.append("g").attr("transform", "translate(180,0)");

    var tree = d3.cluster().size([height, width - 440]);

    var stratify = d3.stratify().parentId(function(d) {
        return d.id.substring(0, d.id.lastIndexOf("."));
    });

    var root = d3.hierarchy(authData);
    tree(root);

    var link = g.selectAll(".link")
        .data(root.descendants().slice(1))
        .enter().append("path")
        .attr("class", "link")
        .attr("d", function(d) {
            return "M" + d.y + "," + d.x
                + "C" + (d.parent.y + 100) + "," + d.x
                + " " + (d.parent.y + 100) + "," + d.parent.x
                + " " + d.parent.y + "," + d.parent.x;
            });

    var node = g.selectAll(".node")
        .data(root.descendants())
        .enter().append("g")
        .attr("class", function(d) { return "node" + (d.children ? " node--internal" : " node--leaf"); })
        .attr("transform", function(d) { return "translate(" + d.y + "," + d.x + ")"; })

    node.append("circle")
        .attr("r", 2.5);

    node.append("text")
        .attr("dy", 3)
        .attr("x", function(d) { return d.children ? -8 : 8; })
        .style("text-anchor", function(d) { return d.children ? "end" : "start"; })
        .text(function(d) { return d.data.name + " (" + d.data.type + ")"; });

</script>
