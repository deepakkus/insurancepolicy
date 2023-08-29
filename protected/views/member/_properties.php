
<h2>Client Member Properties:</h2>

<?php if(in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types)): ?>

<?php echo CHtml::link('Add Property', array('property/create', 'member_mid'=>$member->mid), array('class'=>'btn btn-info')); ?>

<?php endif; ?>

<?php

$route = 'property/update';
if(isset($readOnly) && $readOnly == true){
    $route = 'property/view';
}

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'property-grid',
	'dataProvider'=>$properties->search(),
	'columns'=>array(
		array(
			'class'=>'CLinkColumn',
			'label'=>'Edit',
			'urlExpression'=>'"index.php?r='.$route.'&pid=".$data->pid',
			'header'=>'Edit',
		),
		array('name'=>'member_name', 'value'=>'$data->member->first_name." ".$data->member->last_name', 'htmlOptions'=>array('width'=>'100%'),),
		'address_line_1',
		'city',
		'state',
		'geo_risk',
		'policy',
		'policy_status',
		'response_status',
		'fireshield_status',
		'pre_risk_status',
	),
));

?>
