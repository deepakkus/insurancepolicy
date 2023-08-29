<?php
/* @var $this EngCrewManagementController */
/* @var $model EngCrewManagement */
/* @var $form CActiveForm */
?>
<script>
$( document ).ready(function() {
     $("#btnSubmit").click(function () {
        var ext = $('#create_photo_id').val().split('.').pop().toLowerCase();
        if(($.inArray(ext, ['png','jpg','jpeg']) == -1) && $('#create_photo_id').val() != '') {
            alert('Please upload jpg/png image only!');
            return false;
        }
    });
});
</script>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'eng-crew-management-form',
    'htmlOptions' => array('enctype' => 'multipart/form-data')
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <div class="form-section">
	                <div>
		                <?php echo $form->labelEx($model,'first_name'); ?>
		                <?php echo $form->textField($model,'first_name',array('size'=>20,'maxlength'=>20)); ?>
		                <?php echo $form->error($model,'first_name'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'last_name'); ?>
		                <?php echo $form->textField($model,'last_name',array('size'=>20,'maxlength'=>20)); ?>
		                <?php echo $form->error($model,'last_name'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'address'); ?>
                        <?php echo $form->textField($model,'address',array('size'=>80,'maxlength'=>80)); ?>
		                <?php echo $form->error($model,'address'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'work_phone'); ?>
		                <?php echo $form->telField($model,'work_phone',array('size'=>20,'maxlength'=>20,'placeholder'=>'xxx-xxx-xxxx','pattern'=>'\d{3}-\d{3}-\d{4}')); ?>
		                <?php echo $form->error($model,'work_phone'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'cell_phone'); ?>
		                <?php echo $form->telField($model,'cell_phone',array('size'=>20,'maxlength'=>20,'placeholder'=>'xxx-xxx-xxxx','pattern'=>'\d{3}-\d{3}-\d{4}')); ?>
		                <?php echo $form->error($model,'cell_phone'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'email'); ?>
		                <?php echo $form->emailField($model,'email',array('size'=>50,'maxlength'=>50,'placeholder'=>'user@mydomain.com')); ?>
		                <?php echo $form->error($model,'email'); ?>
	                </div>

	                <div>
		                <?php echo $form->labelEx($model,'crew_type'); ?>
                        <div class="compactRadioGroup">
                            <?php echo $form->radioButtonList($model,'crew_type', $model->getCrewTypes(), array('prompt'=>'', 'separator' => ' &nbsp; ')); ?>
		                    <?php echo $form->error($model,'crew_type'); ?>
                        </div>
	                </div>

                    <div>
                        <?php echo $form->labelEx($model,'fire_officer'); ?>
                        <?php echo $form->checkBox($model,'fire_officer'); ?>
                        <?php echo $form->error($model,'fire_officer'); ?>
                    </div>

                    <div>
                        <?php echo CHtml::label('Crew Photo', CHtml::activeId($model, 'photo_id')); ?>
		                <?php echo CHtml::fileField('create_photo_id','',array('accept'=>'image/png,image/jpeg')); ?>
                        <?php echo CHtml::image('images/photo-medium.png', 'Crew Photo'); ?>
                    </div>
                    <p><i>(Currently: <?php echo $model->photoName; ?>)</i></p>

	                <div class="buttons">
		                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit', 'id' => 'btnSubmit')); ?>
                        <span class="paddingLeft10">
                            <?php echo CHtml::link('Cancel', array('admin')); ?>
                        </span>
	                </div>
                </div>
            </div>
            <div class="span6">
                <div class="form-section">
                    <h2>Alliance</h2>
	                <div>
		                <?php echo $form->labelEx($model,'alliance'); ?>
		                <?php echo $form->checkBox($model,'alliance',array(
                            'onclick'=>'if(this.checked) { $("#'.CHtml::activeId($model, 'alliance_id').'").prop("disabled", false); }
                                                    else { $("#'.CHtml::activeId($model, 'alliance_id').'").prop("disabled", true);
                                                           $("#'.CHtml::activeId($model, 'alliance_id').'").prop("selectedIndex", 0); }'
                        )); ?>
		                <?php echo $form->error($model,'alliance'); ?>
	                </div>

                    <div>
		                <?php echo $form->labelEx($model,'alliance_id'); ?>
		                <?php echo $form->dropDownList($model,'alliance_id',$model->getAlliancePartners(),array('empty'=>' ','disabled' => $model->alliance ? false : true)); ?>
		                <?php echo $form->error($model,'alliance_id'); ?>
                    </div>

                    <br />
                    <h2>User Info</h2>

                    <div>
		                <?php echo $form->labelEx($model,'user_id'); ?>

		                <?php echo $form->dropDownList($model,'user_id',$model->getUsers(),array('empty'=>' ')); ?>

		                <?php echo $form->error($model,'user_id'); ?>
                    </div>
                    <br />
                    <div>
                        <?php echo $form->labelEx($model,'wdsfleet_active'); ?>
                        <?php echo $form->checkBox($model,'wdsfleet_active');?>
                    </div>
                    <br />
                    <div>
                        <?php echo $form->labelEx($model,'wdsfleet_download_kmz'); ?>
                        <?php echo $form->checkBox($model,'wdsfleet_download_kmz');?>
                    </div>
                    <br />
                    <div>
                        <?php echo $form->labelEx($model,'wdsfleet_download_policy'); ?>
                        <?php echo $form->checkBox($model,'wdsfleet_download_policy');?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->