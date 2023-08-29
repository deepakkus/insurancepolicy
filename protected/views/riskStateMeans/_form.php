<?php

/* @var $this RiskStateMeansController */
/* @var $model RiskStateMeans */
/* @var $form CActiveForm */

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'risk-state-means-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

echo $form->errorSummary($model);

echo $form->numberFieldRow($model, 'mean', array(
    'step' => 'any',
    'min' => 0,
    'placeholder' => 'Enter a mean'
));

echo $form->numberFieldRow($model, 'std_dev', array(
    'step' => 'any',
    'min' => 0,
    'placeholder' => 'Enter a std dev'
));

$stateData = CHtml::listData(GeogStates::model()->findAll(array(
    'select' => 'id, abbr',
    'order' => 'abbr ASC'
)), 'id', 'abbr');

echo $form->dropDownListRow($model, 'state_id', $stateData, array(
    'prompt' => 'Select a state'
));

$versionData = CHtml::listData(RiskVersion::model()->findAll(array(
    'select' => 'id, version',
    'order' => 'id DESC'
)), 'id', 'version');

echo $form->dropDownListRow($model, 'version_id', $versionData, array(
    'prompt' => 'Select a risk version'
));

?>

<div class="buttons marginTop20">
	<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class'=>'submit')); ?>
    <span class="paddingLeft10">
        <?php echo CHtml::link('Cancel', array('admin')); ?>
    </span>
</div>

<?php $this->endWidget(); ?>
