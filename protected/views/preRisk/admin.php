<?php
/* @var $this PreRiskController */
/* @var $model PreRisk */
echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/preRisk/admin.js');

if(Yii::app()->user->hasFlash('message'))
{
    echo '<div style="color:red">';
    echo Yii::app()->user->getFlash('message');
    echo '</div>';
}

$this->breadcrumbs=array(
	'Pre Risk' => array('admin'),
	'Manage',
);
?>

<h1>PreRisk - Member Search</h1>

<?php
    echo CHtml::link('Columns','#',array('class'=>'column-toggle paddingRight20')); 
    echo CHtml::link('Advanced Search','#',array('class'=>'search-toggle paddingRight20'));
    echo CHtml::link('Download CSV', array('preRisk/admin','download'=>true),array('class'=>'paddingRight20'));
    echo CHtml::link('Reset Filters','#', array('class'=>'reset-filters'));

    echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow, 'pageSize' => $pageSize));
    echo $this->renderPartial('_adminAdvancedSearch', array('model' => $model, 'advSearch' => $advSearch));

    
    $columnArray = array();

    //------------Button Columns for HA Review/Resouce/Followup ------------//
    if(in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types) || in_array('PR Reviser', Yii::app()->user->types) || in_array('PR Final', Yii::app()->user->types))
    {
        $columnArray[] = array('class'=>'CButtonColumn',
                            'template'=>'{update}',
                            'header'=>'Production',
                            'buttons' => array(
                                 'update' => array('url' => '$this->grid->controller->createUrl("/preRisk/update", array("id"=>$data->id,"type"=>"review"))',
                                     'label'=>"HA Review",
                                ),
                            ),
                        );
    }

    if(in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types) || in_array('PR Caller', Yii::app()->user->types))
    {
        $columnArray[] = array('class'=>'CButtonColumn',
                            'template'=>'{update}',
                            'header'=>'Follow Up',
                            'buttons' => array(
                                 'update' => array('url' => '$this->grid->controller->createUrl("/preRisk/update", array("id"=>$data->id,"type"=>"preRiskfollowUp"))',
                                     'label'=>"Follow Up",
                                ),
                            ),
                        );

        $columnArray[] = array('class'=>'CButtonColumn',
                            'template'=>'{update}',
                            'header'=>'Scheduling',
                            'buttons' => array(
                                 'update' => array('url' => '$this->grid->controller->createUrl("/preRisk/update", array("id"=>$data->id,"type"=>"resource"))',
                                     'label'=>"Resource",
                                ),
                            ),
                        );
    }

    foreach($columnsToShow as $columnToShow)
    {
		if(strpos($columnToShow, 'property_') !== FALSE)
        {
            $data_attr = str_replace('property_', 'property->', $columnToShow);
            $columnArray[] = array(
                'name'=>$columnToShow, 
                'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
            );
        }
        elseif(strpos($columnToShow, 'member_') !== FALSE && $columnToShow != 'ok_to_do_wo_member_present')
        {
            if($columnToShow == 'member_member_num')
                $data_attr = 'member->member_num';
            else
                $data_attr = str_replace('member_', 'member->', $columnToShow);
            
			if(in_array($data_attr, array('member->home_phone', 'member->work_phone', 'member->cell_phone')))
			{
				$columnArray[] = array(
					'name'=>$columnToShow, 
					'value'=>'(isset($data->'.$data_attr.')) ? preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $data->'.$data_attr.') : "";',
				);
			}
			else 
			{
				$columnArray[] = array(
					'name'=>$columnToShow, 
					'value'=>'(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
				);
			}
        }
		elseif(in_array($columnToShow, array_keys($model->getRecActions())))
		{
			$columnArray[] = array('name'=>$columnToShow, 'value'=>'(isset($data->'.$columnToShow.') && $data->'.$columnToShow.' == 1) ? "YES" : "NO";', 'filter'=>CHtml::activeDropDownList($model, $columnToShow, $model->yesNoBoolean(), array('empty'=>'')));
		}
		elseif($columnToShow == 'recommended_actions')
		{
			$columnArray[] = array('name'=>'allRecActions', 'value'=>'$data->allRecActions', 'type'=>'raw', 'filter'=>'');
		}
        elseif($columnToShow == 'id')
		{
			$columnArray[] = array(
					'name'=>$columnToShow, 
                    'filter' => CHtml::activeNumberField($model,'id',array('min'=>0, 'max'=>9999999999))
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

    $dataProvider = $model->search($advSearch, $pageSize, $sort);

    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'pre-risk-grid',
        'dataProvider'=>$dataProvider,
        'filter'=>$model,
        'columns'=>$columnArray,
        'ajaxType'=>'post',
        //'ajaxUpdate'=>false,
        )); 

?>

