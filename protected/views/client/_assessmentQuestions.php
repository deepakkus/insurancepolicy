<?php
echo "<br><h2>Client Agent App Questions:</h2>";
echo CHtml::link('Add New Question', array('fsAssessmentQuestion/create', 'client_id'=>$client->id));

Yii::app()->format->booleanFormat = array('No', 'Yes');

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'client-agent-questions-grid',
	'dataProvider'=>$clientQuestions->search(),
    'filter'=>$clientQuestions,
	'columns'=>array(
		array(
			'class'=>'CLinkColumn',
			'label'=>'Edit',
			'urlExpression'=>'"index.php?r=fsAssessmentQuestion/update&id=".$data->id',
			'header'=>'Edit',
		),
		'title',
        array(
            'name' => 'active',
            'filter' => CHtml::activeDropDownList($clientQuestions, 'active', array('1' => 'Yes', '0' => 'No'), array('prompt' => '')),
            'type' => 'boolean'
        ),
        'type',
        'set_id',
		'question_num',
        'order_by',
        'label',
        'yes_points',
        'allow_notes',
		'section_type',
	),
));



?>