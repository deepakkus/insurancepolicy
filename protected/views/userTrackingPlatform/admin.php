<?php

/* @var $this UserTrackingPlatformController */
/* @var $model UserTrackingPlatform */

$this->breadcrumbs = array(
    'User Tracking' => array('/userTracking/admin'),
    'Platforms' => array('admin'),
    'Manage',
);

?>

<h1>Manage User Tracking Platforms</h1>

<div>
    <a class="btn btn-success" href="<?php echo $this->createUrl('/userTrackingPlatform/create'); ?>">Create</a>
</div>

<div style="width:50%">
    <?php $this->widget('zii.widgets.grid.CGridView', array(
	    'id' => 'user-tracking-platform-grid',
	    'dataProvider' => $model->search(),
	    'filter' => $model,
	    'columns' => array(
		    array(
			    'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}'
            ),
		    array(
                'name' => 'id',
                'filter' => false
            ),
		    'platform'
	    )
    )); ?>
</div>
