<?php

echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/importFile/admin.js');

$this->breadcrumbs=array(
    'Import Files'=>array('admin'),
    'Manage',
);
Yii::app()->format->dateFormat = 'm/d/Y h:i:s';
?>

<h1>Manage Import Files</h1>

<?php if(in_array('Admin', Yii::app()->user->types)): ?>

    <?php echo CHtml::link('Add an Import File', array('importFile/create'), array('class'=>'btn btn-success')); ?> 
    <?php echo CHtml::link('Add a "one off" Import File', array('importFile/create', 'standard' => 0), array('class'=>'btn btn-info')); ?>

<?php endif; ?>

<?php

echo ' Downloads: '.CHtml::link('One Off Import File Template', array('importFile/downloadFile','fileName'=>'pif-template-non-standard.csv'));
echo ' | '.CHtml::link('One Off Logic Doc', array('importFile/downloadFile','fileName'=>'One-OffLogic.docx'));

?>

<?php

$columnArray = array(
    array(
        'class'=>'CButtonColumn', 
        'template'=>'{update}', 
        'header' => 'Actions',
        'buttons'=>array(
            'update'=>array(
                'url'=>'$this->grid->controller->createUrl("/importFile/update", array("id"=>$data->id))'
            )
        )
    ),
    'id',
    array(
        'name'=>'client',
        'header'=>'Client',
        'type'=>'raw',
        'value'=>function($data) { return (isset($data->clientName)) ? $data->clientName->name : 'n/a'; },
        'filter'=>''
    ),
    'type',
    'status',
    'details',
    'file_path',
    //'date_time',
    array(
        'name'=>'date_time',
        'type'=>'date',
        'filter' => CHtml::activeDateField($importFiles,'date_time',array('onkeydown' => 'return false')),
        )
);

$dataProvider = $importFiles->search($pageSize, $sort);

?>

<div class ="table-responsive">

<?php
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'importFile-grid',
        'dataProvider'=>$dataProvider,
        'filter'=>$importFiles,
        'columns'=>$columnArray,
    ));   

?>

</div>