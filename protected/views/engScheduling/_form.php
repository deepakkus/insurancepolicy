<?php

/* @var $this EngSchedulingController */
/* @var $model EngScheduling */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerScriptFile('/js/engScheduling/form.js',CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/css/engScheduling/admin.css');

if(!$model->isNewRecord)
    echo '<input type="hidden" id="schedule_id" value="'.$model->id.'">';
else
    echo '<input type="hidden" id="schedule_id" value="">';
?>

<div class="form">

<?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm',array(
    'id' => 'eng-scheduling-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">

                <!-- Engine Information -->

                <h3>Engine Information</h3>

	            <div>
		            <?php echo $form->labelEx($model,'engine_id'); ?>
                    <?php echo $form->dropDownList($model,'engine_id',CHtml::listData($model->getAvailibleEngines(),'id',function($data) {
                        return $data->getEngineSource($data->engine_source) . ' - ' . $data->engine_name;          
                    }),array('empty'=>' ')); ?>
		            <?php echo $form->error($model,'engine_id'); ?>
	            </div>

                <div class="row-fluid">
                    <div class="span6">
                        <div>
                            <?php echo $form->datepickerRow($model,'start_date',array('options' =>array('autoclose' => true, 'todayHighlight' => true))); ?>
                        </div>
                        <div>
                            <?php echo $form->datepickerRow($model,'end_date',array('options' =>array('autoclose' => true, 'todayHighlight' => true))); ?>
                        </div>
                    </div>
                    <div class="span6">
                        <div>
                            <?php echo $form->timepickerRow($model,'start_time', array(
                                'class' => 'input-small',
                                'options' => array(
                                    'showMeridian' => false,
                                    'defaultTime' => false
                                )
                            )); ?>
                        </div>
                        <div>
                            <?php echo $form->timepickerRow($model,'end_time', array(
                                'class' => 'input-small',
                                'options' => array(
                                    'showMeridian' => false,
                                    'defaultTime' => false
                                )
                            )); ?>
                        </div>
                    </div>
                </div>

                <!-- Resource Order Information -->

                <h3 class="marginTop10">Resource Order Information</h3>

                
                <div>
                    <?php 
                    if(!$model->isNewRecord){
                        echo CHtml::label($model->getAttributeLabel('resource_order_id') , CHtml::activeId($model, 'resource_order_id')); ?>
                        <?php echo $form->textField($model,'resource_order_id',array('size'=>8,'maxlength'=>8,'readonly'=>true));
                    } ?>
                </div>

	            <div>
		            <?php echo $form->labelEx($model,'specific_instructions'); ?>
                    <?php echo $form->textArea($model,'specific_instructions',array('maxlength'=>200, 'rows' => 5, 'cols' => 40, 'style'=>'width:auto;')); ?>
		            <?php echo $form->error($model,'specific_instructions'); ?>
	            </div>

                <!-- Engine Assignment Information -->

                <h3 class="marginTop10">Assignment Information</h3>
                    
                <div>
                    <?php echo $form->labelEx($model,'fire_officer_id'); ?>
                    <?php echo $form->dropDownList($model,'fire_officer_id',CHtml::listData($model->getAvailibleFireOfficers(),'id',function($data) {
                        return $data->last_name . ', ' . $data->first_name;          
                    }),array('empty'=>'')); ?>
                    <?php echo $form->error($model,'fire_officer_id'); ?>
                </div>

	            <div>
		            <?php echo $form->labelEx($model,'comment'); ?>
                    <?php echo $form->textArea($model,'comment',array('maxlength'=>200, 'rows' => 5, 'cols' => 40, 'style'=>'width:auto;')); ?>
		            <?php echo $form->error($model,'comment'); ?>
	            </div>

	            <div>
		            <?php echo $form->labelEx($model,'assignment'); ?>  
		            <?php echo $form->dropDownList($model,'assignment',$model->getEngineAssignments(),array('empty'=>' ')); ?>
		            <?php echo $form->error($model,'assignment'); ?>
	            </div>

            </div>
            <div class="span6">
                <div class="location-info-anchor"></div>
                <div class="location-info">

                    <!-- Engine Location Information -->

                    <h3>Location Information</h3>

	                <div>
		                <?php echo $form->labelEx($model,'fire_id'); ?>
                        <?php if (!$model->fire_id): ?>
                        <?php echo $form->dropDownList($model,'fire_id',array(),array('disabled' => $model->fire_id ? false : true)); ?>
                        <?php else: ?>
                        <?php echo $form->dropDownList($model,'fire_id',$this->formGetAvailibleFiresUpdate($model->id),array('empty'=>'')); ?>
                        <?php endif; ?>
		                <?php echo $form->error($model,'fire_id'); ?>
	                </div>

                    <div>
	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'city'); ?>
		                    <?php echo $form->textField($model,'city',array('size'=>30,'maxlength'=>30)); ?>
		                    <?php echo $form->error($model,'city'); ?>
	                    </div>

	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'state'); ?>
                            <?php echo $form->dropDownList($model,'state', Helper::getStates(), array('style'=>'width:100px;', 'prompt'=>'')); ?>
		                    <?php echo $form->error($model,'state'); ?>
	                    </div>
                        <div class="clearfix">
                            <?php echo CHtml::link('Geocode Address','#',array('id'=>'geocode-address-link')); ?>
                        </div>
                    </div>

                    <div>
	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'lat'); ?>
		                    <?php echo $form->textField($model,'lat',array('size'=>18,'maxlength'=>18)); ?>
		                    <?php echo $form->error($model,'lat'); ?>
	                    </div>

	                    <div class="clearfix">
		                    <?php echo $form->labelEx($model,'lon'); ?>
		                    <?php echo $form->textField($model,'lon',array('size'=>18,'maxlength'=>18)); ?>
		                    <?php echo $form->error($model,'lon'); ?>
	                    </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row-fluid">
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

<?php $this->endWidget(); ?>

<!-- RO Modal -->

<?php $this->beginWidget('bootstrap.widgets.TbModal', array(
    'id' => 'ro-modal',
    'htmlOptions' => array('class' => 'modal-admin')
)); ?>

<div class="modal-header">
    <a class="close" data-dismiss="modal">&times;</a>
    <h3>Resource Orders</h3>
</div>
<div class="modal-body">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <p><b>Select a Client </b><small>(find past used ROs)</small></p>
                <?php echo CHtml::dropDownList('clients', '', CHtml::listData($model->getAvailibleFireClients(), 'id', 'name'), array('empty' => ''));  ?>
            </div>
            <div class="span6">
                <p><b>Create a new RO</b></p>
                <a class="btn btn-success" id="create-ro" href="<?php echo $this->createUrl('/engResourceOrder/resourceOrderCreateModel'); ?>">New RO</a>
            </div>
        </div>
        <br />
        <div class="row-fluid">
            <div id="ro-modal-container" style="height: 400px;"></div>
        </div>
    </div>
</div>

<?php $this->endWidget(); ?>

</div><!-- end form -->