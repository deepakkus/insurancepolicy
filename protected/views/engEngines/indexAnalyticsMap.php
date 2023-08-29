<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engine Analytics' => array('indexAnalytics'),
    'Engines Map'
);

Yii::app()->clientScript->registerCssFile('/css/engEngines/indexAnalyticsMap.css');
Yii::app()->clientScript->registerCss('indexenginecss', '
   .span4 {
        width: 91%;
    }
    .top_tbl{
     border-bottom: 1px solid #ccc;
     border-right: 1px solid #ccc;
    }
    .top_tbl td:nth-child(2){
     border-left:1px solid #ccc;
    }
    .top_tbl td:nth-child(4){
    border-left:1px solid #ccc;
    }
    .top_tbl td:nth-child(6){
    border-left:1px solid #ccc;
    }
    .top_tbl td:nth-child(8){
    border-left:1px solid #ccc;
    }
');
$activeEngineStats = EngScheduling::reportingSiteCountActiveEngines();
$currentFleetStats = EngScheduling::reportingSiteCurrentFleetStatistics();

?>
<div class="container">
<div class="span4">
    <div class="row-fluid">
<div class="span12 table-responsive" style="border: 1px solid #cccccc;height: 500px; overflow-y: auto;">

        <h2 class="center" style="background-color:#222; color:white;">Engines</h2>
        <!-- Engines Working Now -->
        <table class="table">
            <thead>
                <tr>
                    <th colspan="6"><span style="font-size:15px;">Working Now (<?php echo date('H:i'); ?>)</span></th>
                </tr>
            </thead>
            <tbody class="top_tbl">
                <tr>
                    <td></td>
                    <td>Response</td>
                    <td><b> % </b></td>
                    <td>Dedicated</td>
                    <td><b> %</b></td>
                    <td>PreRisk</td>
                    <td>On Hold</td>
                    <td>Totals</td>
                    <td><b> %</b></td>
                </tr>
                <tr>
                    <td>WDS</td>
                   <?php 
                    $AllianceResponse = $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE];
                    $WDSResponse = $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE];
                    $WDSDedicated = $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED];
                    $sumResponse = array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; }, $activeEngineStats));
                    $sumDedicated = array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; }, $activeEngineStats));
                    if($WDSResponse != 0)
                    {
                        $WDSResponsePercentage = ($WDSResponse / $sumResponse) * 100;
                    }
                    else
                    {
                        $WDSResponsePercentage = 0;
                    }
                    if($WDSDedicated != 0)
                    {
                        $WDSDedicatedPercentage = ($WDSDedicated / $sumDedicated) * 100;
                    }
                    else
                    {
                        $WDSDedicatedPercentage = 0;
                    }
                    $totalWDS = $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS]['Total'];
                    $sumTotal = array_sum(array_map(function($data) { return $data['Total']; }, $activeEngineStats));
                    if($totalWDS != 0)
                    {
                        $totalWDSPercentage = ($totalWDS / $sumTotal) * 100;
                    }
                    else
                    {
                        $totalWDSPercentage = 0;
                    }

                    if($AllianceResponse != 0)
                    {
                        $AllianceResponsePercentage = ($AllianceResponse / $sumResponse) * 100;
                    }
                    else
                    {
                        $AllianceResponsePercentage = 0;
                    }
                    $AllianceDedicated = $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED];
                    if($AllianceDedicated != 0)
                    {
                        $AllianceDedicatedPercentage = ($AllianceDedicated / $sumDedicated) * 100;
                    }
                    else
                    {
                        $AllianceDedicatedPercentage = 0;
                    }
                    $totalAlliance = $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE]['Total'];
                    if($totalAlliance != 0)
                    {
                        $totalAlliancePercentage = ($totalAlliance / $sumTotal) * 100;
                    }
                    else
                    {
                        $totalAlliancePercentage = 0;
                    }
                    $Rentalresponse = $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; 
                    if($Rentalresponse != 0)
                    {
                        $RentalResponsePercentage = ($Rentalresponse / $sumResponse) * 100;
                    }
                    else
                    {
                        $RentalResponsePercentage = 0;
                    }
                    $RentalDedicated = $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; 
                    if($RentalDedicated != 0)
                    {
                        $RentalDedicatedPercentage = ($RentalDedicated / $sumDedicated) * 100;
                    }
                    else
                    {
                        $RentalDedicatedPercentage = 0;
                    }
                    $totalRental = $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL]['Total'];
                    if($totalRental != 0)
                    {
                        $totalRentalPercentage = ($totalRental / $sumTotal) * 100;
                    }
                    else
                    {
                        $totalRentalPercentage = 0;
                    }
                    ?>
                    <td><?php echo ($WDSResponse < $AllianceResponse ? '<span style="color:#FF0000;text-align:center;"><b>'.$WDSResponse : $WDSResponse.'</b></span>'); ?></td>
                    <td><?php echo '<b>'.round($WDSResponsePercentage,2) .'%'.'</b>'; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; ?></td>
                    <td><?php echo '<b>'.round($WDSDedicatedPercentage,2).'%'.'</b>'; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS]['Total']; ?></td>
                    <td><?php echo '<b>'.round($totalWDSPercentage,2).'%'.'</b>'; ?></td>
                </tr>
                <tr>
                    <td>Alliance</td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; ?></td>
                    <td><?php echo '<b>'.round($AllianceResponsePercentage,2).'%'.'</b>';  ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; ?></td>
                    <td><?php echo '<b>'.round($AllianceDedicatedPercentage,2).'%'.'</b>';  ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE]['Total']; ?></td>
                    <td><?php echo '<b>'.round($totalAlliancePercentage,2).'%'.'</b>'; ?></td>
                </tr>
                <tr>
                    <td>Rental</td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; ?></td>
                    <td><?php echo '<b>'.round($RentalResponsePercentage,2).'%'.'</b>';  ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; ?></td>
                    <td><?php echo '<b>'.round($RentalDedicatedPercentage,2).'%'.'</b>';  ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL]['Total']; ?></td>
                    <td><?php echo '<b>'.round($totalRentalPercentage,2).'%'.'</b>';  ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; }, $activeEngineStats));  ?></strong></td>
                    <td></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; }, $activeEngineStats));  ?></strong></td>
                    <td></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; }, $activeEngineStats));  ?></strong></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; }, $activeEngineStats));  ?></strong></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data['Total']; }, $activeEngineStats));  ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <!-- Current Fleet Statistics -->
        <table class="table">
            <thead>
                <tr>
                    <th colspan="6"><span style="font-size:15px;">Current Fleet Status (<?php echo date('H:i'); ?>)</span></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td>WDS</td>
                    <td>Alliance</td>
                    <td>Rental</td>
                    <td>Total</td>
                </tr>
                <tr>
                    <td>Working</td>
                    <td><?php echo $currentFleetStats['working_now'][EngEngines::ENGINE_SOURCE_WDS]; ?></td>
                    <td><?php echo $currentFleetStats['working_now'][EngEngines::ENGINE_SOURCE_ALLIANCE]; ?></td>
                    <td><?php echo $currentFleetStats['working_now'][EngEngines::ENGINE_SOURCE_RENTAL]; ?></td>
                    <td><?php echo array_sum(array_values($currentFleetStats['working_now'])); ?></td>
                </tr>
                <tr>
                    <td>Not Working</td>
                    <td><?php echo $currentFleetStats['not_active'][EngEngines::ENGINE_SOURCE_WDS] + $currentFleetStats['not_scheduled'][EngEngines::ENGINE_SOURCE_WDS]; ?></td>
                    <td><?php echo $currentFleetStats['not_active'][EngEngines::ENGINE_SOURCE_ALLIANCE] + $currentFleetStats['not_scheduled'][EngEngines::ENGINE_SOURCE_ALLIANCE]; ?></td>
                    <td><?php echo $currentFleetStats['not_active'][EngEngines::ENGINE_SOURCE_RENTAL] + $currentFleetStats['not_scheduled'][EngEngines::ENGINE_SOURCE_RENTAL]; ?></td>
                    <td><?php echo array_sum(array_values($currentFleetStats['not_active'])) + array_sum(array_values($currentFleetStats['not_scheduled'])); ?></td>
                </tr>
                <tr>
                    <td>Total Engines in Fleet</td>
                    <td><strong><?php echo $currentFleetStats['total_engines_fleet'][EngEngines::ENGINE_SOURCE_WDS]; ?></strong></td>
                    <td><strong><?php echo $currentFleetStats['total_engines_fleet'][EngEngines::ENGINE_SOURCE_ALLIANCE]; ?></strong></td>
                    <td><strong><?php echo $currentFleetStats['total_engines_fleet'][EngEngines::ENGINE_SOURCE_RENTAL]; ?></strong></td>
                    <td><strong><?php echo array_sum(array_values($currentFleetStats['total_engines_fleet'])); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <a class="btn btn-large btn-success" href="<?php echo $this->createUrl('/engEngines/indexAnalytics') ?>" style="display:inline-block; margin:10px 5px 10px 10px;">View More</a>
        <a class="btn btn-large btn-info" href="<?php echo $this->createUrl('/engEngines/indexAnalyticsMap') ?>" style="display:inline-block; margin:10px 10px 10px 5px;">Today's Engines</a>

    </div></div></div>
    
    </div>
    <br>
<?php
if (!$print)
{
    Assets::registerMapboxPackage();
    Assets::registerLegendToggleControl();
    Assets::registerMapboxMarkerCluster();

    echo CHtml::link('Print View',$this->createUrl($this->route,array('print'=>true)),array('class'=>'btn btn-primary marginBottom10','target'=>'_blank'));
}

?>

<?php if (!$print): ?>
<style>
    .custom-wrapper {
      background: #ccc;
      overflow: hidden;
      transition: height 700ms;
      height: 155px;
    }

    .open{
        height: 155px !important;
    }
</style>
<h1>Engines Map</h1>
            

<div class="row">

	<div id="map-wrapper">
		<div id="map">
            <select id="button" style="
                            float: right; 
                            width: 178px;
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            z-index: 10;"><option>Select</option></select>
			<form id="client-filters" class="custom-wrapper" style="max-height: 155px !important; min-height: 28px !important; overflow-y:scroll; margin-top: 27px !important; display: none; width: 178px !important;">    
            </form>
		</div>

	</div>

</div>

<div class="engines-legend hidden">
    <div class="engines-legend-title">Map Legend</div>
    <div class="engines-legend-scale">
        <ul class="engines-legend-labels">
            <li><span style='background-image: url("images/mapboxmarkers/marker-dedicated.png");'></span>Dedicated Service</li>
            <li><span style='background-image: url("images/mapboxmarkers/marker-prerisk.png");'></span>Pre Risk</li>
            <li><span style='background-image: url("images/mapboxmarkers/marker-response.png");'></span>Response</li>
            <li><span style='background-image: url("images/mapboxmarkers/marker-onhold.png");'></span>On Hold</li>
            <li><span style='background-image: url("images/mapboxmarkers/marker-staged.png");'></span>Staged</li>
            <li><span style='background-image: url("images/mapboxmarkers/marker-outofservice.png");'></span>Out of Service</li>
            <li><span style='background-image: url("images/mapboxmarkers/marker-instorage.png");'></span>In Storage</li>
        </ul>
    </div>
</div>

<?php else: ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Engine Schedule | Print View</title>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo Yii::app()->request->baseUrl; ?>/images/logo.png">
    </head>
    <body>

<?php endif; ?>

<div class="engines-wrapper">

    <!-- Scheduled Engines - Active -->

    <?php

    $models_active_response =  array_filter($models_active, function($v) { return $v->assignment === 'Response';          });
    $models_active_dedicated = array_filter($models_active, function($v) { return $v->assignment === 'Dedicated Service'; });
    $models_active_onhold =    array_filter($models_active, function($v) { return $v->assignment === 'On Hold';           });
    $models_active_prerisk =   array_filter($models_active, function($v) { return $v->assignment === 'Pre Risk';          });

    ?>

    <div class="table-wrapper container">
        <h2 style="background-color:#222; color:white;"><i>Working - <?php echo date('M d, Y - H:i'); ?></i></h2>
		<p class="center"><i>Note: This schedule operates in real time. Engines will not appear in this section if the current time falls out of the ending time for their assignment. Instead they will show as not working.</i></p>
        <div class="table-responsive">
            <?php if (count($models_active_response)): ?>
            <table class="table table-condensed">
                <caption><h3 class="text-left"><u>Response</u></h3></caption>
                <tbody>
                    <?php

                    $count = 0;
                    $firename = '';
                    $different = false;
                    foreach ($models_active_response as $index => $model)
                    {
                        if ($model->fire_name !== $firename)
                        {
                            echo '<tr><td style="padding-left: 60px; border-top: 0;"><h4>' . $model->fire_name  . '</h4></td></tr>';
                            $firename = $model->fire_name;
                            $different = true;
                            $count = 0;
                        }

                        $count++;
                        $tableRow = '<tr>
                            <td style="padding-left: 120px;">
                                <b>' . $model->engine_name . (!empty($model->client_names) ? ' (' . join(' / ',$model->client_names) . ')' : '') . '</b>
                            </td>
                            <td>
                                <a href="#" class="engine-map-link" data-id="' . $model->id . '">Map</a>
                            </td>
                            <td>' .
                                implode('<br />', array_map(function($employee) { return $employee->crew_first_name . ' ' . $employee->crew_last_name; }, $model->employees)) .'
                            </td>
                            <td>
                                <b>' . $model->assignment . '</b><br />' . $model->city . ', ' .$model->state . '
                            </td>
                            <td>' .
                                ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE ? 
                                    '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b> (<i>' . $model->engine->alliance_partner . '</i>)<br />' . $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : '') : 
                                    '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b><br />' . $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : '')
                                ) . '
                            </td>';

                        if (!isset($models_active_response[$index + 1]) || $models_active_response[$index + 1]->fire_name !== $firename)
                        {
                            $tableRow .= '<td style="width: 100px;"><h5 class="text-right">' . $count . ' ' . ($count > 1 ? 'Engines' : 'Engine') . '</h5></td></tr>';
                        }
                        else 
                        {
                            $tableRow .= '<td style="width: 100px;"></td></tr>';
                        }

                        echo $tableRow;
                    }
                    
                    ?>
                </tbody>
            </table>

            <table class="table table-condensed">
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="width: 250px;">
                            <h5 class="text-right">Response: <?php echo count($models_active_response) . ' ' . (count($models_active_response) > 1 ? 'Engines' : 'Engine'); ?></h5>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php endif; ?>

            <?php

            // Adding Dedicated, On Hold, and Pre Risk tables

            $engineModels = array(
                EngScheduling::ENGINE_ASSIGNMENT_DEDICATED => $models_active_dedicated,
                EngScheduling::ENGINE_ASSIGNMENT_ONHOLD    => $models_active_onhold,
                EngScheduling::ENGINE_ASSIGNMENT_PRERISK   => $models_active_prerisk
            );

            foreach ($engineModels as $assignment => $models)
            {
                if (count($models))
                {
                    $tableContent = '
                    <table class="table table-condensed">
                        <caption><h3 class="text-left"><u>' . $assignment . '</u></h3></caption>
                        <thead>
                            <tr>
                                <th>Engine Name</th>
                                <th>Map Location</th>
                                <th>Crew</th>
                                <th>Task / Location</th>
                                <th>Reason</th>
                                <th>Engine Source / Assignment</th>
                            </tr>
                        </thead>
                        <tbody>';

                    $count = 0;
                    foreach ($models as $index => $model)
                    {
                        $count++;
                        $tableContent .= '<tr>
                            <td>
                                <b>' . $model->engine_name . (!empty($model->client_names) ? ' (' . join(' / ',$model->client_names) . ')' : '') . '</b>
                            </td>
                            <td>
                                <a href="#" class="engine-map-link" data-id="' . $model->id . '">Map</a>
                            </td>
                            <td>' .
                                implode('<br />', array_map(function($employee) { return $employee->crew_first_name . ' ' . $employee->crew_last_name; }, $model->employees)) .'
                            </td>
                            <td>
                                <b>' . $model->assignment . '</b><br />' . $model->city . ', ' .$model->state . '
                            </td>
                            <td>
                                ' . $model->comment . '
                            </td>
                            <td>' .
                                ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE ?
                                    '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b> (<i>' . $model->engine->alliance_partner . '</i>)<br />' . $model->assignment . ($model->fire_id ?' (<i>' . $model->fire_name. '</i>)' : '') :
                                    '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b><br />' . $model->assignment . ($model->fire_id ? ' (<i>' . $model->fire_name. '</i>)' : '')
                                ) . '
                            </td>';

                        if (!isset($models[$index + 1]))
                        {
                            $tableContent .= '<td style="width: 100px;"><h5 class="text-right">' . $count . ' ' . ($count > 1 ? 'Engines' : 'Engine') . '</h5></td></tr>';
                        }
                        else
                        {
                            $tableContent .= '<td style="width: 100px;"></td></tr>';
                        }
                    }

                    $tableContent .= '</tbody></table>';

                    echo $tableContent;
                }
            }

            echo '<table class="table table-condensed">
                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="width: 200px;"><h5 class="text-right">Total: ' . count($models_active) . ' ' . (count($models_active) > 1 ? 'Engines' : 'Engine') . '</h5></td>
                    </tr>
                </tbody>
            </table>
            ';
            
            ?>

        </div>
    </div>

    <hr style="margin: 30px;" />

    <div class="table-wrapper container">
        <h2 style="background-color:#222; color:white;"><i>Not Working - <?php echo date('M d, Y - H:i'); ?></i></h2>
		<h3>WDS</h3>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Engine Name</th>
                        <th>Map Location</th>
                        <th>Crew</th>
                        <th>Task / Location</th>
                        <th>Reason</th>
                        <th>Engine Source / Assignment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if (count($models_notactive))
                    {
                        $count = 0;
                        foreach ($models_notactive as $index => $model)
                        {
                            $count++;
                            $tableRow = '<tr>
                                <td>
                                    <b>' . $model->engine_name . (!empty($model->client_names) ? ' (' . join(' / ',$model->client_names) . ')' : '') . '</b>
                                </td>
                                <td>
                                    <a href="#" class="engine-map-link" data-id="' . $model->id . '">Map</a>
                                </td>
                                <td>' .
                                    implode('<br />', array_map(function($employee) { return $employee->crew_first_name . ' ' . $employee->crew_last_name; }, $model->employees)) . '
                                </td>
                                <td>' .
                                    $model->assignment . '</b><br />' . $model->city . ', ' . $model->state . '
                                </td>
                                <td>
                                    ' . $model->comment . '
                                </td>
                                <td>' .

                                    ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE ?
                                        '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b> (<i>' . $model->engine->alliance_partner . '</i>)<br />' . $model->assignment . ($model->fire_id ? ' (<i>' . $model->fire_name . '</i>)' : '') :
                                        '<b>' . $model->engine->getEngineSource($model->engine_source)  . '</b><br />' . $model->assignment . ($model->fire_id ? ' (<i>' . $model->fire_name . '</i>)' : '')
                                    ) . '
                                </td>';

                            if (!isset($models_notactive[$index + 1]))
                            {
                                $tableRow .= '<td style="width: 100px;"><h5>' . $count . ' ' . ($count > 1 ? 'Engines' : 'Engine') . '</h5></td></tr>';
                            }
                            else
                            {
                                $tableRow .= '<td style="width: 100px;"></td></tr>';
                            }

                            echo $tableRow;
                        }
                    }
                    
					?>
                </tbody>
            </table>
        </div>
		<h3>Alliance</h3>
        <div class="table-responsive">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Engine Name</th>
                        <th>Available</th>
                        <th>Reason</th>
                        <th>Engine Source</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    $count = 0;
                    foreach ($unused_engines as $index => $engine)
                    {
                        $count ++;
                        $tableRow = '<tr>
                            <td>
                                <b>' . $engine->engine_name . '</b>
                            </td>
                            <td>' .
                                Yii::app()->format->formatBoolean($engine->availible). '
                            </td>
                            <td>' .
                                $engine->reason . '
                            </td>
                            <td>' .
                                $engine->getEngineSource($engine->engine_source) . '
                            </td>';

                        if (!isset($unused_engines[$index + 1]))
                        {
                            $tableRow .= '<td style="width: 100px;"><h5>' . $count . ' ' . ($count > 1 ? 'Engines' : 'Engine') . '</h5></td></tr>';
                        }
                        else
                        {
                            $tableRow .= '<td style="width: 100px;"></td></tr>';
                        }

                        echo $tableRow;
                    }

                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php if ($print): ?>

    </body>
</html>

<?php else: ?>

<script type="text/javascript">

    (function($) {
        $.fn.scrollTo = function(callback) {
            $('body,html').animate({
                scrollTop: $(this).offset().top + 'px'
            }, {
                duration: 200,
                complete: function() {
                    setTimeout(function() { typeof callback === 'function' && callback(); }, 200);
                }
            });
            return this;
        }
    })(jQuery);   
            
    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;

    var map = new L.mapbox.Map('map').setView([40, -108], 5);

    map.removeControl(map.legendControl);
    map.removeControl(map.attributionControl);

    var esriTileOptions = {
        detectRetina: true,
        reuseTiles: true,
        subdomains: ['server', 'services']
    };

    new L.LayerGroup([
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', esriTileOptions),
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', esriTileOptions)
    ]).addTo(map);

    var fullscreen = new L.Control.Fullscreen();
    //var layerControl = new L.Control.Layers(baseLayers, null, { position: 'topright', collapsed: false });
    var legend = new L.mapbox.legendControl().addLegend(document.getElementsByClassName('engines-legend')[0].innerHTML);
    var legendToggle = new L.Control.LegendToggle(legend);

    map.addControl(fullscreen);
    map.addControl(legendToggle);
    map.addControl(legend);

    var markerClusters = new L.MarkerClusterGroup({
        showCoverageOnHover: true,
        zoomToBoundsOnClick: false,
        spiderfyOnMaxZoom: true
    });

    var clientFilters = document.getElementById('client-filters');

    var enginesLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('engEngines/enginesFeatureCollection', array('startDate'=>$startDate)); ?>').on('ready', function() {
        var filteredClients = [];
        var filteredClientsObject = {};
        this.eachLayer(function(marker) {
            // Deciding which clients need to be filtered by
            Object.keys(marker.feature.properties.clients).forEach(function(clientID) {
                if (filteredClients.indexOf(clientID) === -1) {
                    filteredClients.push(clientID)
                    filteredClientsObject[marker.feature.properties.clients[clientID]] = clientID;
                }
            });
            // Adding markers to layer
            marker.bindPopup(marker.feature.properties.popup);
            markerClusters.addLayer(marker);
        });
        // Adding markers to map
        map.addLayer(markerClusters);
        // Adding filters to map
        if (filteredClients.length) {
            Object.keys(filteredClientsObject).sort(function(a, b) {
                if (a < b)      { return -1; }
                else if (a > b) { return 1;  }
                else            { return 0;  }
            }).forEach(function(clientName) {
                clientFilters.innerHTML += '<div class="sh">' +
                    '<label class="checkbox sh"><input class="sh" value="' + filteredClientsObject[clientName] + '" type="checkbox" onclick="filterClusterMarkers();" checked>' + clientName + '</label>' +
                '</div>';
            });
            clientFilters.style.display = 'none';
        }
    });

    markerClusters.on('clusterclick', function(e) {
        e.layer.spiderfy();
    });

    function openMarker(id) {
        markerClusters.eachLayer(function(marker) {
            if (marker.feature.properties.id === id) {
                var cluster = markerClusters.getVisibleParent(marker);
                if (cluster instanceof L.MarkerCluster) { cluster.spiderfy(); }
                if (!map.getBounds().contains(marker.getLatLng())) {
                    map.panTo(marker.getLatLng());
                }
                marker.openPopup();
            }
        });
    }

    function filterClusterMarkers() {
        // Get a list of clients allowed in map
        var filters = clientFilters.getElementsByTagName('input');
        var list = [];
        for (var i = 0; i < filters.length; i++) {
            if (filters[i].checked) {
                list.push(filters[i].value);
            }
        }
        // Clear old layers
        markerClusters.clearLayers();
        enginesLayer.eachLayer(function(marker) {
            // See if clients in marker exist in allowed filter
            var intersectionArray = Object.keys(marker.feature.properties.clients).filter(function(n) {
                return list.indexOf(n) !== -1;
            });
            // Add if allowed
            if (intersectionArray.length) {
                marker.bindPopup(marker.feature.properties.popup);
                markerClusters.addLayer(marker);
            }
        });
    }

    var engineLinks = document.getElementsByClassName('engine-map-link');
    for (var i = 0; i < engineLinks.length; i++) {
        engineLinks[i].addEventListener('click', function(event) {
            event.preventDefault();
            $('#map').scrollTo(function() {
                openMarker(event.target.getAttribute('data-id'));
            });
        });
    }

    $(function() {
      var b = $("#button");
      var w = $(".custom-wrapper");
      var l = $("#client-filters");
  
      w.height(l.outerHeight(true));

      b.click(function() {
  
        if(w.hasClass('open')) {
          w.removeClass('open');
          w.height(0);
          l.hide();
        } else {
          l.show();
          w.addClass('open');
          w.height(l.outerHeight(true));
        }
  
      });
    });

    $('#button').on('mousedown', function(e) {
       e.preventDefault();
       this.blur();
       window.focus();
    });

    $("body").click(function(e){
        if((e.target.id !== "client-filters")&&(e.target.id !== "button")&&(e.target.className !== "sh")&&(!e.target.parentElement.classList.contains('sh')))
        {
          $(".custom-wrapper").removeClass('open');
          $(".custom-wrapper").height(0);
          $("#client-filters").hide();
        }
    });
</script>

<?php endif; ?>