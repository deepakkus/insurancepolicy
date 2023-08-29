<?php
/* @var $this PreRiskController */
/* @var $model PreRisk */
/* @var $form CActiveForm */
// This is used to schedule pre risk hazard assessments by the call center. It is accessed via the 'scheduling' button in the admin view
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
            
                    <h1>Call Attempts</h1>

                    <div>
		                <?php echo $form->labelEx($model,'call_attempt_1'); ?>
		                <?php echo $form->dropDownList($model,'call_attempt_1',$model->wdsCallers($model->call_attempt_1)); ?>
		                <?php echo $form->error($model,'call_attempt_1'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_1'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'time_1',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'time_1'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'call_attempt_2'); ?>
                        <?php echo $form->dropDownList($model,'call_attempt_2',$model->wdsCallers($model->call_attempt_2)); ?>
                        <?php echo $form->error($model,'call_attempt_2'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_2'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'time_2',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'time_2'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'call_attempt_3'); ?>
                        <?php echo $form->dropDownList($model,'call_attempt_3',$model->wdsCallers($model->call_attempt_3)); ?>
                        <?php echo $form->error($model,'call_attempt_1'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_3'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model' => $model,
                                'attribute' => 'time_3',
                                'options' => array(
                                    'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                                ),
                                'htmlOptions' => array(
                                        'size' => '10',         // textField size
                                        'maxlength' => '10',    // textField maxlength
                                ),
                        )); ?>
                        <?php echo $form->error($model,'time_3'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'call_attempt_4'); ?>
                        <?php echo $form->dropDownList($model,'call_attempt_4',$model->wdsCallers($model->call_attempt_4)); ?>
                        <?php echo $form->error($model,'call_attempt_4'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_4'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'time_4',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'time_4'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'call_attempt_5'); ?>
                        <?php echo $form->dropDownList($model,'call_attempt_5',$model->wdsCallers($model->call_attempt_5)); ?>
                        <?php echo $form->error($model,'call_attempt_5'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_5'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model' => $model,
                                'attribute' => 'time_5',
                                'options' => array(
                                    'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                                ),
                                'htmlOptions' => array(
                                        'size' => '10',         // textField size
                                        'maxlength' => '10',    // textField maxlength
                                ),
                        )); ?>
                        <?php echo $form->error($model,'time_5'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'call_attempt_6'); ?>
                        <?php echo $form->dropDownList($model,'call_attempt_6',$model->wdsCallers($model->call_attempt_6)); ?>
                        <?php echo $form->error($model,'call_attempt_6'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_6'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'time_6',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'time_6'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'call_attempt_7'); ?>
                        <?php echo $form->dropDownList($model,'call_attempt_7',$model->wdsCallers($model->call_attempt_7)); ?>
                        <?php echo $form->error($model,'call_attempt_7'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'time_7'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'time_7',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'time_7'); ?>
                    </div>

                </div>
            </div>
            <div class="span4">
                <div class="form-section">

                    <h1>HA Information</h1>

                    <div>
                        <?php echo $form->labelEx($model,'status'); ?>
                        <?php echo $form->dropDownList($model,'status',$model->wdsStatuses()); ?>
                        <?php echo $form->error($model,'status'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'assigned_by'); ?>
                        <?php echo $form->dropDownList($model,'assigned_by',$model->wdsCallers($model->assigned_by)); ?>
                        <?php echo $form->error($model,'assigned_by'); ?>
                    </div>
                    
                    <div>
                        <?php echo $form->labelEx($model,'ha_date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'ha_date',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'ha_date'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'ha_time'); ?>
                        <?php echo $form->dropDownList($model,'ha_time',$model->wdsHATimes()); ?>
                        <?php echo $form->error($model,'ha_time'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'homeowner_to_be_present'); ?>
                        <?php echo $form->dropDownList($model,'homeowner_to_be_present',$model->yesNo(true)); ?>
                        <?php echo $form->error($model,'homeowner_to_be_present'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'ok_to_do_wo_member_present'); ?>
                        <?php echo $form->dropDownList($model,'ok_to_do_wo_member_present',$model->yesNo()); ?>
                        <?php echo $form->error($model,'ok_to_do_wo_member_present'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'authorization_by_affadivit'); ?>
                        <?php echo $form->textField($model,'authorization_by_affadivit',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'authorization_by_affadivit'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'contact_date'); ?>
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model' => $model,
                            'attribute' => 'contact_date',
                            'options' => array(
                                'dateFormat' => 'yy-mm-dd',     // format of "2012-12-25"
                            ),
                            'htmlOptions' => array(
                                    'size' => '10',         // textField size
                                    'maxlength' => '10',    // textField maxlength
                            )
                        )); ?>
                        <?php echo $form->error($model,'contact_date'); ?>
                    </div>
                    
                   <div>
                        <?php echo $form->labelEx($model,'engine'); ?>
                        <?php echo $form->dropDownList($model,'engine',$model->wdsEngines()); ?>
                        <?php echo $form->error($model,'engine'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'delivery_method'); ?>
                        <?php echo $form->dropDownList($model,'delivery_method',$model->deliveryMethod()); ?>
                        <?php echo $form->error($model,'delivery_method'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'alt_mailing_address'); ?>
                        <?php echo $form->textField($model,'alt_mailing_address',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'alt_mailing_address'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'fs_offered'); ?>
                        <?php echo $form->dropDownList($model,'fs_offered',$model->yesNo()); ?>
                        <?php echo $form->error($model,'fs_offered'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'fs_accepted'); ?>
                        <?php echo $form->dropDownList($model,'fs_accepted',$model->getFSAcceptedOptions()); ?>
                        <?php echo $form->error($model,'fs_accepted'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'fs_notes'); ?>
                        <?php echo $form->textField($model,'fs_notes',array('size'=>60,'maxlength'=>255)); ?>
                        <?php echo $form->error($model,'fs_notes'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'fs_email_resend'); ?>
                        <?php echo $form->dropDownList($model,'fs_email_resend',$model->yesNoBoolean()); ?>
                        <?php echo $form->error($model,'fs_email_resend'); ?>
                    </div>

                </div>
            </div>
            <div class="span4">
                <div class="form-section">

                    <div>
                        <?php echo $form->labelEx($model,'call_center_comments'); ?>
                        <?php echo $form->textArea($model, 'call_center_comments', array('rows'=>'15', 'cols'=>'35')) ?>
                        <?php echo $form->error($model,'call_center_comments'); ?>
                    </div>

                    <div>
                        <?php echo $form->labelEx($model,'appointment_information'); ?>
                        <?php echo $form->textArea($model, 'appointment_information', array('rows'=>'15', 'cols'=>'35')) ?>
                        <?php echo $form->error($model,'appointment_information'); ?>
                    </div>

                    <div class="buttons">
                        <?php echo CHtml::submitButton('Save & Close', array('class'=>'submit')); ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->