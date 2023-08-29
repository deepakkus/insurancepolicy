<?php

$this->breadcrumbs=array(
	'Properties' => array('admin'),
    $pid => array('property/view', 'pid' => $pid),
	'Attach file',
);

echo '<h1>Attach File to Property ' . $pid . '</h1>';

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'attach-file-form',
    'enableAjaxValidation' => false,
    'htmlOptions' => array(
        'class' => 'well',
        'enctype' => 'multipart/form-data'
    )
));

echo $form->fileFieldRow($model, 'upload', array('style' => 'margin-bottom: 20px;'));

echo $form->textAreaRow($model, 'notes', array('rows' => '8', 'maxlength' => '255', 'style' => 'width:500px;'));

if (!$model->isNewRecord)
{
    echo $form->hiddenField($model, 'property_pid');
    echo $form->hiddenField($model, 'file_id');

    if (isset($model->file))
    {
        echo '<h4>Current file: ' . $model->file->name . '</h4>';
    }
}

//echo CHtml::image('images/kmz-medium.png', 'KML');
//echo '<p><i>(Currently: show map view here? Maybe link to the monitor log vew for that entry?</i></p>';

echo '<div class="row-fluid buttons marginTop20">';
echo CHtml::submitButton('Save', array('class' => 'submit'));
echo '</div>';

$this->endWidget();