<?php
drupal_add_js("https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js");
drupal_add_js("https://cdnjs.cloudflare.com/ajax/libs/queue-async/1.0.7/queue.min.js");
drupal_add_js("https://cdnjs.cloudflare.com/ajax/libs/topojson/1.6.19/topojson.min.js");
?>
<style>
 @import url(https://fonts.googleapis.com/css?family=PT+Sans:400,700,400italic,700italic);

 body {
   background: url("/iframes/d3exp/12197898755_b3c85b617a_k_d.jpg") no-repeat center center fixed;
   -webkit-background-size: cover;
   -moz-background-size: cover;
   -o-background-size: cover;
   background-size: cover;
 }
 .announce {
   font-family:"PT sans", sans-serif;
   font-size:18px;
   text-align:center;
   margin:0 auto;
 }
 .announce h1{
   font-size:30px;
   font-weight:bold;
   text-align:center;
   color:#FFF;
   margin-bottom:0;
   margin-top:10px;
 }
 .announce h2{
   font-weight:normal;
   font-size:60px;
   color:#fff;
   margin:0;
 }
 #legendRanges{
   width: 500px;
   margin: 0 auto;
 }
 #legendRanges ul{
   margin: 0;
   padding: 0;
   list-style-type: none;
   text-align: left;
 }
 #legendRanges ul li{
   float:left;
   padding-left:10px;
   font: 11px "PT sans";
 }
 .legendText{
   font-size:14px;
   color:#fff; /*#ffc58c;*/
   position: relative;
   top: 4px;
 }
 #legendRanges ul li {
   width:90px;
 }
 #legendRanges ul li div{
   float:left;
   margin-right:5px;
 }
 #legendRanges ul li div span{
   position:relative;
   top:5px;
 }
 .imagecredit{
   font-family:"PT sans";
   font-size:10px;
   float:right;
   position:relative;
   top:100px;
   left:-20px;
   color:#ccc;
 }
 .imagecredit a{
   color:#ccc;
 }
</style>


<div class="announce" style="width:950px">
  <h1>We are all sharing the same ocean...</h1>
  However, human activity is not uniform across the globe,<br/>
  incurring global and local risk on ocean services.<br/>
  Discover details for <a href="//onesharedocean.org/open_ocean" target="_top" style="font-weight:bold">Open Ocean</a> and <a href="//onesharedocean.org/lmes" target="_top" style="font-weight:bold">Large Marine Ecosystems</a><br/>
  <h2>One Shared Ocean</h2>
  <div id="canvas" style="float:left"></div>
</div>
<div style="clear:both"></div>

<div class="announce" style="width:110px; padding:0; padding-bottom:10px; font-family: 'PT sans'; color:#fff; font-size:14px; font-weight:bold">Risk level</div>
<div  id="legendRanges" >
  <ul >
    <li><div style="border-radius:50%; width:20px; height:20px; padding:0px; background:#ca0020; border: 1px solid #CBCCCB; color:#fff; text-align:center; font: 10px Arial, sans-serif;"><span style="margin: auto auto;"></span></div> <span class="legendText">Very high</span></li>
    <li><div style="border-radius:50%; width:20px; height:20px; padding:0px; background:#f4a582; border: 1px solid #CBCCCB;  color:#fff; text-align:center; font: 10px Arial, sans-serif;"><span style="margin: auto auto;"></span></div> <span class="legendText">High</span></li>
    <li><div style="border-radius:50%; width:20px; height:20px; padding:0px; background:#c3b5b0; border: 1px solid #CBCCCB; color:#FFF; text-align:center; font: 10px Arial, sans-serif;"><span style="margin: auto auto;"></span></div> <span class="legendText">Medium</span></li>
    <li><div style="border-radius:50%; width:20px; height:20px; padding:0px; background:#92c5de; border: 1px solid #CBCCCB; color:#FFF; text-align:center; font: 10px Arial, sans-serif;"><span style="margin: auto auto;"></span></div> <span class="legendText">Low</span></li>
    <li><div style="border-radius:50%; width:20px; height:20px; padding:0px; background:#0571b0; border: 1px solid #CBCCB; color:#FFF; text-align:center; font: 10px Arial, sans-serif;"><span style="margin: auto auto;"></span></div> <span class="legendText">Very low</span></li>
  </ul>
</div>
<div style="clear:both"></div>
<div class="imagecredit">
  background image by <a href="https://www.flickr.com/photos/usfwspacific/12197898755/" target="_blank">Susan White/USFWS</a>
</div>
<div style="clear:both"></div>

<script>
 var width = 750,
 height = 500;
 //var riskColor=["#5fbadd","#78bb4b","#e4e344","#ee9f42","#d8232a"];
 var riskColor=["#0571b0","#92c5de", "#c3b5b0", "#f4a582", "#ca0020"]; // from very low to very high


 var projection = d3.geo.orthographic()
                        .scale(248)
                        .clipAngle(90);

 var canvas = d3.select("#canvas").append("canvas")
                .attr("width", width)
                .attr("height", height);

 var c = canvas.node().getContext("2d");

 var path = d3.geo.path()
                  .projection(projection)
                  .context(c);

 queue().defer(d3.json,"/iframes/d3exp/eeztopojson.json")
                  .defer(d3.json, "/iframes/d3exp/world-110m.json")
                  .defer(d3.json,"/iframes/d3exp/new_lmes66.topojson.json")
                  .defer(d3.tsv, "/iframes/d3exp/risk.tsv")
                  .await(ready);


 function ready(error, eezs, world, lmes, risk) {
   if (error) throw error;

   var globe = {type: "Sphere"};
   land = topojson.feature(world, world.objects.land);
   var countries = topojson.feature(world, world.objects.countries).features;
   var i = -1,
   n = countries.length;
   var lmes=topojson.feature(lmes, lmes.objects.lmes66);
   var eez=topojson.feature(eezs, eezs.objects.eez);
   var EEZ=topojson.feature(eezs, eezs.objects.eez).features;

   var N=risk.length,
   maxRisk=0;
   for (var ii=0; ii<N; ii++){
     if (risk[ii].Total_Risk>maxRisk) maxRisk=risk[ii].Total_Risk;
   }
   var thisEEZpos=0;

   (function transition() {
     d3.transition()
       .duration(1250)
       .each("start", function() {
         // search for this eez_id in the list
         //thisEEZid=risk[i].eez_id;
         i=(i+1)%N // position in the list of risk values
         thisEEZpos = 0;
         for (ii=0; ii<EEZ.length; ii++) {
           if (EEZ[ii].properties.ID == risk[i].eez_id) thisEEZpos=ii;
         }

       })
       .tween("rotate", function() {
         var p = d3.geo.centroid(EEZ[thisEEZpos]),
         r = d3.interpolate(projection.rotate(), [-p[0], -p[1]]);
         return function(t) {
           projection.rotate(r(t));
           if (parseFloat(risk[i].Total_Risk)>=0.1785) {
             thisColor=riskColor[4];
           } else {
             if (parseFloat(risk[i].Total_Risk)>=0.1114) { thisColor=riskColor[3]}
             else {
               if (parseFloat(risk[i].Total_Risk)>=0.0717) { thisColor=riskColor[2]}
               else {
                 if (parseFloat(risk[i].Total_Risk)>=0.0417) { thisColor=riskColor[1]}
                 else { thisColor=riskColor[0]}
               }
             }
           }

           //thisColor = riskColor[Math.floor(5*risk[i].Total_Risk/maxRisk)];
           c.clearRect(0, 0, width, height);
           // ocean/globe color: #ddfcff, cdefff
           c.fillStyle = "#0cdcf7", c.beginPath(), path(globe), c.fill();
           // land: #f9f6d8
           c.fillStyle = "#236365", c.beginPath(), path(land), c.fill();
           // EEZ
           //c.fillStyle = "#cbcccb", c.beginPath(), path(eez),c.fill();
           c.strokeStyle = thisColor, c.lineWidth=1.5, c.beginPath(),path(EEZ[thisEEZpos]),c.stroke();
           c.fillStyle = thisColor, c.beginPath(), path(EEZ[thisEEZpos]), c.fill();

           // lmes: outline a0cbd1, 29c6fb
           c.strokeStyle = "#039fd0", c.lineWidth=1.75, c.beginPath(),path(lmes),c.stroke();

           // globe frame: #a5bfdd
           c.strokeStyle = "#0cdcf7", c.lineWidth = 2, c.beginPath(), path(globe), c.stroke();
         };
       })
       .transition()
       .each("end", transition);
   })();

 }

</script>

