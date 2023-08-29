<?php

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++)
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    return $randomString;
}

?>

<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://creativecommons.org/ns#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   version="1.1"
   id="svg2"
   viewBox="0 0 669.99995 140"
   height="140"
   width="670">
  <defs
     id="defs4">
    <style
       id="style4180">.cls-1{fill:#231f20;}.cls-2{fill:#fff;}</style>
  </defs>
  <metadata
     id="metadata7">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
        <dc:title></dc:title>
      </cc:Work>
    </rdf:RDF>
  </metadata>
  <g
     transform="translate(0,20)"
     style="display:inline"
     id="layer3" />
  <g
     transform="translate(0,20)"
     style="display:inline"
     id="layer2" />
  <g
     style="display:inline"
     transform="translate(0,-912.36218)"
     id="layer1">
    <rect
       y="985.97083"
       x="29.698454"
       height="1.237"
       width="601.74786"
       id="rect4198"
       style="fill:#1f497d;fill-opacity:1;stroke-width:10;stroke-miterlimit:4;stroke-dasharray:none" />
    <rect
       y="958.09515"
       x="29.697969"
       height="41.719299"
       width="1.2374369"
       id="rect4206"
       style="fill:#1f497d;fill-opacity:1;stroke-width:10;stroke-miterlimit:4;stroke-dasharray:none" />
    <text
       transform="matrix(0,-1,1,0,0,0)"
       id="text4821-2"
       y="18.32262"
       x="-944.9364"
       style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:10px;line-height:120.00000477%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:start;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:start;display:inline;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:10px;line-height:120.00000477%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:end;writing-mode:lr-tb;text-anchor:end;fill:#000000;fill-opacity:1"
         y="18.32262"
         x="-944.9364"
         id="tspan4861">WDS<tspan
   id="tspan12"
   style="font-style:italic;fill:#000000;fill-opacity:1">risk </tspan>score</tspan></text>
    <text
       id="text4863-7"
       y="1010.4948"
       x="26.868416"
       style="font-style:normal;font-weight:normal;font-size:40px;line-height:125%;font-family:sans-serif;letter-spacing:0px;word-spacing:0px;display:inline;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:10px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:start;writing-mode:lr-tb;text-anchor:start"
         y="1010.4948"
         x="26.868416"
         id="tspan4865-6">0</tspan></text>
    
    <!-- All Std Devs on graph, not including +3 -->

    <?php foreach($svgObjectsArray as $svgObject): ?>

    <rect
       y="985.97235"
       x="<?php echo $svgObject->xPos; ?>"
       height="11.485882"
       width="1.237"
       id="<?php echo generateRandomString(); ?>"
       style="display:inline;fill:#1f497d;fill-opacity:1;stroke-width:10;stroke-miterlimit:4;stroke-dasharray:none" />
    <text
       id="<?php echo generateRandomString(); ?>"
       y="1023.9615"
       x="<?php echo $svgObject->xPos; ?>"
       style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:11.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:middle;display:inline;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:11.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;writing-mode:lr-tb;text-anchor:middle"
         y="1023.9615"
         x="<?php echo $svgObject->xPos; ?>"
         id="<?php echo generateRandomString(); ?>"><?php echo $svgObject->htmlChars; ?></tspan></text>
    <text
       id="<?php echo generateRandomString(); ?>"
       y="1034.1753"
       x="<?php echo $svgObject->xPos; ?>"
       style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:6.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Bold';text-align:center;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:middle;display:inline;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:6.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Bold';text-align:center;writing-mode:lr-tb;text-anchor:middle"
         y="1034.1753"
         x="<?php echo $svgObject->xPos; ?>"
         id="<?php echo generateRandomString(); ?>"><?php echo $svgObject->simpleText; ?></tspan><tspan
         id="<?php echo generateRandomString(); ?>"
         style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:6.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Bold';text-align:center;writing-mode:lr-tb;text-anchor:middle"
         y="1041.9878"
         x="<?php echo $svgObject->xPos; ?>"><?php if ($svgObject->type !== RiskSVG::X_BAR) { echo 'DEVIATIONS'; } else { echo 'MEAN'; } ?></tspan></text>
    <text
       id="<?php echo generateRandomString(); ?>"
       y="1007.0759"
       x="<?php echo $svgObject->xPos; ?>"
       style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.75px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:middle;display:inline;fill:#1f497d;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         id="<?php echo generateRandomString(); ?>"
         style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.75px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;writing-mode:lr-tb;text-anchor:middle;fill:#1f497d;fill-opacity:1"
         y="1007.0759"
         x="<?php echo $svgObject->xPos; ?>"><?php echo $svgObject->riskScore; ?></tspan></text>

    <?php endforeach; ?>
      
    <!-- +3 Std Dev -->
    <rect
       y="985.97235"
       x="630.20996"
       height="11.485882"
       width="1.237"
       id="rect4206-6"
       style="display:inline;fill:#1f497d;fill-opacity:1;stroke-width:10;stroke-miterlimit:4;stroke-dasharray:none" />
    <text
       id="text4863-2-7-2-6"
       y="1023.9615"
       x="631.91095"
       style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:11.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:middle;display:inline;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:11.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;writing-mode:lr-tb;text-anchor:middle"
         y="1023.9615"
         x="631.91095"
         id="tspan4865-7-5-8-49">x&#772; + 3&#963;</tspan></text>
    <text
       id="text4863-2-7-2-6-4"
       y="1034.1753"
       x="631.65173"
       style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:6.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Bold';text-align:center;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:middle;display:inline;fill:#000000;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:6.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Bold';text-align:center;writing-mode:lr-tb;text-anchor:middle"
         y="1034.1753"
         x="631.65173"
         id="tspan4865-7-5-8-49-7">+3 STANDARD</tspan><tspan
         id="tspan4174"
         style="font-style:normal;font-variant:normal;font-weight:bold;font-stretch:normal;font-size:6.25px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Bold';text-align:center;writing-mode:lr-tb;text-anchor:middle"
         y="1041.9878"
         x="631.65173">DEVIATIONS</tspan></text>
    <text
       id="text4863-2-7-2-6-4-5"
       y="1007.0759"
       x="629.92462"
       style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.75px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;letter-spacing:0px;word-spacing:0px;writing-mode:lr-tb;text-anchor:middle;display:inline;fill:#1f497d;fill-opacity:1;stroke:none;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
       xml:space="preserve"><tspan
         id="tspan4174-6"
         style="font-style:normal;font-variant:normal;font-weight:normal;font-stretch:normal;font-size:8.75px;line-height:125%;font-family:sans-serif;-inkscape-font-specification:'sans-serif, Normal';text-align:center;writing-mode:lr-tb;text-anchor:middle;fill:#1f497d;fill-opacity:1"
         y="1007.0759"
         x="629.92462"><?php echo number_format($plus_3_std_dev, 6); ?></tspan></text>

    <!-- House Icon -->
    <g
       transform="matrix(0.07566147,0,0,0.07566147,<?php echo $risk_svg_house_pos; ?>,940.22087)"
       id="g4205">
      <g
         transform="matrix(1.796804,0,0,1.796804,-1919.3955,-874.0548)"
         id="layer1-3">
        <g
           style="display:inline"
           id="g4274"
           transform="matrix(0.14389246,0,0,0.14389246,251.70342,866.57462)">
          <path
             style="fill:#000000;fill-opacity:1;stroke:#000000;stroke-width:3.93811417;stroke-opacity:1"
             id="path9-0"
             d="m 1839.4886,-2715.9391 c -510.6946,0 -926.2051,415.471 -926.2051,926.205 0,770.8859 926.2051,1428.55097 926.2051,1428.55097 0,0 926.2051,-657.66507 926.2051,-1428.55097 0,-510.6946 -415.5104,-926.205 -926.2051,-926.205 z"
             class="cls-1" />
          <path
             style="fill:#ffffff"
             id="path11"
             d="m 1834.9042,-2590.9911 c -440.6835,0 -799.1879,358.5045 -799.1879,799.188 0,440.6836 358.5044,799.18795 799.1879,799.18795 440.6836,0 799.188,-358.50435 799.188,-799.18795 0,-440.6835 -358.5044,-799.188 -799.188,-799.188 z"
             class="cls-2" />
          <g
             transform="matrix(4.0745535,0,0,4.0745535,541.02474,-3340.8784)"
             id="hand_house">
            <path
               d="m 325,245.4 126.6,124.5 c 3.8,3.8 4.8,9.1 2.8,14 -2,4.9 -6.6,8 -11.9,8 l -5.5,0 -121.2,-119 -120.5,119 -5.5,0 c -5.3,0 -9.9,-3 -11.9,-8 -2,-4.9 -1,-10.3 2.8,-14 l 126,-124.5 c 5.1,-5 13.3,-5 18.3,0 l 0,0 z"
               id="path8" />
            <polygon
               points="329.5,469.9 321.1,469.9 321.1,451.6 321.1,451.6 330.3,451.6 330.3,469.5 "
               id="polygon10" />
            <polygon
               points="418.4,245.7 379.4,245.7 379.4,282.1 418.4,318.7 418.4,245.7 "
               id="polygon12" />
            <path
               d="m 418.4,391.9 -102.5,-100.7 -74.8,73.2 -27.5,27.5 0,85.8 c 11,-7.5 25.8,-21.6 41.2,-21.6 9.1,0 27.7,5.7 36.9,11.4 l 0,-46 48.5,0 0,46.9 c 8,-7 16,-12.3 25.6,-12.3 14.8,0 38,17.6 52.5,21 l 0.1,-85.2 0,0 z"
               id="path14" />
            <rect
               x="213.7"
               y="423"
               width="78"
               height="60.200001"
               id="rect16" />
            <rect
               x="340.39999"
               y="425.79999"
               width="78"
               height="60.200001"
               id="rect18" />
          </g>
        </g>
      </g>
    </g>

  </g>
</svg>
