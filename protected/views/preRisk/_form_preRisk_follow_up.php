<?php
/* @var $this PreRiskController */
/* @var $model PreRisk */
/* @var $form CActiveForm */
// This is used for the follow up campaign, and is selected via the 'follow up' button in the admin view
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
            <div class="span4">
                <div class="form-section">
                    <h1>Follow Up Attempts</h1>

                    <div>
		                <?php echo $form->labelEx($model,'follow_up_attempt_1'); ?>
		                <?php echo $form->dropDownList($model,'follow_up_attempt_1',$model->wdsCallers($model->follow_up_attempt_1)); ?>
		                <?php echo $form->error($model,'follow_up_attempt_1'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'follow_up_time_date_1'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'follow_up_time_date_1',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'follow_up_time_date_1'); ?>
                    </div>

                    <div>
		                <?php echo $form->labelEx($model,'follow_up_attempt_2'); ?>
		                <?php echo $form->dropDownList($model,'follow_up_attempt_2',$model->wdsCallers($model->follow_up_attempt_2)); ?>
		                <?php echo $form->error($model,'follow_up_attempt_2'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'follow_up_time_date_2'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'follow_up_time_date_2',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'follow_up_time_date_2'); ?>
                    </div>
            
                     <div>
                        <?php echo $form->labelEx($model,'follow_up_status'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_status',$model->wdsFollowUpStatuses()); ?>
                        <?php echo $form->error($model,'follow_up_status'); ?>
                    </div>
            
                    <div>
                        <?php echo $form->labelEx($model,'call_center_comments'); ?>
                        <?php echo $form->textArea($model, 'call_center_comments', array('rows'=>'15', 'cols'=>'35')) ?>
                        <?php echo $form->error($model,'call_center_comments'); ?>
                    </div>

                </div>
            </div>
            <div class="span4">
                <div class="form-section">
                    <h1>Follow Up Questions</h1>
                    
                    <div>
                        <?php echo $form->labelEx($model,'point_of_contact'); ?>
                        <?php echo $form->dropDownList($model,'point_of_contact',$model->pointOfContact()); ?>
                        <?php echo $form->error($model,'point_of_contact'); ?>
                    </div>
                    
                   <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_1'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_answer_1',$model->scaleRating()); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_1'); ?>
                    </div>
                    
                     <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_2'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_answer_2',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_2'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_3'); ?>
                        <?php echo $form->textField($model,'follow_up_2_answer_3',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_3'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_4'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_answer_4',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_4'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_5'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_answer_5',$model->actionsNotComplete()); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_5'); ?>
                    </div>
                    
                     <div>
                        <p><strong>6. Action Taken On The Following Conditions:</strong></p>
                    </div>
                    
                     <div>
                        <?php echo $form->labelEx($model,'follow_up_2_question_6a'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_question_6a',$model->conditions()); ?>  <?php echo $form->dropDownList($model,'follow_up_2_6a_response',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_question_6a'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_question_6b'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_question_6b',$model->conditions()); ?>  <?php echo $form->dropDownList($model,'follow_up_2_6b_response',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_question_6b'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_question_6c'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_question_6c',$model->conditions()); ?>  <?php echo $form->dropDownList($model,'follow_up_2_6c_response',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_question_6c'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_question_6d'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_question_6d',$model->conditions()); ?>  <?php echo $form->dropDownList($model,'follow_up_2_6d_response',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_question_6d'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_question_6e'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_question_6e',$model->conditions()); ?>  <?php echo $form->dropDownList($model,'follow_up_2_6e_response',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_question_6e'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_question_6f'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_question_6f',$model->conditions()); ?>  <?php echo $form->dropDownList($model,'follow_up_2_6f_response',$model->yesNoNotSure()); ?>
                        <?php echo $form->error($model,'follow_up_2_question_6f'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_7'); ?>
                        <?php echo $form->dropDownList($model,'follow_up_2_answer_7',$model->scaleRatingPrepare()); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_7'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'follow_up_2_answer_8'); ?>
                        <?php echo $form->textField($model,'follow_up_2_answer_8',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'follow_up_2_answer_8'); ?>
                    </div>

                </div>
            </div>
            <div class="span4">
                <div class="form-section">

                    <h1>HA Information</h1>

                    <p><strong>Recommended Actions:</strong></p>
                    <p><?php echo $model->recommended_actions; ?></p>
                    <p><strong>HA Date and Time:</strong></p>
                    <p><?php echo $model->ha_date; ?> <?php echo $model->ha_time; ?></p>
                     <p><strong>Field Assessor:</strong></p>
                    <p><?php echo $model->ha_field_assessor; ?></p>
                    <div class="buttons">
                        <?php echo CHtml::submitButton('Save & Close', array('class'=>'submit')); ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->