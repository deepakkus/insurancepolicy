<?php

/* @var $this EngShiftTicketStatusTypeController */
/* @var model $shiftTicketStatusType */

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Manage Shift Tickets' => array('engShiftTicket/admin'),
    'Manage Shift Tickets Status Types'
);

Yii::app()->format->booleanFormat = array('&#x2716;', '&#x2713;');

?>

<h1>Manage Engine Shift Ticket Status Type</h1>


    <div class="marginTop10">
        <a class="btn btn-success" href="<?php echo $this->createUrl('/engShiftTicketStatusType/create'); ?>">Create New Engine Shift Ticket Status Type</a>
    </div>

<div class="table-responsive">

    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
	    'id'=>'eng-enginesstatustype-grid',
	    'dataProvider'=>$shiftTicketStatusType->search(),
	    'filter'=>$shiftTicketStatusType,
	    'columns'=>array(
		    array(
			    'class'=>'bootstrap.widgets.TbButtonColumn',
                'template'=>'{update}{delete}',
                'header' => 'Actions',
                'buttons' => array(
                    'delete' => array(
                        'visible' => 'false'
                    )
                ),                
		    ),           
		    'type',
		    'order',
		     array(
                'name' => 'disabled',
                'type' => 'boolean',
                'filter' => CHtml::activeDropDownList($shiftTicketStatusType,'disabled', array(0 => '&#x2716;', 1 => '&#x2713;'), array('encode'=>false,'prompt'=>''))
        ),	
                	    
	    )
    )); ?>

</div>

