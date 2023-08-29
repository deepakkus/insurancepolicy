<?php
/* @var $this ResThreatController */
/* @var $model ResThreat */
/* @var $form CActiveForm */

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'res-threat-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well',
        'enctype' => 'multipart/form-data'
    )
));

echo $form->errorSummary($model);

?>

<div class="control-group">
    <div class="control-label">
        <?php echo CHtml::image('images/kmz-medium.png', 'KML'); ?>
    </div>
</div>

<?php

echo $form->fileFieldRow($model, 'kmlFileUpload', array(
    'accept'=>'.kml,.kmz'
));

?>

<div class="marginTop20">
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'submit')); ?>
    <span class="paddingLeft10">
        <?php echo CHtml::link('Cancel', array('resFireName/admin')); ?>
    </span>
</div>

<?php

$this->endWidget();