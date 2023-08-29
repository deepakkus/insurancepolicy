<?php
/* @var $this ClientDedicatedHoursController */
/* @var $model ClientDedicatedHours */

$this->breadcrumbs=array(
    'Client Dedicated Hours' => array('admin'),
    'Manage',
);

Yii::app()->format->dateFormat = 'Y-m-d';

?>

<h2>Dedicated Hours</h2>

<a class="btn btn-success" href="<?php echo $this->createUrl('/clientDedicatedHours/create'); ?>">New Dedicated Hours Pool</a>

<?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
    'id' => 'client-dedicated-hours-grid',
     'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
    'dataProvider' => $model->search(),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}{delete}',
            'header' => 'Actions'
        ),
        array(
            'name' => 'name',
        ),
        array(
            'name' => 'clientNames',
            'value' => 'implode(", ", $data->clientNames)',
            'filter' => false
        ),
        'dedicated_hours',
        array(
            'name' => 'dedicated_start_date',
            'type' => 'date',
            'filter' => false
        ),
        array(
            'name' => 'notes',
            'filter' => false
        )
    )
)); ?>
