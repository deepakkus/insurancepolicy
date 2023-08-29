<?php

if(Yii::app()->user->hasFlash('message'))
{
    echo '<div style="color:red">';
    echo Yii::app()->user->getFlash('message');
    echo '</div>';
}

$this->breadcrumbs=array(
	'Pre Risk' => array('admin'),
	'Metrics',
);

$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'post',
));

?>

    <h3>Select Call List Month(s), Year(s), and State(s)</h3>
    <div class="row-fluid">

<?php
//echo $form->dropDownList($model,'call_list_month',$model->months());
$options = array();
$selectedOptions = array();
foreach($model->months() as $option)
{
	if($option != '')
	{
		$options[$option] = $option;
		if($months != null)
		{
			if(in_array($option, $months))
			{
				$selectedOptions[$option] = array('selected'=>"selected");
			}
		}
	}
}
$htmlOptions = array();
$htmlOptions['options'] = $selectedOptions;
$htmlOptions['multiple'] = 'multiple';
$htmlOptions['size'] = '6';
$htmlOptions['name'] = 'months';
$htmlOptions['id'] = 'pr-metrics-months';

?>

    <div class ="span2">

<?php 

echo CHtml::activeDropDownList($model, 'call_list_month', $options, $htmlOptions);

?>

    </div>

<?php

$options = array();
$selectedOptions = array();
foreach($model->years() as $option)
{
	if($option != '')
	{
		$options[$option] = $option;
		if($years != null)
		{
			if(in_array($option, $years))
			{
				$selectedOptions[$option] = array('selected'=>"selected");
			}
		}
	}
}
$htmlOptions = array();
$htmlOptions['options'] = $selectedOptions;
$htmlOptions['multiple'] = 'multiple';
$htmlOptions['size'] = '6';
$htmlOptions['name'] = 'years';
$htmlOptions['id'] = 'pr-metrics-years';

?>

    <div class ="span2">

<?php 

echo CHtml::activeDropDownList($model, 'call_list_year', $options, $htmlOptions);

?>

    </div>

<?php

$options = array();
$selectedOptions = array();
foreach($model->states() as $option)
{
	if($option != '')
	{
		$options[$option] = $option;
		if($states != null)
		{
			if(in_array($option, $states))
			{
				$selectedOptions[$option] = array('selected'=>"selected");
			}
		}
	}
}
$htmlOptions = array();
$htmlOptions['options'] = $selectedOptions;
$htmlOptions['multiple'] = 'multiple';
$htmlOptions['size'] = '6';
$htmlOptions['name'] = 'states';
$htmlOptions['id'] = 'pr-metrics-states';

?>

    <div class ="span2">

<?php

echo CHtml::dropDownList('states', '', $options, $htmlOptions);

?>

    </div>
</div>
<div class ="row-fluid">
    <div class ="span3">

<?php

echo CHtml::submitButton('Get Metrics');

?>

    </div>

<?php

?>

    </div>

<?php if(isset($metrics)): ?>
	
	<br><h2>Summary Metrics For Criteria:</h2>
	<b>Month(s): <?php echo implode(', ', $months); ?><br>
	Year(s): <?php echo implode(', ', $years); ?><br>
	State(s): <?php echo implode(', ', $states); ?></b><br><br>
	
	<table border="1">
	    <tr><th>Totals</th><th>Count</th></tr>
	    <?php foreach($metricsStatuses as $status): ?>
		    <tr><td><?php echo $status; ?></td><td> <?php echo $metrics[$status]; ?></td></tr>
        <?php endforeach; ?>
	
	</table><br />

<?php	
endif;
$this->endWidget();
?>
