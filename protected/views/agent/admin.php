<?php
    echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/agent/admin.js');

    $this->breadcrumbs=array(
        'Agents'=>array('admin'),
        'Manage',
    );
?>

<h1>Manage Agents</h1>

<?php 
    echo CHtml::link('Columns','#',array('class'=>'column-toggle paddingRight20')); 
    echo CHtml::link('Add an Agent', array('agent/create'), array('class'=>'paddingRight20'));
        
    echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow, 'pageSize' => $pageSize));
    
    $columnArray = array();
    $columnArray[] = array(
        'class'=>'CButtonColumn', 
        'template'=>'{update}', 
        'buttons'=>array(
            'update'=>array(
                'url'=>'$this->grid->controller->createUrl("/agent/update", array("id"=>$data->id))'
             )
         )
    );

    foreach($columnsToShow as $column)
    {
         $item = array(
            'name' => $column,
            'value' => '$data->'.$column, 
        );
         if ($column === 'id')
         {
             $item['filter'] = CHtml::activeNumberField($agents,'id',array('min'=>0,'max'=> 999999));

         }
         if ($column === 'is_tester' || $column == 'status_override')
         {
             $item['type'] = 'boolean';
         }

         if ($column === 'agent_type')
         {
             $item['filter'] = CHtml::activeDropDownList($agents,'agent_type', Agent::agentTypes(), array('prompt'=>''));
         }
         
         $columnArray[] = $item;
    }

    //var_dump($columnArray); die();

    $dataProvider = $agents->search($pageSize, $sort);
    
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'agent-grid',
        'dataProvider'=>$dataProvider,
        'filter'=>$agents,
        'columns'=>$columnArray,
    ));   