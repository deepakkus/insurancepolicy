<?php

/* @var $this RiskBatchController */
/* @var $model RiskScore */
/* @var $form TbActiveForm */

Yii::app()->clientScript->registerCss('search-css','

    form label {
        font-weight: bold;
        font-size: 0.9em;
        display: block;
    }

');

?>

<div class="search-form" style="width: 600px;">

    <?php $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	    'action' => $this->createUrl($this->route),
        'id' => 'risk-score-search',
        'htmlOptions' => array('class' => 'well'),
	    'method' => 'get',
    )); ?>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <?php echo $form->radioButtonListRow($riskScore, 'client_id', $riskScore->getRiskClients()); ?>
                <?php echo $form->radioButtonListRow($riskScore, 'score_type', $riskScore->getRiskScoreTypes()); ?>
            </div>
            <div class="span6">
                <?php
                
                echo $form->datepickerRow($riskScore, 'searchStartDate', array(
                    'placeholder' => 'Start Date ...',
                    'prepend' => '<i class="icon-calendar"></i>',
                    'options' =>array(
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    )
                ));
                
                echo $form->datepickerRow($riskScore, 'searchEndDate', array(
                    'placeholder' => 'End Date ...',
                    'prepend' => '<i class="icon-calendar"></i>',
                    'options' =>array(
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'format' => 'yyyy-mm-dd',
                    )
                ));
                
                ?>

                <div>
		            <?php echo $form->labelEx($riskScore, 'state'); ?>
                    <div class="compactRadioGroup">
                        <?php echo $form->dropDownList($riskScore, 'state', $riskScore->getRiskScoreStates(), array('prompt' => '')); ?>
                    </div>
		            <?php echo $form->error($riskScore, 'state'); ?>
                </div>

                <?php
                
                echo $form->radioButtonListRow($riskScore, 'geocoded', array(0 => 'No', 1 => 'Yes'));

                echo $form->checkBoxListRow($riskScore, 'version_id', $riskScore->getRiskScoreVersions());
                
                ?>

            </div>
        </div>
        <div class="row-fluid">
            <div class="buttons">
		        <?php echo CHtml::submitButton('Search', array('class'=>'submit')); ?>
                <?php echo CHtml::button('Reset', array('class'=>'reset')); ?>
                <?php echo CHtml::button('Export CSV', array('class'=>'export')); ?>
	        </div>
        </div>
    </div>
    <?php $this->endWidget(); ?>
</div>