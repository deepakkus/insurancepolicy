<?php 

$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'unmatched-form',
    'htmlOptions' => array(
        'class' => 'text-left',
        'onsubmit' => new CJavaScriptExpression('
            window.location.href = "' . Yii::app()->createAbsoluteUrl("property/downloadUnmatchedByClient") . '&" + $(this).serialize();
            return false;
        ')
    )
));

$this->widget('bootstrap.widgets.TbSelect2', array(
    'asDropDownList' => true,
    'name' => 'clientID',
    'data' => Helper::getFireClients(),
));

echo CHtml::submitButton('Download Unmatched', array('class' => 'submit marginTop20', 'style' => 'display: block'));

$this->endWidget();