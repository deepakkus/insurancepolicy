<?php
/* @var $this UserController */
/* @var $model User */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerScriptFile('/js/user/formAffiliationToggle.js',CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/css/user/form.css');

?>

<div class="form">

    <?php
    
    $form=$this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true
        )
    ));

    CHtml::$renderSpecialAttributesValue = false;

    ?>

    <br>
    <h2>General Account Info</h2>
    
	<?php echo $form->errorSummary($model); ?>
        <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
        
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('size'=>32,'maxlength'=>32))." Leave Blank to keep the same"; ?>
		<?php echo $form->error($model,'password'); ?>
   </div>
	
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>	
    
    <br>

    <h2>User Affiliation</h2>
    
    <div class="row-fluid" style ="padding-top:10px;">
        <?php  echo CHtml::radioButtonList('affiliation', '', array('wds'=> 'WDS Staff' ,'alliance' => 'Alliance'), array(
            'labelOptions' => array('style'=>'display:inline; padding-right:25px;'),
            'separator'=>' ',
            'required' => true
        )); ?>
	</div>


    <div class="row-fluid" style="padding-top:10px;">
        <?php
        echo $form->labelEx($model,'alliance_id');
        echo CHtml::activeDropDownList($model, 'alliance_id', CHtml::listData(Alliance::model()->findAll(), 'id', 'name'), array('prompt'=>''));
        echo $form->error($model,'alliance_id');
        ?>
    </div>

    <h2>User Permissions</h2>

    <div class="row-fluid" style ="padding-top:10px;">
		<?php 
            echo $form->labelEx($model,'type');
            $selected = array();
            foreach($model->getSelectedTypes() as $type)
            {
                $selected[$type] = array('selected'=>'selected');
            }
            echo CHtml::activeDropDownList($model, 'type', User::getEngineUserTypes(), array('id'=>'type-select', 'multiple'=>'multiple', 'size'=>'5', 'options'=>$selected,'required' => true))." Hold down CTRL to select multiple types";
            echo $form->error($model,'type'); 
        ?>
	</div>
    
    <br>
    <h2>Account Expiration and Locking</h2>

    <div class="row-fluid">
		<?php echo $form->labelEx($model,'active'); ?>
        <?php echo $form->checkbox($model,'active'); ?>
		<?php echo $form->error($model,'active'); ?>
	</div>
    <div class="row-fluid">
        <?php echo $form->labelEx($model,'removed'); ?>
        <?php echo $form->checkbox($model,'removed'); ?>
        <?php echo $form->error($model,'removed'); ?>
    </div>
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'pw_exp'); ?>
		<?php
            if(!isset($model->pw_exp))
                $model->pw_exp = date('Y-m-d', strtotime('+ 90 days'));
            $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model' => $model,
                'attribute' => 'pw_exp',
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
		<?php echo $form->error($model,'pw_exp'); ?>
	 </div>
	
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'locked_until'); ?>
		<?php
				$this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
					'model' => $model,
					'attribute' => 'locked_until',
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
		<?php echo $form->error($model,'locked_until'); ?>
	 </div>
	
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'login_attempts'); ?>
		<?php echo $form->textField($model,'login_attempts',array('size'=>10,'maxlength'=>5)); ?>
		<?php echo $form->error($model,'login_attempts'); ?>
	</div>
    <div class="row-fluid buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->

<script type="text/javascript">

    $('document').ready(function () {

        <?php if (!$model->isNewRecord): ?>

        selectAffiliate();
        checkForDeactivate();

        <?php else: ?>

        $('#User_client_id').prop("disabled", true);
        $('#User_alliance_id').prop("disabled", true);

        <?php endif; ?>
    });

</script>
