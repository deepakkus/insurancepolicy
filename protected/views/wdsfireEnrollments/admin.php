<?php

/* @var $this WdsfireEnrollmentsController */
/* @var $model WdsfireEnrollments */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Enrollments'
);

Yii::app()->format->dateFormat = 'Y-m-d H:i';

?>

<h1>Enrollments</h1>

<div class ="table-responsive">
<?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
    'cssFile' => '../../css/wdsExtendedGridView.css',
    'type' => 'striped hover condensed',
    'id' => 'wdsfire-enrollments-grid',
    'dataProvider' => $dataProvider,
    'filter' => $model,
    'columns' => array(
        'user_name',
        'client_name',
        array(
			'name' => 'pid',
			'filter' => CHtml::activeNumberField($model, 'pid'),
		),
        'member_first_name',
        'member_last_name',
        'property_address_1',
        'property_city',
        'property_state',
        'property_zip',
        'fire_name',
        'status_type',
        array(
            'name' => 'date',
            'filter' => CHtml::activeDateField($model,'date'),
            'type' => 'date'
        )
    )
)); ?>
</div>