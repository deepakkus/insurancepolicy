<?php

/* @var $this RiskStateMeansController */
/* @var $model RiskStateMeans */

$this->breadcrumbs = array(
	'State Means'
);

Yii::app()->format->datetimeFormat = 'Y-m-d H:i';

?>

<h1>Risk State Means</h1>

<a class="btn btn-success marginTop10" href="<?php echo $this->createUrl('/riskStateMeans/create'); ?>">New State Mean</a>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'risk-state-means-grid',
	'dataProvider' => $model->search(),
	'filter' => $model,
	'columns' => array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}{delete}'
        ),
		'mean',
		'std_dev',
		'stateAbbr',
        'version',
		array(
            'name' => 'date_created',
            'type' => 'datetime',
            'filter' => false
        )
	)
));
