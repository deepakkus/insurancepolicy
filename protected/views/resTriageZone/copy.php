<?php
/* @var $this ResTriageZoneController */
/* @var $model ResTriageZone */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Work Zone Copy'
);

?>

<h1>Copy Work Zones</h1>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <p class="lead"><u>Current Data</u></p>
                <?php $this->widget('zii.widgets.CDetailView', array(
                    'data' => $model,
                    'htmlOptions' => array(
                        'class' => 'table table-striped table-hover table-condensed',
                        'style' => 'width: 70%;'
                    ),
                    'itemTemplate' => '<tr><th style="width:1%; white-space: nowrap;">{label}:</th><td>{value}</td></tr>',
                    'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
                    'attributes' => array(
                        'clientName',
                        'fireName',
                        array(
                            'label' => 'Notice',
                            'value' => $model->notice->recommended_action . " - " . date("Y-m-d H:i", strtotime($model->notice->date_created))
                        ),
                        array(
                            'label' => 'Number Zones',
                            'value' => $model->resTriageZoneAreas ? count($model->resTriageZoneAreas) : ''
                        )  
                    )
                )); ?>
            </div>
            <div class="span6">

                <?php $form = $this->beginWidget('CActiveForm', array(
	                'id' => 'res-triage-zone-copy-form',
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit' => true
                     )
                )); ?>

                <?php echo $form->errorSummary($model); ?>

                <div>
                    <?php echo CHtml::label('Which notice would you like to copy this to?', CHtml::activeId($model, 'notice_id')); ?>
                    <?php echo CHtml::activeDropDownList($model, 'notice_id', ResTriageZone::getDispatchedNotices($model->clientID, $model->fireID), array(
                        'prompt' => 'Select a notice',
                        'style' => 'width: 400px;'
                    )); ?>
		            <?php echo $form->error($model, 'notice_id'); ?>
	            </div>

	            <div class="marginTop20">
		            <?php echo CHtml::submitButton('Copy', array('class'=>'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('/resTriageZone/admin')); ?>
                    </span>
	            </div>

                <?php $this->endWidget(); ?>

            </div>
        </div>
    </div>

