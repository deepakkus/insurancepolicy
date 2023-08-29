<?php

$this->breadcrumbs=array(
	'Properties' => array('admin'),
	'Manage',
);

echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/property/admin.js');

Yii::app()->format->dateFormat = 'Y-m-d H:i';

if (Yii::app()->user->hasFlash('message'))
{
    echo '<div style="color:red">';
    echo Yii::app()->user->getFlash('message');
    echo '</div>';
}

$datesArray = array(
    'fs_status_date',
    'pr_status_date',
    'res_status_date',
    'policy_status_date',
    'fsOfferedDate',
    'fsEnrolledDate',
    'policy_effective',
    'policy_expiration',
    'transaction_effective',
    'last_update',
    'wds_geocode_date'
);

?>
<h1>Manage Properties / Policies</h1>

<div class="marginTop20">
    <a class="column-toggle btn-primary" style="padding:5px 10px 5px 10px" href="#">Columns</a>
    <?php
        $buttonColor = '';
        foreach($advSearch as $searchField)
        {
            if(!empty($searchField))
            {
                $buttonColor = 'color:red;';
            }
        }
    
        echo '<a class="search-toggle btn-primary" style="padding:5px 10px 5px 10px;'.$buttonColor.'" href="#">Advanced Search</a>';
    ?>
    <a class="reset-prop-grid btn-primary" style="padding:5px 10px 5px 10px" href="<?php echo $this->createUrl('property/admin',array('reset'=>1)); ?>">Reset</a>
    <div class="floatRight">
        <?php if (in_array('Admin', Yii::app()->user->types)): ?>
        <a href="<?php echo $this->createUrl('propertiesType/admin'); ?>">Property Types</a>
        <?php endif; ?>
    </div>
</div>

<?php

    echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow, 'pageSize' => $pageSize));
    echo $this->renderPartial('_adminAdvancedSearch', array('properties' => $properties, 'advSearch' => $advSearch));

    $columnArray = array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{view}',
            'buttons' => array(
                'view' => array(
                    'url' => '$this->grid->controller->createUrl("property/view", array("pid"=>$data->pid))'
                )
            )
        )
    );

    foreach ($columnsToShow as $columnToShow)
    {
        if ($columnToShow === 'member_mid')
        {
            $columnArray[array_search($columnToShow,$columnOrder)] = $columnToShow;
        }
        else if ($columnToShow === 'member_member_num')
        {
            $columnArray[array_search($columnToShow,$columnOrder)] = array(
                'name' => $columnToShow,
                'header' => 'PolicyHolder #',
                'value' => '(isset($data->member->member_num)) ? $data->member->member_num : "";'
            );
        }
        else if ($columnToShow === 'client_id')
        {
            $columnArray[array_search($columnToShow,$columnOrder)] = array(
                'name' => $columnToShow,
                'value' => '(isset($data->member->client)) ? $data->member->client : "";',
                'filter' => CHtml::activeDropDownList($properties, $columnToShow, CHtml::listData(Client::model()->findAll(array('select' => array('id','name'))), 'id', 'name'), 
                    array(
                        'prompt' => '',
                        'style'=>'width:75px',
                ))
            );
        }
        else if ($columnToShow === 'type_id')
        {
            $columnArray[array_search($columnToShow,$columnOrder)] = array(
                'name' => $columnToShow,
                'value' => '$data->property_type',
                'filter' => CHtml::activeDropDownList($properties, $columnToShow, CHtml::listData(PropertiesType::model()->findAll(array(
                    'select' => array('id','type')
                )), 'id', 'type'), array(
                    'prompt' => ''
                ))
            );
        }
        else if (strpos($columnToShow, 'member_') !== false)
        {
            $data_attr = str_replace('member_', 'member->', $columnToShow);
            $columnArray[array_search($columnToShow,$columnOrder)] = array(
                'name' => $columnToShow,
                'header' => ucwords(str_replace('member ', '', str_replace('_', ' ', $columnToShow))),
                'value' => '(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";'
            );
        }
        else if (strpos($columnToShow, 'property_access_') !== false)
        {
            $data_attr = str_replace('property_access_', 'res_property_access->', $columnToShow);
            $columnArray[array_search($columnToShow,$columnOrder)] = array(
                'name' => $columnToShow,
                'header' => ucwords(str_replace('property access ', '', str_replace('_', ' ', $columnToShow))),
                'value' => '(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
                'filter' => false
            );
        }
        else if (in_array($columnToShow, $datesArray))
        {
            $columnArray[array_search($columnToShow,$columnOrder)] = array(
                'name' => $columnToShow,
                'type' => 'date'
            );
        }
        else if (in_array($columnToShow, array('response_status','pre_risk_status','fireshield_status')))
        {
            $programStatuses = Property::model()->getProgramStatuses();
            $programStatuses = array_combine($programStatuses,$programStatuses);
            $columnArray[] = array(
                'name' => $columnToShow,
                'filter' => CHtml::activeDropDownList($properties,$columnToShow, $programStatuses, array('empty'=>'')),
            );
        }
        else if ($columnToShow === 'policy_status')
        {
            $policyStatuses = Property::model()->getPolicyStatuses();
            $policyStatuses = array_combine($policyStatuses,$policyStatuses);
            $columnArray[] = array(
                'name' => $columnToShow,
                'filter' => CHtml::activeDropDownList($properties,$columnToShow, $policyStatuses, array('empty'=>'')),
            );
        }
        else if ($columnToShow === 'state')
        {
            $columnArray[] = array(
                'name' => $columnToShow,
                'filter'=> CHtml::activeDropDownList($properties,$columnToShow,Helper::getStates(),array('empty'=>'','style'=>'width:47px')),
            );
        }
        else if ($columnToShow === 'wds_lob')
        {
            $lobTypes = Property::model()->getLOBTypes();
            $lobTypes = array_combine($lobTypes,$lobTypes);
            $columnArray[] = array(
                'name' => $columnToShow,
                'filter' => CHtml::activeDropDownList($properties,$columnToShow, $lobTypes, array('empty'=>'')),
            );
        }
        else
        {
            $columnArray[array_search($columnToShow,$columnOrder)] = $columnToShow;
        }
    }
    $dataProvider = $properties->search($advSearch, $pageSize, $sort, $pageSizeMethod);

?>

<div class ="table-responsive">
    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'id' => 'properties-grid',
        'type' => 'striped bordered',
        'fixedHeader' => false,
        'cssFile' => '/css/wdsExtendedGridView.css',
        'dataProvider' => $dataProvider,
        'filter' => $properties,
        'columns' => $columnArray
    )); ?>
</div>