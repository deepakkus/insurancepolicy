<?php
echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/agentProperty/admin.js');

if(Yii::app()->user->hasFlash('message'))
{
    echo '<div style="color:red">';
    echo Yii::app()->user->getFlash('message');
    echo '</div>';
}

$this->breadcrumbs=array(
	'Agent Properties' => array('admin'),
	'Manage',
);
?>

<h1>Manage Agent Properties</h1>

<?php
    echo CHtml::link('Columns','#', array('class'=>'column-toggle paddingRight20')); 
    //echo CHtml::link('Advanced Search','#', array('class'=>'search-toggle paddingRight20'));
    //echo CHtml::link('Download CSV', array('property/admin', 'download'=>true), array('class'=>'paddingRight20'));    

    echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow, 'pageSize' => $pageSize));
    //echo $this->renderPartial('_adminAdvancedSearch', array('properties' => $properties, 'advSearch' => $advSearch));    
    
    $columnArray = array();

    $columnArray[] = array('class'=>'CButtonColumn', 'template'=>'{update}', 'buttons'=>array('update'=>array('url'=>'$this->grid->controller->createUrl("/agentProperty/update", array("id"=>$data->id,))',)));
    foreach($columnsToShow as $columnToShow)
    {
        if(strpos($columnToShow, 'agent_') !== FALSE)
        {
            if($columnToShow == 'agent_agent_num')
                $data_attr = 'agent->agent_num';
            else
                $data_attr = str_replace('agent_', 'agent->', $columnToShow);
            $item = array(
                'name'=>$columnToShow, 
                'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
            );
           if($columnToShow == 'agent_id')
           {
                $item['filter'] = CHtml::activeNumberField($agentProperties, 'agent_id', array('min'=>0,'max'=> 999999));
           }
           
           $columnArray[] = $item;
        }
         
        else 
        {
            $item = array(
                'name'=>$columnToShow,
                'value'=>'$data->'.$columnToShow, 
            );
           if($columnToShow == 'geo_risk')
           {
                $item['filter'] = CHtml::activeNumberField($agentProperties, 'geo_risk', array('min'=>0,'max'=> 99));
           } 
           $columnArray[] = $item;
        }
    }

    $dataProvider = $agentProperties->search($pageSize, $sort);

    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'agent-property-grid',
        'dataProvider'=>$dataProvider,
        'filter'=>$agentProperties,
        'columns'=>$columnArray,
        ));
?>