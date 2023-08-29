<?php

/* @var EngShiftTicketActivityTypeController $this */
/* @var EngShiftTicketActivityType $engShiftTicketActivityType */

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Manage Shift Tickets' => array('engShiftTicket/admin'),
    'Manage Shift Tickets Activity Types'
);

Yii::app()->format->booleanFormat = array('&#x2716;', '&#x2713;');

?>

<h1>Manage Engine Shift Ticket Activity Type</h1>

    <div class="marginTop10">
        <a class="btn btn-success" href="<?php echo $this->createUrl('/engShiftTicketActivityType/create'); ?>">Create New Engine Shift Ticket Activity Type</a>
    </div>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
	    'id'=>'eng-enginesstatustype-grid',
	    'dataProvider'=>$engShiftTicketActivityType->search(),
	    'filter'=>$engShiftTicketActivityType,
	    'columns'=>array(
		    array(
			    'class'=>'bootstrap.widgets.TbButtonColumn',
                'template'=>'{update}',
                'header' => 'Actions',                
		    ),           
		    'type',
		     array(
                'name' => 'active',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($engShiftTicketActivityType,'active', array(0 => '&#x2716;', 1 => '&#x2713;'), array('encode'=>false,'prompt'=>''))
            ),
            'description'
	    )
    )); ?>

</div>

