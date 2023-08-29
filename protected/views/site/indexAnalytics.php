<?php

$this->pageTitle = Yii::app()->name;

Yii::app()->clientScript->registerCss(1, '
    .message-board {
        border: 1px solid black;
        padding: 20px;
        background-color: lightyellow;
        font-size: 1.1em;
        border-radius: 4px;
        box-shadow: 3px 3px 5px 1px #cccccc;
        box-sizing: border-box;
        margin-top: 20px;
    }
    
');
?>

<h1>Welcome, <?php echo Yii::app()->user->getState('fullname'); ?>!</h1>
<p>WDS Admin - Internal Software for WDS Employees</p>

<div class="row-fluid" style="margin-bottom: 40px;">
    <div class="span6">
        <div class="message-board">
            <p class="lead">Announcements Board</p>
            <hr style="border-color: powderblue;" />
            <?php echo Yii::app()->systemSettings->announcements; ?>
        </div>
    </div>
    <div class="span6">
        <div class="message-board">
            <p class="lead">Support Board</p>
            <hr style="border-color: powderblue;" />
            <?php echo Yii::app()->systemSettings->support; ?>
        </div>
    </div>
</div>
<div>

<h1 class ="center">Department Summary</h1>

<div class ="row-fluid" style="margin:0px 0px 25px;" >
    <div class="span4">
     <div class="row-fluid">
<div class="span12 table-responsive" style="border: 1px solid #cccccc;height: 500px; overflow-y: auto;">
        <h2 class="center" style="background-color:#222; color:white;">Clients</h2>
        <table class="table">
            <tr>
                <td><strong>Client Name</strong></td>
                <td><strong>WDSfire</strong></td>
                <td><strong>WDSpro</strong></td>
                <td><strong>WDSrisk</strong></td>
                <td><strong>WDSedu</strong></td>
            </tr>

            <?php foreach($clients as $client): ?>
            <tr>
                <?php if ($client->wds_fire == 1): ?>
                <td> <?php echo CHtml::link($client->name, $this->createUrl('/resNotice/viewNotice', array('clientid'=>$client->id)), array('target'=>'_blank')); ?></td>
                <?php else: ?>
                <td><?php echo $client->name; ?></td>
                <?php endif; ?>
                <td><?php echo ($client->wds_fire) ? "&#x2713;" : "&#x2716;"; ?></td>
                <td><?php echo ($client->wds_pro) ? "&#x2713;" : "&#x2716;"; ?></td>
                <td><?php echo ($client->wds_risk) ? "&#x2713;" : "&#x2716;"; ?></td>
                <td><?php echo ($client->wds_education) ? "&#x2713;" : "&#x2716;"; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Only show the button to view more client stats if a manager or admin -->
        <?php if(in_array("Admin", Yii::app()->user->types) || in_array("Manager", Yii::app()->user->types)): ?>

        <a style="display:inline-block; margin:10px;" class="btn btn-success btn-large" href="<?php echo $this->createUrl('/client/index'); ?>">View More</a>

        <?php endif; ?>
</div>
    </div>
    </div>
   

    <div class="span4">
     <div class="row-fluid">
<div class="span12 table-responsive" style="border: 1px solid #cccccc;height: 500px; overflow-y: auto;">
        <h2 class="center" style="background-color:#222; color:white;">Response</h2>
        <table class="table">
            <tr>
                <td><strong>Timeframe</strong></td>
                <td><strong>Dispatched</strong></td>
                <td><strong>Not-Dispatched</strong></td>
                <td><strong>Total Fires</strong></td>
                <td><strong>Total Notifications</strong></td>
            </tr>
            <tr>
                <td>YTD</td>
                <td><?php echo ResNotice::countProgramFires(date('Y'), null, 1); ?></td>
                <td><?php echo ResNotice::countProgramFires(date('Y'), null, null) - ResNotice::countProgramFires(date('Y'), null, 1); ?></td>
                <td> <?php echo ResFireName::countFiresByDate(date('Y')); ?></td>
                <td><?php echo ResNotice::countNotices(date('Y')); ?></td>
            </tr>
            <tr>
                <td>PTD (2013)</td>
                <td><?php echo ResNotice::countProgramFires(null, null, 1); ?></td>
                <td><?php echo ResNotice::countProgramFires(null, null, null) - ResNotice::countProgramFires(null, null, 1); ?></td>
                <td> <?php echo ResFireName::countFiresByDate(); ?></td>
                <td><?php echo ResNotice::countNotices(); ?></td>
            </tr>
        </table>
        <?php if ($fireData): ?>
        <h3 class="center">
            <?php echo count($fireData); ?> Dispatched Fires:
        </h3>
        <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th><strong>Fire</strong></th>
                <th><strong>Location</strong></th>
                <th><strong>Clients</strong></th>
                <th><strong>Enrolled Policyholders</strong></th>
            </tr>
            </thead>
            <?php foreach ($fireData as $data):?>
            
            <tr>
                <td><?= ($data['fire_name']);?></td>
                <td><?= ($data['state']);?></td>
                <td><?= implode('<br />', explode(',', $data['client_names']));?></td>
                <td><?= $data['triggered_enrolled'];?></td>
                <?php endforeach;?>
            </tr>
        </table>
        </div>
        <?php else: ?>

        <p class="lead center">
            <i>WDS is currently not responding to any fires</i>
        </p>

        <?php endif; ?>

        <a style="display:inline-block; margin:10px;" class="btn btn-success btn-large" href="<?php echo $this->createUrl('/resNotice/index'); ?>">View More</a>
   

    </div>
    </div>
    </div>
    <div class="span4">
     <div class="row-fluid">
<div class="span12 table-responsive" style="border: 1px solid #cccccc;height: 500px; overflow-y: auto;">
        <h2 class="center"style="background-color:#222; color:white;">Pre Risk</h2>
        <table class="table">
            <tr>
                <td><strong>Timeframe</strong></td>
                <td><p><strong><u>Fire Shield Reports</u></strong></p><strong>&#35; Completed Reports</strong></td>
            </tr>
            <tr>
                <td>MTD</td> 
                <td> <?php echo FSReport::countReports(date('Y-m') . '-01', date('Y-m', strtotime('+1 months', strtotime(date('Y-m-d')))) . '-01' ); ?></td> 
            </tr>
            <tr>
                <td>YTD</td>  
                <td><?php echo FSReport::countReports(date('Y'), date('Y', strtotime('+1 years', strtotime(date('Y'))))); ?></td>
            </tr>
            <tr>
                <td>PTD</td>  
                <td><?php echo FSReport::countReports(); ?></td>
            </tr>
        </table>

        <table class="table">
            <tr>
                <td><strong>Timeframe</strong></td>
                <td><p><strong><u>WDSPro Reports</u></strong></p><strong>&#35; Completed Reports</strong></td>
            </tr>
             <tr>
                <td>MTD</td> 
                <td> <?php echo FSReport::countWDSProReports(date('Y-m') . '-01', date('Y-m', strtotime('+1 months', strtotime(date('Y-m-d')))) . '-01' ); ?></td> 
            </tr>
            <tr>
                <td>YTD</td>  
                <td><?php echo FSReport::countWDSProReports(date('Y'), date('Y', strtotime('+1 years', strtotime(date('Y'))))); ?></td>
            </tr>
            <tr>
                <td>PTD</td>  
                <td><?php echo FSReport::countWDSProReports(); ?></td>
            </tr>
        </table>

        <a style="display:inline-block; margin:10px;" class="btn btn-success btn-large" href="<?php echo $this->createUrl('/fsReport/index'); ?>">View More</a>
        

    </div>
    </div>
    </div>
    
    
    
    
    

</div>
<!-- row-fluid -->
<div style="clear:both"></div>
<div class="row-fluid">

    <?php

    $activeEngineStats = EngScheduling::reportingSiteCountActiveEngines();
    $currentFleetStats = EngScheduling::reportingSiteCurrentFleetStatistics();

    ?>

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
            <tbody>
                <tr>
                    <td></td>
                    <td>Response</td>
                    <td>Dedicated</td>
                    <td>PreRisk</td>
                    <td>On Hold</td>
                    <td>Totals</td>
                </tr>
                <tr>
                    <td>WDS</td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS][EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_WDS]['Total']; ?></td>
                </tr>
                <tr>
                    <td>Alliance</td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE][EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_ALLIANCE]['Total']; ?></td>
                </tr>
                <tr>
                    <td>Rental</td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL][EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; ?></td>
                    <td><?php echo $activeEngineStats[EngEngines::ENGINE_SOURCE_RENTAL]['Total']; ?></td>
                </tr>
                <tr>
                    <td></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_RESPONSE]; }, $activeEngineStats));  ?></strong></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_DEDICATED]; }, $activeEngineStats));  ?></strong></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_PRERISK]; }, $activeEngineStats));  ?></strong></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data[EngScheduling::ENGINE_ASSIGNMENT_ONHOLD]; }, $activeEngineStats));  ?></strong></td>
                    <td><strong><?php echo array_sum(array_map(function($data) { return $data['Total']; }, $activeEngineStats));  ?></strong></td>
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

    <div class="span4">
    <div class="row-fluid">
<div class="span12 table-responsive" style="border: 1px solid #cccccc;height: 500px; overflow-y: auto;">

        <h2 class="center" style="background-color:#222; color:white;">Dedicated Service</h2>
        <table class="table">
            <tr>
                <td><strong>Client Name</strong></td>
                <td><strong>Month to Day</strong></td>
                <td><strong>Contract to Day</strong></td>
                <td><strong>Year to Day</strong></td>
            </tr>
            <?php
            $clients = Client::model()->findAll('dedicated = 1 AND active = 1 AND wds_fire = 1');
            foreach($clients as $client)
            {
                echo '<tr>';
                echo '<td>' . $client->name . '</td>';
                echo '<td>' . round(ResDedicated::reportingSiteCountDaysMTD($client->id), 2) . '</td>';
                echo '<td>' . round(ResDedicated::reportingSiteCountDaysYTD($client->id, true), 2) . '</td>';
                echo '<td>' . round(ResDedicated::reportingSiteCountDaysYTD($client->id, false), 2) . '</td>';
                echo '</tr>';
            }
            ?>
            <tr>
                <td></td>
                <td colspan="2"><h4>Dedicated service day is 8 hours.</h4></td>
            </tr>
        </table>
        <a style="display:inline-block; margin:10px;" class="btn btn-large btn-success" href="<?php echo $this->createUrl('/resDedicated/index') ?>" >View More</a>
    </div></div></div>

</div>


