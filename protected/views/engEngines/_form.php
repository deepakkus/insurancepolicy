<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'eng-engines-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span4">
                <div class ="form-section">
	                <div>
		                <?php echo $form->labelEx($model,'engine_name'); ?>
		                <?php echo $form->textField($model,'engine_name',array('size'=>20,'maxlength'=>20)); ?>
		                <?php echo $form->error($model,'engine_name'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($model,'make'); ?>
		                <?php echo $form->textField($model,'make',array('size'=>50,'maxlength'=>50)); ?>
		                <?php echo $form->error($model,'make'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($model,'model'); ?>
		                <?php echo $form->textField($model,'model',array('size'=>50,'maxlength'=>50)); ?>
		                <?php echo $form->error($model,'model'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($model,'vin'); ?>
		                <?php echo $form->textField($model,'vin',array('size'=>30,'maxlength'=>30)); ?>
		                <?php echo $form->error($model,'vin'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($model,'plates'); ?>
		                <?php echo $form->textField($model,'plates',array('size'=>20,'maxlength'=>20)); ?>
		                <?php echo $form->error($model,'plates'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($model,'type'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'type', $model->getEngineTypes()); ?>
                        </div>
                        <?php echo $form->error($model,'type'); ?>
	                </div>
	                <div>
		                <?php echo $form->labelEx($model,'active'); ?>
		                <?php echo $form->checkBox($model,'active'); ?>
		                <?php echo $form->error($model,'active'); ?>
	                </div>
                </div>
            </div>
            <div class="span8">
                <div class ="form-section">
	                <div>
		                <?php echo $form->labelEx($model,'comment'); ?>
		                <?php echo $form->textArea($model,'comment',array('maxlength'=>200, 'rows' => 6, 'cols' => 50, 'style'=>'width:auto;')); ?>
		                <?php echo $form->error($model,'comment'); ?>
	                </div>
                    <div>
                        <?php echo $form->labelEx($model,'engine_source'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'engine_source', $model->getEngineSources(), array('prompt'=>'', 'separator' => ' &nbsp; ')); ?>
		                    <?php echo $form->error($model,'engine_source'); ?>
                        </div>
                    </div>
                    <div>
		                <?php echo $form->labelEx($model,'alliance_id'); ?>
		                <?php echo $form->dropDownList($model,'alliance_id',$model->getAlliancePartners(),array('empty'=>' ','disabled' => ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE) ? false : true)); ?>
		                <?php echo $form->error($model,'alliance_id'); ?>
                    </div>
                    <br />
                    <h3><u>Alliance Use Only</u></h3>
	                <div>
		                <?php echo $form->labelEx($model,'availible'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'availible', array('1' => 'Availible', '2' => 'On WDS Assignment', '0' => 'Not Availible'), array('prompt'=>'', 'separator' => ' &nbsp; ')); ?><?php echo $form->error($model,'availible'); ?>
                        </div>
                    </div>
	                <div>
		                <?php echo $form->labelEx($model,'reason'); ?>
		                <?php echo $form->textArea($model,'reason',array('maxlength'=>200, 'rows' => 6, 'cols' => 50, 'style'=>'width:auto;', 'disabled' => ($model->engine_source == EngEngines::ENGINE_SOURCE_ALLIANCE) ? false : true)); ?>
		                <?php echo $form->error($model,'reason'); ?>
	                </div>
                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <div class ="form-section">
	                <div class="buttons">
		                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                        <span class="paddingLeft10">
                            <?php echo CHtml::link('Cancel', array('admin')); ?>
                        </span>
	                </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div>

<!-- JS that only allows editing of alliance information when 'Alliance' is selected from the 'Engine Source' radio options -->

<?php Yii::app()->clientScript->registerScript('alliancePartnersJS', ' 

// Javascript form interaction when the user checks "Alliance" in the "Engine Sources" radio selection
$("#' . CHtml::activeId($model, 'engine_source') .'").click(function() {
    var checkedValue = $("#' . CHtml::activeId($model, 'engine_source') . ' input[type=radio]:checked").val();
    if (checkedValue == '. EngEngines::ENGINE_SOURCE_ALLIANCE .') {
        $("#' . CHtml::activeId($model, 'alliance_id') . '").prop("disabled", false);
        $("#' . CHtml::activeId($model, 'availible') . '").prop("disabled", false);
        $("#' . CHtml::activeId($model, 'reason') . '").prop("disabled", false);
    }
    else {
        $("#' . CHtml::activeId($model, 'alliance_id') . '").prop("disabled", true);
        $("#' . CHtml::activeId($model, 'alliance_id') . '").prop("selectedIndex", 0);
        $("#' . CHtml::activeId($model, 'availible') . '").prop("disabled", true);
        $("#' . CHtml::activeId($model, 'reason') . '").prop("disabled", true);
    }
});

// Enabling all forms on submit so php captures the values of the forms.
$("#eng-engines-form").submit(function() {
    $("#' . CHtml::activeId($model, 'alliance_id') . '").prop("disabled", false);
    $("#' . CHtml::activeId($model, 'availible') . '").prop("disabled", false);
    $("#' . CHtml::activeId($model, 'reason') . '").prop("disabled", false);
});

', CClientScript::POS_READY); ?>