<?php
$dataProvider=new CActiveDataProvider('FSReport', array(
    'criteria'=>array(
        'condition'=>'pre_risk_id = '.$pre_risk->id,
        'with'=>array('agent_property', 'agent', 'assigned_user'),
    ),
    'countCriteria'=>array(
        'condition'=>'pre_risk_id = '.$pre_risk->id,
    ),
    'pagination'=>array(
        'pageSize'=>20,
    ),
));
if($dataProvider->itemCount > 0)
{
	$this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'agent-reports-grid',
		'dataProvider'=>$dataProvider,
		'summaryText'=>'Agent App Reports:',
		'template'=>"<div style=\"font-weight:bold;float:left;padding-top:50px;\">{summary}</div>\n{items}",
		'columns'=>array(
			array(
				'class'=>'CLinkColumn',
				'label'=>'View',
				'urlExpression'=>'"index.php?r=fsReport/update&id=".$data->id',
				'header'=>'Edit',
				'linkHtmlOptions'=>array('target'=>'_blank'),
			),
            'id',
			'status',
			'status_date',
			'due_date',
			'agent.first_name',
            'agent.last_name',
            'assigned_user.first_name',
            'assigned_user.last_name',
		),
	));
}

?>

