<?php
    echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/fsReport/admin.js');

    if(Yii::app()->user->hasFlash('message'))
    {
        echo '<div style="color:red">';
        echo Yii::app()->user->getFlash('message');
        echo '</div>';
    }

    $this->breadcrumbs=array(
        'Reports' => array('allReports'),
        'Manage',
    );

	if($advSearch['types'] == 'fs')
		echo '<h1>Manage FireShield Reports</h1>';
	elseif($advSearch['types'] == 'agent')
		echo '<h1>Manage Agent Reports</h1>';

    echo CHtml::link('Columns','#',array('class'=>'column-toggle paddingRight20')); 
    echo CHtml::link('Advanced Search','#',array('class'=>'search-toggle paddingRight20'));
    //echo CHtml::link('Download CSV', array('fsReport/download'),array('class'=>'paddingRight20'));    

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
		elseif(strpos($columnToShow, 'client_') !== FALSE && $columnToShow != 'fs_user_client_name')
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
        'ajaxUpdate'=>false,
        ));
?>