<?php

/* @var $this WdsStatesController */
/* @var $model WDSStates */

$this->breadcrumbs = array(
	'WDS States' => array('update'),
	'Update'
);

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'wdsstates-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

?>

<div class="rows-fluid">
    <div class="compactRadioGroup" style="width:600px;">
        <ul style="columns: 4; -webkit-columns: 4; -moz-columns: 4;">
            <?php echo CHtml::checkBoxList('WDSStates[state_id]', $wdsstates, $stateformdata, array(
                'template' => '{beginLabel} {input} {labelTitle} {endLabel}',
                'labelOptions' => array('class' => 'checkbox', 'style' => 'display: inline-block;')
            )); ?>
        </ul>
    </div>
</div>

<?php

echo CHtml::submitButton('Save', array('class' => 'submit marginTop20', 'style' => 'display: block;'));

$this->endWidget();
unset($form);