<?php

/* @var $this PropertiesTypeController */
/* @var $model PropertiesType */

$this->breadcrumbs=array(
	'Properties' => array('property/admin'),
    'Properties Types'=>array('admin'),
	'Manage'
);

?>

<h1>Property Types</h1>

<a class="btn btn-success marginTop10" href="<?php echo $this->createUrl('/propertiesType/create'); ?>">New Property Type</a>

<div style="width: 400px;">
    <?php $this->widget('zii.widgets.grid.CGridView', array(
	    'id' => 'properties-type-grid',
	    'dataProvider' => $model->search(),
	    'filter' => $model,
	    'columns' => array(
            array(
                'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}'
            ),
            'id',
		    array(
                'name' => 'type',
                'htmlOptions' => array('class'=>'grid-column-width-100'),
            )
	    )
    )); ?>
</div>

