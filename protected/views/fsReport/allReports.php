<?php
Yii::app()->format->dateFormat = 'm/d/Y';
echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/fsReport/admin.js');

if(Yii::app()->user->hasFlash('message'))
{
    echo '<div style="color:red">';
    echo Yii::app()->user->getFlash('message');
    echo '</div>';
}

echo '<h1>Manage App Reports</h1>';

echo CHtml::link('Columns','#',array('class'=>'column-toggle paddingRight20')); 
echo CHtml::link('Advanced Search','#',array('class'=>'search-toggle paddingRight20'));    
echo CHtml::link('Clear Settings', array('fsReport/allReports', 'clear_settings'=>1));

echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow, 'pageSize' => $pageSize, 'advSearch' => $advSearch,));
echo $this->renderPartial('_adminAdvancedSearch', array('fsReports' => $fsReports, 'advSearch' => $advSearch));

$columnArray = array();

if(in_array('Admin',Yii::app()->user->types))
    $columnArray[] = array('class'=>'CButtonColumn','template'=>'{update} <br><br> {delete}');
else
    $columnArray[] = array('class'=>'CButtonColumn','template'=>'{update}');

foreach($columnsToShow as $columnToShow)
{
    if(strpos($columnToShow, 'assigned_user_') !== FALSE)
    {
        $data_attr = str_replace('assigned_user_', 'assigned_user->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif(strpos($columnToShow, 'agent_property_') !== FALSE)
    {
        $data_attr = str_replace('agent_property_', 'agent_property->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif($columnToShow == 'fs_user_client_name')
    {
        $columnArray[] = array(
            'name'=> 'client_name', 
            'header'=>'Client',
            'value'=>'(isset($data->client->name)) ? $data->client->name : "";',
        );
    }
    elseif(strpos($columnToShow, 'fs_user_') !== FALSE)
    {
        $data_attr = str_replace('fs_user_', 'fs_user->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif(strpos($columnToShow, 'property_') !== FALSE)
    {
        $data_attr = str_replace('property_', 'property->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif(strpos($columnToShow, 'member_') !== FALSE)
    {
        if($columnToShow == 'member_member_num')
            $data_attr = 'member->member_num';
        else
            $data_attr = str_replace('member_', 'member->', $columnToShow);
        
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif(strpos($columnToShow, 'agent_') !== FALSE)
    {
        if($columnToShow == 'agent_agent_num')
            $data_attr = 'agent->agent_num';
        else
            $data_attr = str_replace('agent_', 'agent->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif(strpos($columnToShow, 'client_') !== FALSE)
    {
        $data_attr = str_replace('client_', 'client->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif(strpos($columnToShow, 'pre_risk_') !== FALSE)
    {
        $data_attr = str_replace('pre_risk_', 'pre_risk->', $columnToShow);
        $columnArray[] = array(
            'name'=>$columnToShow, 
            'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
        );
    }
    elseif($columnToShow =='completeDate')
    {
        $columnArray[] = array(
            'name'=>$columnToShow,
            'value'=>'$data->getCompleteDate()',
        );
    }
     elseif($columnToShow =='submit_date')
    {
        $columnArray[] = array(
            'name'=>'submit_date', 
            'type' => 'date',
            'filter' => CHtml::activeDateField($fsReports,'submit_date',array('min'=>'2012-12-11', 'onkeydown' => 'return false')),
        );
    }
    elseif($columnToShow =='due_date')
    {
        $columnArray[] = array(
            'name'=>'due_date', 
            'type' => 'date',
            'filter' => CHtml::activeDateField($fsReports,'due_date',array('min'=>'2016-01-07', 'onkeydown' => 'return false')),
        );
    }
    elseif($columnToShow == 'type')
    {
        $columnArray[] = array(
            'name' => 'type',
            'filter' => CHtml::activeDropDownList($fsReports,'type',$fsReports->getTypes(), array('empty'=>'')),
            'value' => '$data->getTypeLabel()',

        );
    }
    elseif($columnToShow == 'status')
    {
        $columnArray[] = array(
            'name' => 'status',
            'filter' => CHtml::activeDropDownList($fsReports,'status',$fsReports->getStatuses(), array('empty'=>'')),
            'value' => '$data->status',

        );
    }
    elseif($columnToShow == 'id')
    {
        $columnArray[] = array(
            'name' => 'id',
           'filter'=> CHtml::activeNumberField($fsReports, 'id', array('min'=>0,'max'=> 999999))
        );
    }
    elseif($columnToShow == 'scheduled_call')
    {
        $columnArray[] = array(
            'name' => 'scheduled_call',
            'type' => 'date',
            'filter' => CHtml::activeDateField($fsReports,'scheduled_call',array('min'=>'2013-10-29', 'onkeydown' => 'return false')),
        );
    }
    else 
    {
        $columnArray[] = array(
            'name'=>$columnToShow,
            'value'=>'$data->'.$columnToShow, 
        );
    }
}

$dataProvider = $fsReports->search($advSearch, $pageSize, $sort);

$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'fsReport-grid',
    'dataProvider'=>$dataProvider,
    'filter'=>$fsReports,
    'columns'=>$columnArray,
    'ajaxType'=>'POST',
    'ajaxUpdate'=>true,
    ));
?>