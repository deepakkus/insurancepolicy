<?php

/* @var $this ResPerimetersController */
/* @var $model ResPerimeters */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerScriptFile('/js/resPerimeters/form.js', CClientScript::POS_END);

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'res-perimeters-form',
    'type' => 'vertical',
    'htmlOptions' => array(
        'class' => 'well',
        'enctype' => 'multipart/form-data'
    )
));

echo $form->errorSummary($model);

echo $form->fileFieldRow($model, 'kmlFileUpload', array(
    'accept'=>'.kml,.kmz',
    'required' =>true,
    'label' => true
));

?>
<div class="control-group" id="button-row" style="padding-top:30px;">
    <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'submit')); ?>
    <span class="paddingLeft10">
        <?php echo CHtml::link('Cancel', array('resFireName/admin')); ?>
    </span>
</div>

<?php
$this->endWidget();
?>

