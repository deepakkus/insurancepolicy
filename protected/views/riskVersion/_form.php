<?php

/* @var $this RiskVersionController */
/* @var $model RiskVersion */

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'risk-version-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

echo $form->textFieldRow($model, 'version', array(
    'maxlength' => 10,
    'placeholder' => $model->getAttributeLabel('version')
));

echo $form->numberFieldRow($model, 'year_dataset', array(
    'min' => 1,
    'max' => 9999,
    'placeholder' => $model->getAttributeLabel('year_dataset')
));

echo $form->textAreaRow($model, 'comment', array(
    'rows' => 8,
    'maxlength' => 300,
    'class' => 'span6',
    'placeholder' => 'Any special details about this version of the risk database.',
    'hint' => 'Limited to 300 characters'
));

?>

<div>
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
    <span class="paddingLeft10">
        <?php echo CHtml::link('Cancel', array('riskVersion/versions')); ?>
    </span>
</div>

<?php $this->endWidget(); ?>