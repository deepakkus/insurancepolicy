<?php
/* @var $this AppSettingController */
/* @var $model AppSetting */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'app-setting-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

    <?php
        echo '<div>';
		echo $form->labelEx($model,'type');
        echo $form->dropDownList($model, 'type', $model->getTypes());
		echo $form->error($model,'type'); 
	    echo '</div>';
    ?>
    

	<div>
		<?php 
        echo $form->labelEx($model,'client_ids');
        $selected = array();
        foreach($model->getSelectedClients() as $client)
        {
            if(!empty($client))
                $selected[$client] = array('selected'=>'selected');
        }
        if(empty($selected)) //if none were selected default to All which is id 0
            $selected['0'] = array('selected'=>'selected');
        echo CHtml::activeDropDownList($model, 'client_ids', array_merge(array('0'=>'All'), Client::getClientNames()), array('id'=>'type-select', 'multiple'=>'multiple', 'size'=>'15', 'options'=>$selected))." Hold down CTRL to select multiple clients";
        echo $form->error($model,'client_ids'); 
        ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'application_context'); ?>
		<?php
        $app_context_options = array('default'=>'default');
        foreach(Client::model()->findAll() as $client)
            $app_context_options = array_merge($app_context_options, $client->getApplicationContexts());
        echo CHtml::activeDropDownList($model, 'application_context', $app_context_options);
        ?>
		<?php echo $form->error($model,'application_context'); ?>
	</div>

    <div>
        <?php echo $form->labelEx($model,'platform_context'); ?>
        <?php echo $form->textField($model,'platform_context',array('size'=>50,'maxlength'=>50)); ?>
        <?php echo $form->error($model,'platform_context'); ?>
    </div>

	<div>
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>200)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'data_type'); ?>
		<?php echo $form->textField($model,'data_type',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'data_type'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'value'); ?>
		<?php echo $form->textArea($model,'value',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'value'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'effective_date'); ?>
				<?php
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                    'model' => $model,
                    'attribute' => 'effective_date',
                    'options' => array(
                        'showAnim' => 'fold',
                        'showButtonPanel' => true,
                        'autoSize' => true,
                        'dateFormat' => 'mm/dd/yy',
                        'timeFormat' => 'h:mm TT',
                        'ampm' => true,
                        'separator' => ' ',
                        'defaultValue' => null,
                    ),
                ));
                ?>
		<?php echo $form->error($model,'effective_date'); ?>
	</div>

	<div>
		<?php echo $form->labelEx($model,'expiration_date'); ?>
				<?php
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                    'model' => $model,
                    'attribute' => 'expiration_date',
                    'options' => array(
                        'showAnim' => 'fold',
                        'showButtonPanel' => true,
                        'autoSize' => true,
                        'dateFormat' => 'mm/dd/yy',
                        'timeFormat' => 'h:mm TT',
                        'ampm' => true,
                        'separator' => ' ',
                        'defaultValue' => null,
                    ),
                ));
                ?>
		<?php echo $form->error($model,'expiration_date'); ?>
	</div>

    <div>
        <?php echo $form->labelEx($model,'minimum_resolution'); ?>
        <?php echo $form->textField($model,'minimum_resolution',array('size'=>50,'maxlength'=>50)); ?>
        <?php echo $form->error($model,'minimum_resolution'); ?>
    </div>

	<div class="buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->