<?php

class RiskSVG
{
    const MINUS_3 = 1;
    const MINUS_2 = 2;
    const MINUS_1 = 3;
    const X_BAR = 4;
    const PLUS_1 = 5;
    const PLUS_2 = 6;

    public $type;
    public $xPos;
    public $riskScore;
    public $htmlChars;
    public $simpleText;

    public function __construct($type, $xPos, $riskScore, $htmlChars, $simpleText)
    {
        $this->type = $type;
        $this->xPos = $xPos;
        $this->riskScore = $riskScore;
        $this->htmlChars = $htmlChars;
        $this->simpleText = $simpleText;
    }
}

/* @var $this RiskModelController */
/* @var $stateMeanModel RiskStateMeans */
/* @var $reportForm RiskReportForm */

Yii::app()->clientScript->registerCssFile('/css/riskModel/risk_report.css');
Assets::registerMapboxPackage();

// Risk Values

$risk_v = round($reportForm->risk_v, 6);
$risk_whp = round($reportForm->risk_whp, 6);
$risk_wds = round($reportForm->risk_wds, 6);

$risk_wds_display = $risk_wds;

// Calculating Standard Deviations for SVG

$state = $stateMeanModel->state->abbr;
$state_mean = $stateMeanModel->mean;
$state_std_dev = $stateMeanModel->std_dev;

$minus_3_std_dev = round($state_mean - (3 * $state_std_dev), 6);
$minus_2_std_dev = round($state_mean - (2 * $state_std_dev), 6);
$minus_1_std_dev = round($state_mean - $state_std_dev, 6);
$x_bar = round($state_mean, 6);
$plus_1_std_dev = round($state_mean + $state_std_dev, 6);
$plus_2_std_dev = round($state_mean + (2 * $state_std_dev), 6);
$plus_3_std_dev = round($state_mean + (3 * $state_std_dev), 6);

// Testing for outliers where value lies outside +- 3 standard deviations of the mean, this is VERY unlikely

if ($risk_wds < $minus_3_std_dev)
{
    $risk_wds = $minus_3_std_dev;
}
if ($risk_wds > $plus_3_std_dev)
{
    $risk_wds = $plus_3_std_dev;
}

// Calculating Slider position on SVG

$low_px = 29.698;  // Start of Graph
$high_px = 630.210; // End of Graph

$pixel_range = $high_px - $low_px;

$svgObjectsArray = array();

// Adding std dev objects to array to add to scale in the svg risk graphic

if ($minus_3_std_dev > 0)
{
    $xPos = $low_px + ($pixel_range * ($minus_3_std_dev / $plus_3_std_dev));
    $svgObjectsArray[] = new RiskSVG(RiskSVG::MINUS_3, $xPos, number_format($minus_3_std_dev, 6), 'x&#772; - 3&#963;', '-3 STANDARD');
}
if ($minus_2_std_dev > 0)
{
    $xPos = $low_px + ($pixel_range * ($minus_2_std_dev / $plus_3_std_dev));
    $svgObjectsArray[] = new RiskSVG(RiskSVG::MINUS_2, $xPos, number_format($minus_2_std_dev, 6), 'x&#772; - 2&#963;', '-2 STANDARD');
}
if ($minus_1_std_dev > 0)
{
    $xPos = $low_px + ($pixel_range * ($minus_1_std_dev / $plus_3_std_dev));
    $svgObjectsArray[] = new RiskSVG(RiskSVG::MINUS_1, $xPos, number_format($minus_1_std_dev, 6), 'x&#772; - &#963;', '-1 STANDARD');
}
if ($x_bar > 0)
{
    $xPos = $low_px + ($pixel_range * ($x_bar / $plus_3_std_dev));
    $svgObjectsArray[] = new RiskSVG(RiskSVG::X_BAR, $xPos, number_format($x_bar, 6), 'x&#772;', 'STATE');
}
if ($plus_1_std_dev > 0)
{
    $xPos = $low_px + ($pixel_range * ($plus_1_std_dev / $plus_3_std_dev));
    $svgObjectsArray[] = new RiskSVG(RiskSVG::PLUS_1, $xPos, number_format($plus_1_std_dev, 6), 'x&#772; + &#963;', '+1 STANDARD');
}
if ($plus_2_std_dev > 0)
{
    $xPos = $low_px + ($pixel_range * ($plus_2_std_dev / $plus_3_std_dev));
    $svgObjectsArray[] = new RiskSVG(RiskSVG::PLUS_2, $xPos, number_format($plus_2_std_dev, 6), 'x&#772; + 2&#963;', '+2 STANDARD');
}

// Calculating position of the "house"
// If the "wdsrisk" is over 3 std dev, don't show the house off the graph

$risk_svg_house_pos = 0;

if ($risk_wds > $plus_3_std_dev)
{
    $risk_svg_house_pos = 705.85115;
}
else
{
    $risk_svg_house_pos = 105.35896 + ((705.85115 - 105.35896) * ($risk_wds / $plus_3_std_dev));
}

// Determining Standard Deviation 'human readable' text

$std_dev_text_verbose = '';

if ($risk_wds == $minus_3_std_dev)
    $std_dev_text_verbose = "The property is below the <b>THIRD STANDARD DEVIATION BELOW THE MEAN</b> for the state of $state.";
else if ($risk_wds <= $minus_2_std_dev)
    $std_dev_text_verbose = "The property is within the <b>THIRD STANDARD DEVIATION BELOW THE MEAN</b> for the state of $state.";
else if ($risk_wds <= $minus_1_std_dev)
    $std_dev_text_verbose = "The property is within the <b>SECOND STANDARD DEVIATION BELOW THE MEAN</b> for the state of $state.";
else if ($risk_wds <= $x_bar)
    $std_dev_text_verbose = "The property is within the <b>FIRST STANDARD DEVIATION BELOW THE MEAN</b> for the state of $state.";
else if ($risk_wds <= $plus_1_std_dev)
    $std_dev_text_verbose = "The property is within the <b>FIRST STANDARD DEVIATION ABOVE THE MEAN</b> for the state of $state.";
else if ($risk_wds <= $plus_2_std_dev)
    $std_dev_text_verbose = "The property is within the <b>SECOND STANDARD DEVIATION ABOVE THE MEAN</b> for the state of $state.";
else if ($risk_wds < $plus_3_std_dev)
    $std_dev_text_verbose = "The property is within the <b>THIRD STANDARD DEVIATION ABOVE THE MEAN</b> for the state of $state.";
else if ($risk_wds == $plus_3_std_dev)
    $std_dev_text_verbose = "The property is above the <b>THIRD STANDARD DEVIATION ABOVE THE MEAN</b> for the state of $state.";

?>

<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>WDS Risk Report</title>
		<meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/images/logo.png">
	</head>
	<body>
        <div id="container">
            <div id="content">

                <!-- Header -->

                <div id="header" class="margin-bottom-20">
                    <table class="text-center">
                        <tr>
                            <td colspan="3">
                                <img src="/images/riskModel/WDSrisk.jpg" alt="WDSRisk">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <h2><?php echo $reportForm->address; ?></h2>
                            </td>
                        </tr>
                        <tr>
                            <td style="width:40%" class="text-left">Lat/Lon: <?php echo number_format(round($reportForm->lat, 4), 4) . ', ' . number_format(round($reportForm->lon, 4), 4); ?></td>
                            <td style="width:20%"></td>
                            <td style="width:40%" class="text-right">Report Generated: <?php echo date('m/d/Y'); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Risk Score -->

                <div class="wds-risk-container text-center margin-bottom-10">
                    <div id="wds-risk-score">
                        <div id="risk-score"><b><?php echo number_format($risk_wds_display, 6); ?></b><br /><b style="font-size:80%;">WDS<i>risk</i></b></div>
                        <div id="risk-score-description">The WDS<i>risk</i> score combines the structure’s Vulnerability Index and its Wildfire Hazard Potential Score to determine the probability of loss or damage to the structure from wildfire.</div>
                    </div>
                </div>

                <!-- Std Deviation -->

                <div class="wds-risk-container margin-bottom-10">
                    <div id="std-deviation-container" class="text-center">
                        <div id="std-deviation-verbose"><?php echo $std_dev_text_verbose; ?></div>
                    </div>
                    <div id="std-deviation-svg">
                        <?php include('risk_report_svg.php'); ?>
                    </div>
                </div>

                <!-- Map -->

                <div id="wds-risk-container">
                    <div id="map-container">
                        <div id="map"></div>
                    </div>
                </div>

            </div>
        </div>

        <script type="text/javascript">

            var geojson = JSON.parse(<?php echo json_encode($reportForm->geojson); ?>);

            var riskColor = {
                0: '#006837',
                1: '#1A9850',
                2: '#A6D96A',
                3: '#FFFFAF',
                4: '#FFFF00',
                5: '#F0C800',
                6: '#FFA500',
                7: '#FF4500',
                8: '#FF0000',
                9: '#8B0000'
            };

            function mapGetRiskColor(r) {
                return riskColor[r];
            }

            function mapLegend() {
                var canvas = document.createElement('canvas');
                var context = canvas.getContext('2d');

                canvas.height = 70;
                canvas.width = 100;
                canvas.style.margin = '10px';

                context.rect(0, 0, canvas.width / 2, canvas.height);

                context.font = 'bold 15px Arial';
                context.fillText('High', canvas.width * 0.6, canvas.height * 0.2);
                context.fillText('Low', canvas.width * 0.6, canvas.height * 0.9);

                var gradient = context.createLinearGradient(0, 0, 0, canvas.height);
                gradient.addColorStop(0, '#a50026');
                gradient.addColorStop(0.5, '#d9ef8b');
                gradient.addColorStop(1, '#006837');
                context.fillStyle = gradient;
                context.fillRect(0, 0, canvas.width / 2, canvas.height);

                return canvas;
            }

            L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
            L.mapbox.config.FORCE_HTTPS = true;

            var map = new L.mapbox.map('map');

            //map.legendControl.getContainer().appendChild(mapLegend());
            //map.legendControl.getContainer().style.display = 'block';
            map.zoomControl.removeFrom(map);
            map.attributionControl.removeFrom(map);

            map.dragging.disable();
            map.touchZoom.disable();
            map.doubleClickZoom.disable();
            map.scrollWheelZoom.disable();
            if (map.tap) map.tap.disable();

            var esriTileOptions = {
                detectRetina: true,
                reuseTiles: true,
                subdomains: ['server', 'services']
            };

            new L.LayerGroup([
                new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', esriTileOptions),
                new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', esriTileOptions)
            ]).addTo(map);

            var featureLayer = new L.mapbox.featureLayer(geojson).eachLayer(function(layer) {
                layer.setStyle({
                    fillColor: mapGetRiskColor(layer.feature.properties.risk),
                    color: mapGetRiskColor(layer.feature.properties.risk),
                    weight: 0,
                    opacity: 0.4,
                    fillOpacity: 0.6
                });
            }).addTo(map);

            var marker = new L.CircleMarker(new L.LatLng(<?php echo $reportForm->lat . ', ' . $reportForm->lon; ?>)).setStyle({
                color: 'black',
                weight: 2,
                opacity: 0.6,
                fillColor: 'steelblue',
                fillOpacity: 0.8,
                radius: 4
            }).addTo(map);

            map.fitBounds(featureLayer.getBounds());

        </script>

	</body>
</html>