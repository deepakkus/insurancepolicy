<?php
/* @var $this PreRiskController */
/* @var $model PreRisk */
/* @var $form CActiveForm */
//Used for fire review to log in the status of reports. This is accessed via the 'production' button in the admin view
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'pre-risk-form'
)); ?>

<?php 
            
echo $form->errorSummary($model);
$this->renderPartial('_memberInfoHeader', array('model'=>$model));
        
?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <div class="form-section">
                    <h1>Assessment Information</h1>
                    <div>
                        <?php echo $form->labelEx($model,'ha_field_assessor'); ?>
                        <?php echo $form->dropDownList($model,'ha_field_assessor',$model->wdsFieldAssessors($model->ha_field_assessor)); ?>
                        <?php echo $form->error($model,'ha_field_assessor'); ?>
                    </div>
            
                    <div>
                        <?php echo $form->labelEx($model,'fire_review'); ?>
                        <?php echo $form->dropDownList($model,'fire_review',$model->wdsFireReviewers($model->fire_review)); ?>
                        <?php echo $form->error($model,'fire_review'); ?>
                    </div>
            
                    <div>
                        <?php echo $form->labelEx($model,'wds_ha_writers'); ?>
                        <?php echo $form->dropDownList($model,'wds_ha_writers',$model->wdsHAWriters($model->wds_ha_writers)); ?>
                        <?php echo $form->error($model,'wds_ha_writers'); ?>
                    </div>
            
                    <div>
                        <?php echo $form->labelEx($model,'completion_date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'completion_date',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'completion_date'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'cycle_time_in_days'); ?>
                        <?php echo $form->textField($model,'cycle_time_in_days',array('size'=>60,'maxlength'=>200)); ?>
                        <?php echo $form->error($model,'cycle_time_in_days'); ?>
                    </div>
            
                    <div>
                        <?php echo $form->labelEx($model,'status'); ?>
                        <?php echo $form->dropDownList($model,'status',$model->wdsStatuses()); ?>
                        <?php echo $form->error($model,'status'); ?>
                    </div>
                </div>
            </div>
            <div class="span6">
                <div class="form-section">

				<div>
                    <div class="row-fluid">
					    <?php
					    $rec_actions = array_keys($model->getRecActions());
					    $counter = 0;
					    foreach($rec_actions as $rec_action)
					    {
						    $counter++;
						    if($counter == 9)
							    echo '</div><div class="span6">';
						    elseif($counter == 1)
							    echo '<div class="span6">';
						    echo $form->labelEx($model,$rec_action);
						    echo $form->checkBox($model,$rec_action);
						    echo $form->error($model,$rec_action);
						    echo '<br />';
					    }
					    echo '</div>';
                        ?>
                    </div>
				</div>

				<div>
					<?php echo $form->labelEx($model,'recommended_actions'); ?>
					<?php echo $form->textArea($model, 'recommended_actions', array('rows'=>'10', 'cols'=>'35', 'disabled'=>'disabled')) ?>
					<?php echo $form->error($model,'recommended_actions'); ?>
				</div>

				<div>
					<?php echo $form->labelEx($model,'delivery_date'); ?>
					<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'options' => array(
                            'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                        ),
                        'attribute' => 'delivery_date',
                        'htmlOptions' => array(
                                'size' => '10',         // textField size
                                'maxlength' => '10',    // textField maxlength
                        )
                    )); ?>
					<?php echo $form->error($model,'delivery_date'); ?>
				 </div>

                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <div class ="form-section">
				    <div class="buttons">
					    <?php echo CHtml::submitButton('Save & Close', array('class'=>'submit')); ?>
				    </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->