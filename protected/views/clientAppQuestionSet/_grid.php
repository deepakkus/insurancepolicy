<?php
echo "<br><h2>Client App Question Sets:</h2>";
echo CHtml::link('Add New Question Set', array('clientAppQuestionSet/create', 'client_id'=>$client->id));
Yii::app()->format->booleanFormat = array('No', 'Yes');
$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'client-questions-grid',
	'dataProvider'=>$clientAppQuestionSets->search(),
    'filter'=>$clientAppQuestionSets,
	'columns'=>array(
		array(
			'class'=>'CLinkColumn',
			'label'=>'Edit',
			'urlExpression'=>'"index.php?r=clientAppQuestionSet/update&id=".$data->id',
			'header'=>'Edit',
		),
		'name',
        array(
            'name' => 'active',
            'filter' => CHtml::activeDropDownList($clientAppQuestionSets, 'active', array('1' => 'Yes', '0' => 'No'), array('prompt' => '')),
            'type' => 'boolean'
        ),
        array(
            'name' => 'is_default',
            'filter'=> CHtml::activeNumberField($clientAppQuestionSets, 'is_default', array('min'=>0, 'max' => 255))
        ),
        array(
            'name' => 'default_level',
            'filter'=> CHtml::activeNumberField($clientAppQuestionSets, 'default_level', array('min'=>0, 'max' => 255))
        )
	)
));
?>
