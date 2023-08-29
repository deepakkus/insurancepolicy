<?php
/* @var $this EngSchedulingController */
/* @var $model EngScheduling */

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Engine Scheduling' => array('admin'),
	'Create'
);
?>

<h1>Create Engine Schedule Entry</h1>

<?php

$this->widget('bootstrap.widgets.TbTabs', array(
    'type' => 'tabs',
    'id' => 'engine-tabs',
    'tabs' => array(
        array(
            'label' => 'Schedule Engine',
            'content' => $this->renderPartial('_form', array(
                'model' => $model
            ), true),
            'active' => true
        ),
        array(
            'label' => 'Engine Clients',
            'active' => false,
            'itemOptions' => array('class'=>'disabled')
        ),
        array(
            'label' => 'Employee Scheduling',
            'active' => false,
            'itemOptions' => array('class' => 'disabled')
        )
    )
));

Yii::app()->clientScript->registerScript('disable-bootstrap-tabs', '$("ul.nav-tabs li.disabled a").click(function() { return false; });');

?>