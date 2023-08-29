<?php 

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'fsReport-grid',
    'dataProvider'=>$dataProvider,
	'filter'=>$fsReports,
	'columns'=>array(
		array(
			'class'=>'CButtonColumn', 'template'=>'{update} | {delete}',
		),
        'id',
        array(
            'name' => 'status',
            'filter' => CHtml::activeTextField($fsReports, 'status', array('style' => 'width: 85px')),
        ),
        array(
            'name' => 'assigned_user_name',
            'header' => 'Assigned To',
            'filter' => CHtml::activeTextField($fsReports, 'assigned_user_name', array('style' => 'width: 100px')),
        ),
		array(
            'name' => 'submit_date',
            'value'=>'(isset($data->submit_date)) ? date("m/d/Y h:i A", strtotime($data->submit_date)) : "";',
        ),
		array(
            'name' => 'due_date',
  			//'filter'=> CHtml::activeTextField($fsReports, 'due_date', array('style' => 'width: 130px')),
        ),
		array(
            'name' => 'scheduled_call',
            'value'=>'(isset($data->scheduled_call)) ? date("m/d/Y h:i A", strtotime($data->scheduled_call)) : "";',
  			//'filter'=> CHtml::activeTextField($fsReports, 'scheduled_call', array('style' => 'width: 130px')),
        ),
		array(
            'name'=>'property', 
            'header' => 'Property Member Name',
            'value'=>'(isset($data->property->member)) ? $data->property->member->first_name." ".$data->property->member->last_name : "";',
        ),
        array(
            'name' => 'address_line_1',
        ),
		array(
            'name' => 'city',
            //'filter' => CHtml::activeTextField($fsReports, 'city', array('style' => 'width: 100px')),
        ),
		array(
            'name' => 'state',
            //'filter' => CHtml::activeTextField($fsReports, 'state', array('style' => 'width: 30px')),
        ),
	),
));
 
?>