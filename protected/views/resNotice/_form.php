<?php
/* @var $this ResNoticeController */
/* @var $model ResNotice */
/* @var $form CActiveForm */

Assets::registerMapboxPackage();

Yii::app()->clientScript->registerScriptFile('/js/resNotice/admin.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCss(1,'
    .map-fixed {
        position: fixed;
        right: 40px;
        top: 40px;
        width: 600px;
        height: 400px;
        z-index: -10;
        opacity: 0;
        -webkit-box-shadow: -5px 5px 10px #666;
           -moz-box-shadow: -5px 5px 10px #666;
                box-shadow: -5px 5px 10px #666;
    }
    .close-icon {
        position: absolute;
        z-index: 11;
        left: -15px;
        top: -15px;
        width: 20px;
        height: 20px;
        border-width: 3px;
        border-radius: 100%;
        background: -webkit-linear-gradient(-45deg, transparent 0%, transparent 46%, white 46%,  white 56%,transparent 56%, transparent 100%), -webkit-linear-gradient(45deg, transparent 0%, transparent 46%, white 46%,  white 56%,transparent 56%, transparent 100%);
        background:    -moz-linear-gradient(-45deg, transparent 0%, transparent 46%, white 46%,  white 56%,transparent 56%, transparent 100%), -moz-linear-gradient(45deg, transparent 0%, transparent 46%, white 46%,  white 56%,transparent 56%, transparent 100%);
        background-color: gray;
        box-sizing: border-box;
        box-shadow: 0px 0px 5px 2px rgba(0,0,0,0.5);
        transition: all 0.3s ease;
    }
    #map {
        width: 100%;
        height: 100%;
    }
');
$sql = "SELECT 
f.Fire_ID Fire_ID,
f.Name Name
FROM res_monitor_log m
INNER JOIN res_fire_obs o ON o.Obs_ID = m.Obs_ID
LEFT JOIN res_fire_name f ON f.Fire_ID = o.Fire_ID
WHERE Smoke_Check_Date BETWEEN dateadd(day, datediff(day, 0 ,getdate())-30, 0) and getdate()
ORDER BY f.Name ASC
";
$list = (Yii::app()->db->createCommand($sql))->queryAll();

$firelist = CHtml::listData($list,'Fire_ID','Name');

?>

<div class="map-fixed">
    <a href="#" class="close-icon"></a>
    <?php $this->renderPartial('_form_map', array('model'=>$model)); ?>
</div>

<div class="form">

    <?php   
    $form=$this->beginWidget('CActiveForm', array(
	'id' => 'res-notice-form',
    'enableAjaxValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'afterValidate' => new CJavaScriptExpression('function(form, data, hasError) {
            if (!hasError) 
            {
                var perimeter_id = $("#ResNotice_perimeter_id").val();
                var url = "'.$this->createUrl("getThreatPerimeter").'";
                var wdsStatus = $("input[type=radio]:checked").val();
                var checkPerimeter = checkThreatPerimeter(url, perimeter_id, wdsStatus);
                if(checkPerimeter)
                {
                    var message = confirm("There is no threat attached. Do you still want to save this notice?");
                    if (message) 
                    {
                        return true;
                    }
                    else 
                    {
                        return false;
                    }
                }
                form.submit(function(e) 
                {
                    $("#button-row").empty();
                    $("#button-row").html(
                        \'<div style="width: 300px; margin: 5px 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">\' +
                            \'<div class="bar" style="width: 100%; margin:0;"></div>\' +
                        \'</div>\');
                        return true;
                });
                return true;
            }
        }')
    )
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">

        <div class="row-fluid">
            <div class="span12">

                <!-- Fire Information TbBox -->

                <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
                    'title' => 'Fire Information',
                    'headerIcon' => TbHtml::ICON_EDIT,
                    'htmlOptions' => array('class' => 'bootstrap-widget-table','style' => 'margin-top:20px; margin-bottom:0;')
                )); ?>

                <div style="padding: 10px;">
                    <?php if ($model->isNewRecord): ?>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="form-section">
                                <a href="javascript:void(0);" class="map-show">Show map</a>
                            </div>
                        </div>
                    </div>

                    <div class="row-fluid marginBottom20">
                        <div class="span3">
                            <?php echo CHtml::label('Select Fire - ', CHtml::activeId($model, 'fire_id')); ?>
                            <?php echo $form->dropDownList($model,'fire_id', $firelist, array('empty' => '( Select a fire )',
                                'ajax' => array(
                                    'type' => 'POST',
                                    'url' => $this->createUrl('getFireDetails'),
                                    'update' => '#'.CHtml::activeId($model, 'obs_id')
                                )
                            )); ?>
                            <?php echo $form->error($model, 'fire_id'); ?>
                        </div>

                        <div class="span3">
                            <?php echo CHtml::label('Fire Details - ', CHtml::activeId($model, 'obs_id')); ?>
                            <?php echo $form->dropDownList($model,'obs_id',array()); ?>
                            <?php echo $form->error($model,'obs_id'); ?>
                        </div>

                        <div class="span3">
                            <?php echo CHtml::label('Perimeter - ', CHtml::activeId($model, 'perimeter_id')); ?>
                            <?php echo $form->dropDownList($model,'perimeter_id',array()); ?>
                            <?php echo $form->error($model,'perimeter_id'); ?>
                        </div>
                    </div>

                    <?php else: ?>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="form-section">
                                <a href="javascript:void(0);" class="map-show">Show map</a>
                            </div>
                        </div>
                    </div>

                    <div class="row-fluid">
                        <div class="span3">
                            <?php echo CHtml::label('Select Fire - ', CHtml::activeId($model, 'fire_id')); ?>
                            <?php echo $form->dropDownList($model,'fire_id', $firelist, array('empty'=>'( Select a fire )',
                                'ajax' => array(
                                    'type' => 'POST',
                                    'url' => $this->createUrl('getFireDetails'),
                                    'update' => '#' . CHtml::activeId($model, 'obs_id')
                            ))); ?>
                            <?php echo $form->error($model, 'fire_id'); ?>
                        </div>

                        <div class="span3">
                            <?php echo CHtml::label('Fire Details - ', CHtml::activeId($model, 'obs_id')); ?>
                            <?php echo $form->dropDownList($model,'obs_id',$this->updateGetFireDetails($fire_id)); ?>
                            <?php echo $form->error($model,'obs_id'); ?>
                        </div>

                        <div class="span3">
                            <?php echo CHtml::label('Perimeter - ', CHtml::activeId($model, 'perimeter_id')); ?>
                            <?php echo $form->dropDownList($model,'perimeter_id',$this->updateGetFirePerimeters($fire_id)); ?>
                            <?php echo $form->error($model,'perimeter_id'); ?>
                        </div>
                    </div>

                    <div class="row-fluid">
                        <div class="span12">
                            <div class="form-section">
                                <a class="btn btn-danger" href="<?php echo $this->createUrl('resFireObs/update', array('id'=>$model->obs_id,'fireid'=>$model->fire_id,'source'=>'notice')); ?>">
                                    <i class="icon-pencil"></i> Edit Fire Details
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>
                </div>

                <?php $this->endWidget(); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span6">
                <div class="form-section">

                    <!-- Notice Information -->

                    <?php if ($client_id != 999): ?>
                    <h2>Notice Information</h2>

                    <?php echo $form->labelEx($model,'wds_status'); ?>
                    <div class="compactRadioGroup" id="wds-status-radio-list">
                        <?php echo $form->radioButtonList($model,'wds_status',$model->getWdsStatus(),array('separator' => "<br />")); ?>
                        <?php echo $form->error($model,'wds_status'); ?>
                    </div>
                    

                    <?php echo $form->labelEx($model,'recommended_action'); ?>
                    <div class="compactRadioGroup">
                        <?php echo $form->radioButtonList($model,'recommended_action',$model->getRecommendedActions(),array('separator' => "<br />")); ?>
                        <?php echo $form->error($model,'recommended_action'); ?>
                    </div>

                    <?php else: ?>

                    <?php echo $form->hiddenField($model,'wds_status',array('value'=>'2')); ?>

                    <?php endif; ?>

                    <!-- Impacted Regions -->

                    <div class="form-section">
                        <h2>Impacted Regions</h2>
		                <?php echo $form->labelEx($model,'evacuations'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'evacuations',$model->getEvacuations()); ?>
                        </div>
		                <?php echo $form->error($model,'evacuations'); ?>

                        <?php if ($client_id != 999): ?>

		                <?php echo $form->labelEx($model,'evac_effecting_policy'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'evac_effecting_policy',$model->getEvacAffectingPolicy()); ?>
                        </div>
		                <?php echo $form->error($model,'evac_effecting_policy'); ?>

                        <?php endif; ?>

		                <?php echo $form->labelEx($model,'homes_lost'); ?>
		                <?php echo $form->textField($model,'homes_lost',array('size'=>60,'maxlength'=>125)); ?>
		                <?php echo $form->error($model,'homes_lost'); ?>
                    </div>

                </div>
            </div>
            <div class="span6">
                <div class="form-section">

                    <!-- Comments -->

                    <div class="form-section">
                        <h2>Comments</h2>
		                <?php echo $form->labelEx($model,'comments'); ?>
                        <?php echo $form->textArea($model,'comments',array('maxlength'=>2000, 'style'=>'width:90%;min-height:100px;')); ?>
		                <?php echo $form->error($model,'comments'); ?>
                    
		                <?php echo $form->labelEx($model,'notes'); ?>
                        <?php echo $form->textArea($model,'notes',array('maxlength'=>2000,  'style'=>'width:90%;height:auto;min-height:100px;')); ?>
		                <?php echo $form->error($model,'notes'); ?>
                    </div>

                    <?php if (!$model->isNewRecord): ?>

                    <!-- Triage Zones -->

                    <div class="form-section">
                        <h2>Triage Zones</h2>
                        <?php

                        if ($model->resTriageZone)
                        {
                            if ($model->resTriageZone->resTriageZoneAreas)
                            {
                                echo '<p class="lead" style="display: inline-block;">This notice has ' . count($model->resTriageZone->resTriageZoneAreas) . ' triage zones.</p>';
                                echo '<a href="' . $this->createUrl('/resTriageZone/update', array('id' => $model->resTriageZone->id)) . '" target="_blank" class="marginLeft10">Edit Here</a>';
                            }
                            else
                            {
                                echo '<p class="lead" style="display: inline-block;">This notice has a triage entry with no associated zones.</p>';
                                echo '<a href="' . $this->createUrl('/resTriageZone/update', array('id' => $model->resTriageZone->id)) . '" target="_blank" class="marginLeft10">Edit Here</a>';
                            }
                        }
                        else
                        {
                            echo '<h4 style="display: inline-block;">There are NO triage zones</h4>';
                            echo '<a href="' . $this->createUrl('/resTriageZone/create') . '" target="_blank" class="marginLeft10">Create</a>';
                        }

                        ?>
                    </div>

                    <!-- Evac Zones -->
                    <div class="form-section">
                        <h2>Evac Zones</h2>
                        <?php
                            if ($model->resEvacZones)
                            {
                                echo '<p class="lead" style="display: inline-block;">This notice has ' . count($model->resEvacZones) . ' evac zones.</p>';
                                echo '<a href="' . $this->createUrl('/resEvacZone/update', array('notice_id' => $model->notice_id)) . '" target="_blank" class="marginLeft10">Edit Here</a>';
                            }
                            else
                            {
                                echo '<h4 style="display: inline-block;">There are NO evac zones</h4>';
                                echo '<a href="' . $this->createUrl('/resEvacZone/create', array('notice_id' => $model->notice_id)) . '" target="_blank" class="marginLeft10">Create</a>';
                            }
                        ?>
                    </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <div class="form-section">
                    <?php echo $form->hiddenField($model,'client_id',array('value'=>$client_id));  ?>

                    <h2>Save Notice</h2>
            
                    <?php if (!$model->isNewRecord): ?>

		            <?php echo CHtml::label("Run GIS Analysis", "runAnalysis"); ?>
		            <?php echo CHtml::checkBox("runAnalysis", 0); ?>

                    <?php endif; ?>

		            <?php echo $form->labelEx($model,'publish'); ?>
		            <?php echo $form->checkBox($model,'publish'); ?>
		            <?php echo $form->error($model,'publish'); ?>

                    <div id="button-row" class="buttons">
		                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit','style'=>'background:#298DCD')); ?>
                        <span class="paddingLeft10">
                            <?php echo CHtml::link('Cancel', array('resNotice/admin')); ?>
                        </span>
	                </div>

                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->