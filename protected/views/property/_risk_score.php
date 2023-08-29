<?php

/**
 * _risk_score - a partial view to render the risk score info
 */

//Should probably move this to the controller?
$wds_risk_score = $wds_risk_whp = $wds_risk_v = $state_mean = $state_std_dev = $dev = $wds_risk_geocode_level = $wds_risk_date = $wds_risk_match_address = 'n/a';

if($property->wdsRisk && isset($property->wdsRiskStateMeans) && isset($property->wdsRiskDev)){
    $wds_risk_score = $property->risk_score;
	$wds_risk_whp = $property->wdsRisk->score_whp;
	$wds_risk_v = $property->wdsRisk->score_v;
	$state_std_dev = $property->wdsRiskStateMeans->std_dev;
	$state_mean = $property->wdsRiskStateMeans->mean;
	$dev = $property->wdsRiskDev;
	$wds_risk_geocode_level = $property->wdsRisk->wds_geocode_level;
	$wds_risk_date = date('Y-m-d', strtotime($property->wdsRisk->date_created));
	$wds_risk_match_address = $property->wdsRisk->match_address;
}

?>


<h3>WDSrisk</h3>

<div class="fluidField" style="width:200px;">
    <p><b>Date</b></p>
    <p><?php echo $wds_risk_date; ?></p>
</div>

<div class="fluidField" style="width:200px;">
    <p><b>Property Deviation</b></p>
    <p><?php echo RiskScore::getRiskConcern($state_mean, $state_std_dev, $wds_risk_score); ?> (<?php echo RiskScore::getStandardDevText($state_mean, $state_std_dev, $wds_risk_score); ?>)</p>
</div>

<div class="fluidField" style="width:200px;">
    <p><b>Property Risk Score</b></p>
    <p><?php echo $wds_risk_score; ?></p>
</div>

<div class="fluidField" style="width:200px;">
    <p><b>Wildfire Hazard Potential</b></p>
    <p><?php echo $wds_risk_whp; ?></p>
</div>

<div class="fluidField" style="width:200px;">
    <p><b>Vulnerability</b></p>
    <p><?php echo $wds_risk_v; ?></p>
</div>

<div class="fluidField" style="width:200px;">
    <p><b>Geocode Level</b></p>
    <p><?php echo $wds_risk_geocode_level; ?></p>
</div>

<div class="fluidField" style="width:200px;">
    <p><b>Match Address</b></p>
    <p><?php echo $wds_risk_match_address; ?></p>
</div>



