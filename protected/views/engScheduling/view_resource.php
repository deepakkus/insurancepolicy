<?php

/* @var $this EngSchedulingController */
/* @var $model EngScheduling */

Yii::app()->clientScript->registerCssFile('/css/engScheduling/view_resource.css');

?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title>Resource Order</title>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>

        <?php if (!$print) echo CHtml::link('Download PDF',$this->createUrl('/engScheduling/resourceOrder',array('id'=>$model->id,'print'=>true)),array('target'=>'_blank')); ?>

        <div id="resourceOrderWrapper">
                
            <table style="margin-bottom: 20px;">
                <tr>
                    <td class="border-none" style="text-align: left;">Incident: <?php echo $model->assignment . ($model->fire_id ? " (<i>$model->fire_name</i>)" : '') ?></td>
                    <td class="border-none" style="text-align: right;">Resource Order: <?php echo /*date('Y', strtotime($model->resourceOrder->date_created)) . '-' . */$model->resource_order_num; ?></td>
                </tr>
                <tr>
                    <td class="border-none text-center paddingTop20" colspan="2">
                        <?php foreach ($model->engineClient as $engineClient): ?>
                        <?php echo $engineClient->client->name . '<br />'; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            
            <table>
                <tr class="headers">
                    <td style="width: 25%;">Assignment Name and Location</td>
                    <td style="width: 20%;">Order Time</td>
                    <td style="width: 20%;">Estimated Incident Arrival Time</td>
                    <td style="width: 35%;">Ordered By</td>
                </tr>
                <tr class="information">
                    <td><?php echo $model->resourceOrderGetAssignment(); ?></td>
                    <td><?php echo $model->resourceOrderNearestQuarterHour(date('d-m-Y', strtotime($model->resourceOrder->date_ordered))); ?></td>
                    <td><?php echo date('m/d/Y \a\t H:i \M\D\T', strtotime($model->arrival_date)); ?></td>
                    <td> WDS Staff:<br /><?php echo $model->resourceOrder->user_name; ?></td>
                </tr>
            </table>
                
            <table>
                <tr class="headers">
                    <td style="width: 25%;">Company Name and Phone #</td>
                    <td style="width: 20%;">Preseason Agreement #</td>
                    <td style="width: 20%;">Resource Requested</td>
                    <td style="width: 35%;">Engine Boss and Contact Info</td>
                </tr>
                <tr class="information">
                    <td><?php echo $model->resourceOrderGetCompanyInfo(); ?></td>
                    <td><?php echo $model->engine->alliancepartner ? $model->engine->alliancepartner->preseason_agreement : ''; ?></td>
                    <td><?php echo $model->engine->engine_name; ?></td>
                    <td><?php echo $model->resourceOrderGetEngineBoss() ?></td>
                </tr>
            </table>

            <div>
                <span>Instructions</span>
                <ul>
                    <li>Specific Instructions: <?php echo $model->specific_instructions; ?></li>
                    <li>Fire Officer: <?php echo $model->resourceOrderGetFireOfficer(); ?></li>
                    <li>Morning WDS briefing - 1000 hrs MDT and the number is (515)739-1015 (contact supervisor for the code)</li>
                    <li>Insure engine is fully equipped with sprinkler kits, gel, and all electronic equipment.</li>
                    <li>Send all shift tickets daily to eest@wildfire-defense.com</li>
                    <li>Wildfire Defense Systems, Inc. &ndash; (406)586-5400 ext. 1 or (877)-323-4730 ext. 1</li>
                </ul>
            </div>

            <table class="engine-info">
                <tr>
                    <th>Crew Manifest: &nbsp;&nbsp;</th>
                    <td><?php echo implode(' / ', array_map(function($employee) { return "$employee->crew_first_name $employee->crew_last_name"; }, $model->employees)); ?></td>
                </tr>
                <tr>
                    <th>Make: &nbsp;&nbsp;</th>
                    <td style="border: none;"><?php echo $model->engine->make; ?></td>
                </tr>
                <tr>
                    <th>Model: &nbsp;&nbsp;</th>
                    <td><?php echo $model->engine->model; ?></td>
                </tr>
                <tr>
                    <th>VIN: &nbsp;&nbsp;</th>
                    <td><?php echo $model->engine->vin; ?></td>
                </tr>
                <tr>
                    <th>Plate: &nbsp;&nbsp;</th>
                    <td><?php echo $model->engine->plates; ?></td>
                </tr>
            </table>
                
        </div>
    </body>
</html>