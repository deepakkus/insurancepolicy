<?php
/* @var $this EngResourceOrderController */
/* @var $model EngResourceOrder */

$this->breadcrumbs=array(
	'Engines'=>array('engEngines/index'),
	'Engine Scheduling'=>array('engScheduling/admin'),
    'Resource Orders'
);

Yii::app()->clientScript->registerScriptFile('/js/engResourceOrder/admin.js',CClientScript::POS_HEAD);
Yii::app()->format->dateFormat = 'Y-m-d H:i';

?>

<h1>Resource Orders</h1>

<a class="search-toggle paddingRight20" href="#">Advanced Search</a>
<a class="paddingRight20" href="<?php echo $this->createUrl('/engResourceOrder/admin', array('resetAdvSearch' => true)); ?>">Reset Advanced Search</a>

<?php echo $this->renderPartial('_adminAdvancedSearch', array(
    'advSearch' => $advSearch,
	'model'=>$model
)); ?>

<div class="marginTop10">
    <a class="btn btn-success" href="<?php echo $this->createUrl('/engResourceOrder/create'); ?>">Generate New RO</a>
</div>

<div class="table-responsive">

    <?php

    $dataProvider = $model->search($advSearch);

    $this->widget('zii.widgets.grid.CGridView', array(
    	'id'=>'eng-resource-order-grid',
    	'dataProvider'=>$dataProvider,
    	'filter'=>$model,
    	'columns'=>array(
    		array(
    			'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}',
                //'header' => 'Delete',
                'header' => 'Update',
                'visible' => in_array('Engine Manager',Yii::app()->user->types),
            ),
            array(
                'name' => 'id',
                'filter'=> CHtml::activeNumberField($model, 'id', array('min'=>0, 'max'=> 999999))
            ),
            array(
                'name' => 'user_name',
                'value' => '$data->user_name'
            ),
            array(
                'name' => 'date_ordered',
                'type' => 'date',
                'filter' => ''
            ),
            array(
                'header' => 'Engine',
                'name' => 'engineName',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'Assignment',
                'name' => 'engineAssignment',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'City',
                'name' => 'engineCity',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'State',
                'name' => 'engineState',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'Start Date',
                'name' => 'dateStart',
                'type' => 'html',
                'value'=>'date("Y-m-d",strtotime($data->dateStart))',
                'filter' => ''
            ),
            array(
                'header' => 'Clients',
                'name' => 'clients',
                'type' => 'html',
                'filter' => ''
            )
    	)
    ));

    ?>

</div>