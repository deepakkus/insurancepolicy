<?php
$dataProvider=new CActiveDataProvider('Property', array(
    'criteria'=>array(
        'condition'=>'member_mid = '.$pre_risk->property->member_mid.' AND pid != '.$pre_risk->property_pid,
        'with'=>array('member'),
    ),
    'countCriteria'=>array(
        'condition'=>'member_mid = '.$pre_risk->property->member_mid.' AND pid != '.$pre_risk->property_pid,
        // 'order' and 'with' clauses have no meaning for the count query
    ),
    'pagination'=>array(
        'pageSize'=>20,
    ),
));
if($dataProvider->itemCount > 0)
{
	$this->widget('zii.widgets.grid.CGridView', array(
		'id'=>'property-grid',
		'dataProvider'=>$dataProvider,
		'summaryText'=>'Other Member Properties',
		'template'=>"<div style=\"font-weight:bold;float:left;padding-top:50px;\">{summary}</div>\n{items}",
		'columns'=>array(
			array(
				'class'=>'CLinkColumn',
				'label'=>'View',
				'urlExpression'=>'"index.php?r=property/view&pid=".$data->pid',
				'header'=>'Edit',
				'linkHtmlOptions'=>array('target'=>'_blank'),
			),
			array('name'=>'member_name', 'value'=>'$data->member->first_name." ".$data->member->last_name', 'htmlOptions'=>array('width'=>'90%'),),
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
}

?>
