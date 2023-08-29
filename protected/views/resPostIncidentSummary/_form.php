<?php
/* @var $this ResPostIncidentSummaryController */
/* @var $model ResPostIncidentSummary */
/* @var $form CActiveForm */
?>

<div class="form">

    <?php
    $ajaxCheckDemobUrl = Yii::app()->createUrl('resNotice/isClientFireDemob');

    $form=$this->beginWidget('CActiveForm', array(
	    'id'=>'res-post-incident-summary-form',
    ));

    ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <h2 class ="center">Basic Information</h2>
        </div>
        <div class="row-fluid" style="padding-bottom:25px;">
            <div class="span4">

	            <div>
		            <?php echo $form->labelEx($model,'fire_id'); ?>
		            <?php echo $form->dropDownList($model,'fire_id', ResPostIncidentSummary::getDispatchedFires($client), array('style'=>'width:200px;')); ?>
		            <?php echo $form->error($model,'fire_id'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'personel'); ?>
		            <?php echo $form->textField($model,'personel',array('size'=>60,'maxlength'=>200)); ?>
		            <?php echo $form->error($model,'personel'); ?>
	            </div>

            </div>
            <div class ="span4">
                <div>
                    <?php echo $form->labelEx($model,'date_access_gained'); ?>
                    <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'date_access_gained',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd'
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',
                                    'maxlength' => '10'
                            )
                    )); ?>
                    <?php echo $form->error($model,'date_access_gained'); ?>
                </div>

                <div>
		            <?php echo $form->labelEx($model,'access_gained_comment'); ?>
		            <?php echo $form->textField($model,'access_gained_comment',array('size'=>100,'maxlength'=>500)); ?>
		            <?php echo $form->error($model,'access_gained_comment'); ?>
	            </div>

            </div>

            <div class ="span4">
                <div>
                    <?php echo $form->labelEx($model,'date_access_denied'); ?>
                    <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'date_access_denied',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd'
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',
                                    'maxlength' => '10'
                            )
                    )); ?>
                    <?php echo $form->error($model,'date_access_denied'); ?>
                </div>

                <div>
		            <?php echo $form->labelEx($model,'access_denied_comment'); ?>
		            <?php echo $form->textField($model,'access_denied_comment',array('size'=>100,'maxlength'=>500)); ?>
		            <?php echo $form->error($model,'access_denied_comment'); ?>
	            </div>

            </div>

        </div>

         <div class="row-fluid">
            <h2 class ="center">Narratives</h2>
        </div>

        <div class="row-fluid">
            <div class ="span12">
                <div>
		            <?php echo $form->labelEx($model,'wds_actions'); ?>
                    <?php echo $form->textArea($model,'wds_actions',array('maxlength'=>3000, 'style'=>'width:90%;min-height:100px;')); ?>
		            <?php echo $form->error($model,'wds_actions'); ?>
	            </div>

                <div>
		            <?php echo $form->labelEx($model,'fire_summary'); ?>
                    <?php echo $form->textArea($model,'fire_summary',array('maxlength'=>3000, 'style'=>'width:90%;min-height:100px;')); ?>
		            <?php echo $form->error($model,'fire_summary'); ?>
	            </div>

                <div>
                    <?php
                    if(ResNotice::isClientFireDemob($model->fire_id, $client))
                    {
                        echo $form->labelEx($model,'published', array('id'=>'res-notice-published-label'));
		                echo $form->checkBox($model,'published');
		                echo $form->error($model,'published');
                    }
                    else
                    {
                        echo $form->labelEx($model,'published', array('id'=>'res-notice-published-label')).' NOTE: Client Fire must be Demobilized to publish report.';
                        echo $form->checkBox($model,'published', array('style'=>'display:none;'));
                        echo $form->error($model,'published');
                    }
                    ?>
	            </div>
            </div>

        </div>
        <div class="row-fluid">
            <div class="span12">
	            <?php echo $form->hiddenField($model,'client_id', array('value'=>$client)); ?>
                <div class="buttons" style="padding-top:10px;">
                    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('admin')); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <?php

    $this->endWidget();

    Yii::app()->clientScript->registerScript('checkdemob', "
        $('#ResPostIncidentSummary_fire_id').change( function( event ) {
            $.post('".$this->createUrl('resNotice/isClientFireDemob')."',
                {
                    clientID: ".$client.",
                    fireID: $('#ResPostIncidentSummary_fire_id').val()
                },
                function(data, status) {
                    var result = $.parseJSON(data);
                    if(!result.isDemob)
                    {
                        $('#ResPostIncidentSummary_published').attr('checked', false);
                        $('#ResPostIncidentSummary_published').hide();
                        $('#res-notice-published-label').after(' NOTE: Client Fire must be Demobilized to publish report.');
                    }
                }
            );
        })
    ");

    ?>

</div><!-- form -->