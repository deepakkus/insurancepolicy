<div class="form marginTop10 marginBottom10">

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'agent-form',
	'enableAjaxValidation'=>false,
    'htmlOptions' => array('enctype' => 'multipart/form-data')
));
?>

	<?php echo $form->errorSummary($importFile); ?>

    <div class="paddingTop10 paddingBottom10">
        <?php if (!$importFile->isNewRecord) : ?>
            <div>
                <b>Import File ID:</b> <?php echo $importFile->id; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="clearfix formContainer">
        <h3>Import File Information</h3>
        <div class="fluidField">
            <?php
            echo $form->labelEx($importFile,'status');
            echo $form->dropDownList($importFile,'status', $importFile->getStatuses());
            echo $form->error($importFile,'status');
            ?>
        </div>
        <div class="fluidField">
            <?php echo CHtml::label('CSV','file_name'); ?>
		    <?php echo CHtml::fileField('csv','',array('accept'=>'.csv')); ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($importFile,'client_id');
            echo CHtml::activeDropDownList($importFile, 'client_id', CHtml::listData(Client::model()->findAll(array(
                 'select' => array('id', 'name'),
                 'order' => 'name ASC'
             )), 'id', 'name'), array('prompt'=>'Select a Client'));
            echo $form->error($importFile,'client_id');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($importFile,'date_time');

                if($importFile->isNewRecord)
                    $importFile->date_time = date_format(new DateTime(), 'm/d/Y h:i A');

                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$importFile,
                'attribute' => 'date_time',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));

                echo $form->error($importFile,'date_time');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($importFile,'details');
            echo $form->textField($importFile,'details');
            echo $form->error($importFile,'details');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($importFile,'errors');
            echo $form->textField($importFile,'errors');
            echo $form->error($importFile,'errors');
            ?>
        </div>
    </div>

	<div class="buttons">
		<?php echo CHtml::submitButton($importFile->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('importFile/admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->