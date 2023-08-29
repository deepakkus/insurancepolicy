<?php
    echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/member/admin.js');

    $this->breadcrumbs=array(
        'Members'=>array('admin'),
        'Manage',
    );
?>

<h1>Manage Client Members</h1>

<div class ="row-fluid">

    <?php if (!Yii::app()->user->isGuest && (in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types))): ?>

    <?php echo CHtml::link('Add New Client Member', array('member/create'), array('class'=>'btn btn-info')); ?> 
    <?php echo CHtml::link('Generate Trial Members', array('member/trialGenerator'), array('class'=>'btn btn-info')); ?>

    <?php endif; ?>

</div>

<div class ="row-fluid" style="padding-top:25px;">

<?php 
    echo CHtml::link('Columns','#',array('class'=>'column-toggle paddingRight20')); 
    echo CHtml::link('Advanced Search','#',array('class'=>'search-toggle paddingRight20'));
    
    echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow, 'pageSize' => $pageSize));
    echo $this->renderPartial('_adminAdvancedSearch', array('members' => $members, 'advSearch' => $advSearch));

    ?>

</div>

    <?php

    $columnArray = array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{view}',
            'buttons' => array(
                'view' => array(
                    'url' => '$this->grid->controller->createUrl("/member/view", array("mid"=>$data->mid))'
                )
            )
        )
    );

    foreach ($columnsToShow as $columnToShow)
    {
         $item = array(
            'name' => $columnToShow,
            'value' => '$data-> ' .$columnToShow, 
        );
         
         if ($columnToShow === 'is_tester' || $columnToShow === 'status_override')
         {
             $item['type'] = 'boolean';
         }

         if ($columnToShow === 'client_id')
         {
             $item['value'] = '$data->client';
             $item['filter'] = CHtml::activeDropDownList($members, $columnToShow, CHtml::listData(Client::model()->findAll(array(
                 'select' => array('id','name')
             )), 'id', 'name'), array(
                 'prompt' => ''
             ));
         }
         else if ($columnToShow === 'type_id')
         {
                 $item['value'] = '$data->type->type';
                 $item['filter'] = CHtml::activeDropDownList($members, $columnToShow, CHtml::listData(PropertiesType::model()->findAll(array(
                     'select' => array('id','type')
                 )), 'id', 'type'), array(
                     'prompt' => ''
                 ));
         }
         
         $columnArray[] = $item;
    }

    $dataProvider = $members->search($advSearch, $pageSize, $sort);

?>

<div class ="table-responsive">

<?php
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'member-grid',
        'dataProvider'=>$dataProvider,
        'filter'=>$members,
        'columns'=>$columnArray,
    ));
?>

</div>
