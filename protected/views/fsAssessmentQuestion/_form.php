<style>
.row
{
    margin-left: 5px;
}
</style>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'assessment-question-form',
	'enableAjaxValidation'=>false,
    'htmlOptions' => array('enctype' => 'multipart/form-data'),
)); 

	echo $form->hiddenField($question, 'client_id');
	$client = Client::model()->findByPk($question->client_id);
	if($question->isNewRecord)
		echo '<br><b>Adding ';
	else 
		echo '<br><b>Updating ';
	
	echo 'Question for Client '.$client->name.'<br>';
?>
	
	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($question); ?>
	
    <div class="row">
		<?php 
        echo $form->labelEx($question,'question_num');
        if($question->isNewRecord)
        {
            $next_question_num = Client::model()->getMaxAssessmentQuestionNum($question->client_id)+1;
            $question->question_num = $next_question_num;
            $question->order_by = $next_question_num;
            $question->label = $next_question_num;
            $question->section_type = 0;
        }
        
        echo $form->textField($question,'question_num',array('size'=>25,'maxlength'=>25,));
		echo $form->error($question,'question_num'); 
        ?>
	</div>

    <div class="row">
        <?php
        echo $form->labelEx($question, 'set_id');
        echo $form->dropDownList($question, 'set_id', $client->getAppQuestionSets());
        echo $form->error($question,'set_id');
        ?>
    </div>

    <div class="row">
		<?php echo $form->labelEx($question,'order_by'); ?>
		<?php echo $form->textField($question,'order_by',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'order_by'); ?>
	</div>

    <div class="row">
		<?php echo $form->labelEx($question,'label'); ?>
		<?php echo $form->textField($question,'label',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'label'); ?>
	</div>

    <div class="row">
		<?php echo $form->labelEx($question,'active'); ?>
		<?php
        if($question->isNewRecord)
            $question->active = 1;
        echo $form->checkBox($question, 'active', array('value' => 1, 'uncheckValue' => 0)); 
        ?>
		<?php echo $form->error($question,'active'); ?>
	</div>

    <div class="row">
		<?php echo $form->labelEx($question,'type'); ?>
        <?php echo $form->dropDownList($question, 'type', $question->getTypeOptions()); ?>
		<?php echo $form->error($question,'type'); ?>
	</div>
    
	<div class="row">
		<?php echo $form->labelEx($question,'section_type'); ?>
        <?php echo $form->dropDownList($question, 'section_type', array('0'=>'Home', '1'=>'Yard')); ?>
		<?php echo $form->error($question,'section_type'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'title'); ?>
		<?php echo $form->textField($question,'title',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'title'); ?>
	</div>

    <div class="row">
        <?php echo $form->labelEx($question,'description'); ?>
        <?php echo $form->textArea($question,'description',array('rows'=>3, 'cols'=>50, 'maxlength'=>256, 'style'=>'width:400px')); ?>
        <?php echo $form->error($question,'description'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($question,'question_text'); ?>
        <?php echo $form->textArea($question,'question_text',array('rows'=>3, 'cols'=>50, 'maxlength'=>1024, 'style'=>'width:400px')); ?>
        <?php echo $form->error($question,'question_text'); ?>
    </div>

    <div class="row">
		<?php 
		echo $form->labelEx($question,'choices');
		echo '<b>NOTE:</b> Needs to be a JSON object containing a "choices" array.';
        echo 'For example: <pre>';
		echo htmlspecialchars('
{
    "choices":[
        {"label":"Yes","value":"0","imageurl":"https://dev.wildfire-defense.com/images/app/yes8.png", (optional) "autotrigger":true,"autofill_question_id":1234},
        {"label":"No","value":"1","imageurl":"https://dev.wildfire-defense.com/images/app/no8.png"},
        {"label":"Not Sure","value":"2","imageurl":"https://dev.wildfire-defense.com/images/app/notsure.png"}
    ]
}');    
        echo '</pre>';
		echo $form->textArea($question,'choices',array('rows'=>10, 'cols'=>200, 'maxlength'=>1000, 'style'=>'width:600px'));
		echo $form->error($question,'choices'); 
        ?>
    </div>

    <div class="row">
		<?php echo $form->labelEx($question,'choices_type'); ?>
		<?php echo $form->dropDownList($question, 'choices_type', $question->getChoicesTypeOptions()); ?>
		<?php echo $form->error($question,'choices_type'); ?>
	</div>
	
    <div class="row">
        <?php echo $form->labelEx($question,'photo_text'); ?>
        <?php echo $form->textArea($question,'photo_text',array('rows'=>3, 'cols'=>50, 'maxlength'=>1024, 'style'=>'width:400px')); ?>
        <?php echo $form->error($question,'photo_text'); ?>
    </div>

	<div class="row">
		<?php echo $form->labelEx($question,'number_of_required_photos'); ?>
		<?php echo $form->textField($question,'number_of_required_photos',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'number_of_required_photos'); ?>
	</div>

    <div class="row">
        <?php echo $form->labelEx($question,'allow_notes'); ?>
        <?php echo $form->dropDownList($question, 'allow_notes', array('1'=>'Yes', '0'=>'No', '2'=>'Required')); ?>
        <?php echo $form->error($question,'allow_notes'); ?>
    </div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'launch_camera_on_response_action'); ?>
		<?php echo $form->textField($question,'launch_camera_on_response_action',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'launch_camera_on_response_action'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'enforce_required_photos_on_response_action'); ?>
		<?php echo $form->textField($question,'enforce_required_photos_on_response_action',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'enforce_required_photos_on_response_action'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($question,'requires_landscape_photo'); ?>
		<?php echo $form->textField($question,'requires_landscape_photo',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'requires_landscape_photo'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'overlay_image_should_stretch'); ?>
		<?php echo $form->textField($question,'overlay_image_should_stretch',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'overlay_image_should_stretch'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'yes_points'); ?>
		<?php echo $form->textField($question,'yes_points',array('size'=>25,'maxlength'=>25)); ?>
		<?php echo $form->error($question,'yes_points'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'overlay_portrait_image_name'); ?>
		<?php echo $form->textField($question,'overlay_portrait_image_name',array('size'=>100,'maxlength'=>256, 'style'=>'width:400px')); ?>
		<?php echo $form->error($question,'overlay_portrait_image_name'); ?>
    </div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'help_uri'); ?>
		<?php echo $form->textField($question,'help_uri',array('size'=>100,'maxlength'=>256, 'style'=>'width:400px')); ?>
		<?php echo $form->error($question,'help_uri'); ?>
    </div>
	
    <div class="row">
		<?php echo $form->labelEx($question,'risk_text'); ?>
		<?php echo $form->textArea($question,'risk_text',array('rows'=>3, 'cols'=>50, 'maxlength'=>1024, 'style'=>'width:400px')); ?>
		<?php echo $form->error($question,'risk_text'); ?>
    </div>

	<div class="row">
		<?php echo $form->labelEx($question,'rec_text'); ?>
		<?php echo $form->textArea($question,'rec_text',array('rows'=>3, 'cols'=>50, 'maxlength'=>1024, 'style'=>'width:400px')); ?>
		<?php echo $form->error($question,'rec_text'); ?>
    </div>
	
	<div class="row">
		<?php echo $form->labelEx($question,'overlay_image_help_text'); ?>
		<?php echo $form->textArea($question,'overlay_image_help_text',array('rows'=>3, 'cols'=>50, 'maxlength'=>512, 'style'=>'width:400px')); ?>
		<?php echo $form->error($question,'overlay_image_help_text'); ?>
    </div>

    <div class="row">
		<?php echo $form->labelEx($question,'example_text'); ?>
		<?php echo $form->textArea($question,'example_text',array('rows'=>3, 'cols'=>50, 'maxlength'=>1024, 'style'=>'width:400px')); ?>
		<?php echo $form->error($question,'example_text'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($question,'example_image'). '(example_image_file_id: '.$question->example_image_file_id.")<br>"; ?>
        <?php echo CHtml::fileField('example_image', '', array('id'=>'example_image', 'accept '=> '.jpg','.png','.jpeg','.gif')); ?>
        <?php 
        if(isset($question->example_image_file_id))
        {
            echo 'Current Image: <img src="'.Yii::app()->request->baseUrl.'/index.php?r=file/loadFile&id='.$question->example_image_file_id.'" />';
        }
        ?>
    </div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($question->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <?php echo CHtml::link('Cancel', array('client/update', 'id' => $client->id),array('style'=>'padding-left:6px')); ?>

	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->