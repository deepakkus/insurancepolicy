<?php
/* @var $this ResFireNameController */
/* @var $model ResFireName */
/* @var $form CActiveForm */

?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'res-fire-name-form',
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <div class ="form-section">

                    <div>
		                <?php echo $form->labelEx($model,'Name'); ?>
		                <?php echo $form->textField($model,'Name',array('size'=>200,'maxlength'=>200,'style'=>'width:260px;')); ?>
		                <?php echo $form->error($model,'Name'); ?>
	                </div>

                    <div>
		                <?php echo $form->labelEx($model,'Alternate_Name'); ?>
		                <?php echo $form->textField($model,'Alternate_Name',array('size'=>100,'maxlength'=>100,'style'=>'width:260px;')); ?>
		                <?php echo $form->error($model,'Alternate_Name'); ?>
	                </div>

                    <div>
	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'City'); ?>
		                    <?php echo $form->textField($model,'City',array('size'=>50,'maxlength'=>50,'style'=>'width:150px;')); ?>
		                    <?php echo $form->error($model,'City'); ?>
	                    </div>

                        <div class="clearfix">
		                    <?php echo $form->labelEx($model,'State'); ?>
                            <?php echo $form->dropDownList($model,'State', Helper::getStates(), array('style'=>'width:100px;','prompt'=>'')); ?>
		                    <?php echo $form->error($model,'State'); ?>
                        </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Start_Date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'Start_Date',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd'
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',
                                    'maxlength' => '10',
                                    'readonly' => 'true',
                                    'style' => 'cursor:pointer'
                            )
                        )); ?>
                        <?php echo $form->error($model,'Start_Date'); ?>
                    </div>

                    <div>
	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'Coord_Lat'); ?>
		                    <?php echo $form->textField($model,'Coord_Lat',array('size'=>8,'maxlength'=>8,'style'=>'width:140px;', 'value'=>($model->isNewRecord && isset(Yii::app()->session['centroidLat'])) ? Yii::app()->session['centroidLat'] : $model->Coord_Lat)); ?>
		                    <?php echo $form->error($model,'Coord_Lat'); ?>
	                    </div>

	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'Coord_Long'); ?>
		                    <?php echo $form->textField($model,'Coord_Long',array('size'=>10,'maxlength'=>10,'style'=>'width:140px;')); ?>
		                    <?php echo $form->error($model,'Coord_Long'); ?>
	                    </div>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'Timezone'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'Timezone', array(
                                'America/Los_Angeles' => 'Pacific',
                                'America/Denver' => 'Mountain',
                                'America/Phoenix' => 'Arizona',
                                'America/Chicago' => 'Central',
                                'America/New_York' => 'Eastern',
                                'America/Anchorage' => 'Alaska',
                                'Pacific/Honolulu' => 'Hawaii'
                            ),
                            array(
                                'separator' => " &nbsp; ",
                                'onclick'=>'$("#' . CHtml::activeId($model, 'Timezone') . '").val(this.value)'
                            )); ?>
                            <?php echo $form->error($model,'Timezone'); ?>
                        </div>
                    </div>

	                <div>
		                <?php echo $form->labelEx($model,'res_fuel_type'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->checkBoxList($model,'res_fuel_type',array('Timber'=>'Timber','Brush'=>'Brush','Grass'=>'Grass','Slash'=>'Slash','Pine Timber Litter' => 'Pine Timber Litter', 'Freshwater Marsh'=> 'Freshwater Marsh','Unknown'=>'Unknown')); ?>
                        </div>
		                <?php echo $form->error($model,'res_fuel_type'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'Contained'); ?>
                        <?php echo $form->checkBox($model,'Contained',array(
                            'onclick'=>'if(this.checked) { $("#'.CHtml::activeId($model, 'Contained_Date').'").prop("disabled", false); } 
                                                    else { $("#'.CHtml::activeId($model, 'Contained_Date').'").prop("disabled", true); }'
                        )); ?>
		                <?php echo $form->error($model,'wds_saved'); ?>
		                <?php echo $form->error($model,'Contained'); ?>
	                </div>

                    <div>
		                <?php echo $form->labelEx($model,'Contained_Date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'Contained_Date',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd'
                            ),
                            'htmlOptions' => array(
                                'size' => '10',
                                'maxlength' => '10',
                                'disabled' => ($model->Contained === '1') ? false : true,
                                'readonly' => 'true',
                                'style' => 'cursor:pointer'
                            )
                        )); ?>
		                <?php echo $form->error($model,'Contained_Date'); ?>
	                </div>

	                <div class="buttons" id="button-row">
		                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                        <span class="paddingLeft10">
                            <?php echo CHtml::link('Cancel', array('resFireName/admin')); ?>
                        </span>
	                </div>

                </div>
            </div>
            <div class="span6">
                <div class ="form-section">

                    <h3>Optional Details</h3>
                    <p>(Used in Post Incident Summary)</p>
                    <div>
		                <?php echo $form->labelEx($model,'Cause'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->dropDownList($model,'Cause',array('Unknown'=>'Unknown', 'Lightning'=>'Lightning','Arson'=>'Arson','Human Caused'=>'Human Caused','Perscribed Burn'=>'Perscribed Burn','Structure Fire'=>'Structure Fire')); ?>
                        </div>
		                <?php echo $form->error($model,'Cause'); ?>
	                </div>

                    <div>
                        <?php echo $form->labelEx($model,'Estimated_Containment_Date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model' => $model,
                                'attribute' => 'Estimated_Containment_Date',
                                'options' => array(
                                    'dateFormat' => 'yy-mm-dd'
                                ),
                                'htmlOptions' => array(
                                    'size' => '10',
                                    'maxlength' => '10'
                                )
                        )); ?>
                        <?php echo $form->error($model,'Estimated_Containment_Date'); ?>
                    </div>

                    <div>
		                <?php echo $form->labelEx($model,'Location_Description'); ?>
		                <?php echo $form->textField($model,'Location_Description',array('size'=>60,'maxlength'=>75,'style'=>'width:260px;')); ?>
		                <?php echo $form->error($model,'Location_Description'); ?>
	                </div>

                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<script>
    // Add progress bar when submitted
    $('#res-fire-name-form').submit(function () {
        var submitButton = this.querySelector('input[type="submit"]');
        $('#button-row').empty();
        $('#button-row').html(
            '<div style="width: 300px; margin: 5px 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">' +
                '<div class="bar" style="width: 100%;margin:0;"></div>' +
            '</div>');
    });
</script>