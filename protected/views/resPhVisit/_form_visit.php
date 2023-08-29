<?php

	echo $form->errorSummary($model);
    
?>

<h3><u>Policyholder Visit Details</u></h3>

<div class="row-fluid row-padding">
	<div class="span3">
		<?php

		echo $form->labelEx($model, 'status');
		echo $form->dropDownList($model, 'status', $model->getStatusTypes(), array(
			'size' => '4'
		));

		?>
	</div>
	<div class="span3">
		<?php
            
        echo $form->labelEx($model,'date_action');
		$this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
			'model' => $model,
			'attribute' => 'date_action',
			'mode' => 'datetime',
			'options' => array(
				'showAnim' => 'fold',
				'showButtonPanel' => false,
				'autoSize' => true,
				'dateFormat' => 'yy-mm-dd',
				'ampm' => false,
				'separator' => ' '
			)
		));
		echo $form->error($model,'date_action');

		?>
	</div>
	<div class="span3">
        <?php

		echo $form->labelEx($model, 'approval_user_id');
		echo $form->dropDownList($model, 'approval_user_id', CHtml::listData($model->getManagerApprovalDropdown(), 'id', 'name'), array(
			'prompt' => '',
			'disabled' => true
		));
        echo $form->error($model,'approval_user_id');

        ?>
	</div>
	<div class="span3">
        <?php

		echo $form->labelEx($model, 'review_status');
		echo $form->dropDownList($model, 'review_status', $model->getReviewStatusType((!$model->isNewRecord ? $showStatus : '')));
        echo $form->error($model,'review_status');

        ?>
	</div>
</div>

<div class="row-fluid row-padding">
	<div class="span6">
        <?php

		echo $form->labelEx($model, 'comments');
		echo $form->textArea($model, 'comments', array(
			'rows' => 12,
			'maxlength' => 3000,
			'style' => 'width:100%;',
			'placeholder' => 'Enter policy visit ' . $model->getAttributeLabel('comments'),
			'hint' => 'Limited to 3000 characters'
		));
        echo $form->error($model,'comments');

        ?>
	</div>
	<div class="span6">
        <?php

		echo $form->labelEx($model, 'publish_comments');
		echo $form->textArea($model, 'publish_comments', array(
			'rows' => 12,
			'maxlength' => 3000,
			'style' => 'width:100%;',
			'placeholder' => 'Enter comments about the visit to show on Dashboard',
			'hint' => 'Limited to 3000 characters'
		));
        echo $form->error($model,'publish_comments');

        ?>
	</div>
</div>

<div class="row-fluid row-padding">
    <div class="span6">
        <?php

		echo $form->labelEx($model, 'phvisit_lat');
		echo $form->textField($model, 'phvisit_lat', array(
			'placeholder' => 'Enter policy visit ' . $model->getAttributeLabel('phvisit_lat'),
		));
        echo $form->error($model,'phvisit_lat');

        ?>
    </div>
    <div class="span6"></div>
</div>

<div class="row-fluid row-padding">
    <div class="span6">
        <?php

        echo $form->labelEx($model, 'phvisit_long');
        echo $form->textField($model, 'phvisit_long', array(
            'placeholder' => 'Enter policy visit ' . $model->getAttributeLabel('phvisit_long'),
        ));
        echo $form->error($model,'phvisit_long');

        ?>
    </div>
    <div class="span6"></div>
</div>

<?php

	if ($model->isNewRecord)
	{
		echo $form->hiddenField($model, 'property_pid', array('value' => $pid));
		echo $form->hiddenField($model, 'client_id', array('value' => $client_id));
		echo $form->hiddenField($model, 'fire_id', array('value' => $fire_id));
		echo $form->hiddenField($model, 'user_id', array('value' => Yii::app()->user->id));
	}

?>

<h3><u>Policyholder Visit Actions</u></h3>

<?php

if ($model->isNewRecord)
{
    echo '<h2><small>Fill out visit details first!</small></h2>';
}
else
{
    echo $this->renderPartial('_form_actions', array(
        'model' => $model
    ));
}

echo CHtml::script('

	/*
	* Check publish comments field empty/not if publish field is checked
	*/
	$(".visit-submit" ).click(function(){
		if($("#ResPhVisit_publish").is(":checked")){
			if($("#ResPhVisit_publish_comments").val()==""){
				alert( "Sorry!! Publish Comments can not be Empty." );
				return false;
			}
		}
	});

');
?>