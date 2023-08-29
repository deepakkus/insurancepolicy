<?php

/* @var $this ResMonitorLogController */
/* @var $model ResMonitorLog */
/* @var $form CActiveForm */

$clients = Client::model()->findAllByAttributes(array('wds_fire'=>1));

?>

<div class="form">

    <?php $form=$this->beginWidget('CActiveForm', array(
        'id'=>'res-monitor-log-form'
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span7">
                <!-- Monitor log comments -->
                <div class="form-section">
                    <h2>Entry</h2>
                    <div>
                        <?php echo $form->hiddenField($model,'Dispatcher'); ?>
                    </div>
                    <div class ="span7" style="margin-left:0px;">
                        <?php echo $form->labelEx($model,'Comments',array('style'=>'')); ?>
                        <?php echo $form->textArea($model,'Comments',array('maxlength'=>1000, 'rows' => 6, 'style'=>'width:100%;')); ?>
                        <?php echo $form->error($model,'Comments'); ?>
                    </div>
				<?php if(!$model->isNewRecord): ?>
                <div class="row-fluid">
						<div class ="span7">
							<?php echo $form->labelEx($model,'monitor_log_duty_officer_comments'); ?>
							<?php echo $form->textArea($model,'monitor_log_duty_officer_comments',array('maxlength'=>1000, 'rows' => 6, 'style'=>'width:100%;')); ?>
							<?php echo $form->error($model,'monitor_log_duty_officer_comments'); ?>
						</div>
					<div class="span5" style="padding-top:27px;">
						<div>
							<?php echo $form->checkBox($model,'monitor_log_no_immediate_threat'); ?>
							<?php echo $form->labelEx($model,'monitor_log_no_immediate_threat',array('style'=>'display: inline-block;')); ?>
							<?php echo $form->error($model,'monitor_log_no_immediate_threat'); ?>
						</div>
					</div>
					</div>
					<?php endif; ?>
                </div>
            </div>
            <div class="span5">
                <!-- Smokecheck -->
                <div class="form-section">
                    <?php if($model->isNewRecord): ?>

                    <h2>Smoke Check Process</h2>
                    <p>After saving, re-open this entry to initialize a smoke check</p>

                    <?php else: ?>

                    <h2>Smoke Check Process</h2>
                    <div>
                        <?php echo $form->labelEx($model,'Smoke_Check_Comments'); ?>
                        <?php echo $form->textArea($model,'Smoke_Check_Comments',array('maxlength'=>1000, 'rows' => 6, 'style'=>'width:100%;')); ?>
                        <?php echo $form->error($model,'Smoke_Check_Comments'); ?>
                    </div>
                    <div>
                        <?php echo $form->labelEx($model,'Alert_Distance'); ?>
                        <?php echo $form->dropDownList($model,'Alert_Distance', array('3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10), array('style'=>'width:100px;')); ?>
                        <?php echo $form->error($model,'Alert_Distance'); ?>
                    </div>
                    <div>
                        <?php echo $form->labelEx($model,'Smoke_Check'); ?>
                        <?php echo $form->checkBox($model,'Smoke_Check'); ?>
                        <?php echo $form->error($model,'Smoke_Check'); ?>
                    </div>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">

                <!-- Noteworth Override checkbox -->
                <div class="form-section">
                    <div>
                        <?php if ($model->isNewRecord): ?>
                        <h2>Noteworthy Fires</h2>
                        <p>Noteworthy Fires will be automatically created for:</p>
                        <ul>
                            <li>Fires that are triggering not enrolled policyholders</li>
                            <li>Fires that exceed 1000 acres</li>
                            <li>If the Media Event checkbox is selected:</li>
                        </ul>

                        <?php echo $form->labelEx($model,'Media_Event'); ?>
                        <?php echo $form->checkBox($model,'Media_Event'); ?>
                        <?php echo $form->error($model,'Media_Event'); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php 
                if($model->isNewRecord) 
                {
                    $httpRequest = new CHttpRequest;
                    echo $form->hiddenField($model,'Obs_ID',array('value'=> $httpRequest->getParam('obs_id'))); 
                }  
                ?>

                <div id ="button-row" class="buttons" style ="padding-top:50px;">
                    <div class="marginTop10 marginBottom10">
                        <p><b><u>The following will automatically happen after saving:</u></b></p>
                        <ul>
                            <li>Noteworthies are automatically created if the client is triggered by the fire, if the fire exceeds 1000 acres or if the media event is selected</li>
                            <li>Email will be sent out to WDS staff if smoke check is selected</li>
                            <li>Email will be sent out if smoke check is selected</li>
                        </ul>
                    </div>
                    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', ($page === 'monitor' ? array('resMonitorLog/admin') : array('resMonitorLog/smokeCheck'))); ?>
                    </span>
                </div>

            </div>
        </div>
    </div>

    <?php $this->endWidget(); ?>

</div><!-- form -->

<script>
    // Add progress bar when submitted
    $('#res-monitor-log-form').submit(function () {
        var submitButton = this.querySelector('input[type="submit"]');
        $('#button-row').empty();
        $('#button-row').html(
            '<div style="width: 300px; margin: 5px 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">' +
                '<div class="bar" style="width: 100%;margin:0;"></div>' +
            '</div>');
    });
</script>