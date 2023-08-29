<?php

/* @var $this ResDedicatedAgencyController */
/* @var $model ResDedicatedAgency */

$this->breadcrumbs=array(
    'Engines'=>array('/engEngines/index'),
	'Manage Agency Visits',
);

Yii::app()->format->dateFormat = 'Y-m-d';
Yii::app()->format->numberFormat = array('decimals'=>6, 'decimalSeparator'=>'.', 'thousandSeparator'=>'');

Yii::app()->clientScript->registerCss('crewPictureCSS','
@media (min-width: 660px) {
    .btn-engine:nth-child(2) {
        margin-left: 10px;
    }
    .btn-engine:last-child {
        float: right;
    }
}
@media (max-width: 660px) {
    .btn-engine {
        margin-top: 10px;
    }
}
');

?>

<h1>Manage Dedicated Agency Visits</h1>

<div>
    <a class="btn btn-engine btn-success" href="<?php echo $this->createUrl('/resDedicatedAgency/create'); ?>">Create New Agency Visit</a>
    <a class="btn btn-engine btn-info" href="<?php echo $this->createUrl('/resDedicatedAgency/search'); ?>">Search Agency Visits</a>
    <a class="btn btn-engine btn-info" href="<?php echo $this->createUrl('/resDedicatedAgency/downloadNewAgencyPdf'); ?>" target="_blank">
        <i class="icon-download-alt"></i>Agency Visit Form
    </a>
</div>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	    'id' => 'res-dedicated-agency-grid',
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped bordered condensed',
	    'dataProvider' => $model->search(),
	    'filter' => $model,
	    'columns' => array(
		    array(
			    'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}{delete}',
                'header' => 'Actions'
		    ),
            'name',
            'city',
            array(
                'name' => 'state',
                'filter' => CHtml::activeDropDownList($model,'state',CHtml::listData(ResDedicatedAgency::model()->findAll(array(
                    'select' => 'state',
                    'distinct' => true,
                    'order' => 'state'
                )),'state','state'), array('prompt' => ''))
            ),
		    'contact_name',
		    //'contact_phone_1',
		    //'contact_phone_2',
		    //'email:email',
            array(
                'name' => 'agencyComment',
                'htmlOptions' => array('class' => 'grid-column-width-300')
            ),
		    'wds_contact',
            array(
                'name' => 'last_contact_date',
                'type' => 'date',
                'filter' => false
            ),
		    //'lat:number',
            //'lon:number',
            array(
                'class' => 'CLinkColumn',
                'header' => 'Location',
                'label' => 'Map',
                'linkHtmlOptions' => array('target' => '_blank'),
                'urlExpression' => '"https://maps.google.com/maps?q=" . $data->lat . ", " . $data->lon'
            ),
            array(
                'name' => 'client_id',
                'value' => 'isset($data->client->name) ? $data->client->name : ""',
                'filter' => CHtml::activeDropDownList($model, 'client_id', CHtml::listData(Client::model()->findAll(array(
                    'select' => 'id, name',
                    'order' => 'name ASC'
                )), 'id', 'name'), array('empty' => ''))
            )
	    )
    )); ?>

</div>