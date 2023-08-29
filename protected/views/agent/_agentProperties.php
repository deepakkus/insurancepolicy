<?php
echo "<h2>Agent Properties:</h2>";
echo CHtml::link('Add New Agent Property', array('agentProperty/create', 'agentID'=>$agent->id));
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'agent-property-grid',
	'dataProvider'=>$agentProperties->search(),
	'columns'=>array(
		array(
			'class'=>'CLinkColumn',
			'label'=>'Edit',
			'urlExpression'=>'"index.php?r=agentproperty/update&id=".$data->id',
			'header'=>'Edit',
		),
		//array('name'=>'agent_name', 'value'=>'$data->agent->first_name." ".$data->agent->last_name', 'htmlOptions'=>array('width'=>'100%'),),
        'status',
		'address_line_1',
		'city',
		'state',
		'geo_risk',
	),
));

?>
