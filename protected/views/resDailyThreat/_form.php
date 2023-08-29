<?php
/* @var $this ResDailyThreatController */
/* @var $model ResDailyThreat */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'res-daily-threat-form'
)); ?>

	<?php echo $form->errorSummary($model); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <div class="form-section">
                    <h2>Today's Forecast</h2>
                    <?php $regions = array(
                        'eastern',
                        'southern',
                        'southwest',
                        'california_south',
                        'california_north',
                        'great_basin',
                        'rocky_mountains',
                        'northern_rockies',
                        'northwest',
                        'alaska'

                    ); ?>

                    <?php foreach ($regions as $region): ?>

	                <div>
		                <?php echo $form->labelEx($model, $region); ?>
                        <div class="compactRadioGroup">
		                    <?php echo $form->radioButtonList($model,$region, $model->getFireDangerRatings(), array('separator' => " &nbsp; ")); ?>
                        </div>
		                <?php echo $form->error($model,$region); ?>
	                </div>

                    <?php endforeach; ?>
                </div>
            </div>
            <div class="span6">
                <div class="form-section">
                    <h2>Tomorrow's Forecast</h2>
                    <?php $regions = array(
                        'fx_eastern',
                        'fx_southern',
                        'fx_southwest',
                        'fx_california_south',
                        'fx_california_north',
                        'fx_great_basin',
                        'fx_rocky_mountains',
                        'fx_northern_rockies',
                        'fx_northwest',
                        'fx_alaska'
                    ); ?>

                    <?php foreach ($regions as $region): ?>

	                <div>
		                <?php echo $form->labelEx($model, $region); ?>
                        <div class="compactRadioGroup">
		                    <?php echo $form->radioButtonList($model,$region, $model->getFireDangerRatings(), array('separator' => " &nbsp; ")); ?>
                        </div>
		                <?php echo $form->error($model,$region); ?>
	                </div>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <div>
		            <?php echo $form->labelEx($model,'details'); ?>
		            <?php echo $form->textArea($model,'details',array('maxlength'=>1000, 'rows' => 6, 'style' => 'width: 100%;')); ?>
		            <?php echo $form->error($model,'details'); ?>
	            </div>

                <div class="buttons">
	                <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('resDailyThreat/admin')); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->