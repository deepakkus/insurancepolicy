<?php

/* @var $this EngShiftTicketController */
/* @var $filterData array */
/* @var $shiftTickets array */
/* @var $date string */

$this->breadcrumbs = array(
    'Engines' => array('engEngines/index'),
    'Review'
);

$clientScript = Yii::app()->clientScript;
$clientScript->registerScriptFile('/js/engShiftTicket/admin.js', CClientScript::POS_END);
$clientScript->registerCssFile('/css/engShiftTicket/admin.css');

?>

<h2>Review Shift Tickets</h2>

<div class="marginTop10">
    <a id="shift-ticket-filter-button" href="#">Shift Ticket Filter</a>
    <span class="paddingRight10 paddingLeft10">| </span>
    <a id="shift-ticket-todays" href="<?php echo $this->createUrl('engShiftTicket/shiftTicketTable'); ?>" data-date="<?php echo date('Y-m-d'); ?>">Today's Shift Tickets</a>
    <div class="pull-right">
        <a href="<?php echo $this->createUrl('engShiftTicketStatusType/admin'); ?>">Manage Status Types</a>
        <span class="paddingRight10 paddingLeft10">| </span>
        <a href="<?php echo $this->createUrl('engShiftTicketActivityType/admin'); ?>">Manage Activity Types</a>
    </div>
</div>

<div class="search-form" id="shift-ticket-filter-form" style="display: none;">
    <?php echo $this->renderPartial('_shift_ticket_search', array(
        'filterData' => $filterData
    )); ?>
</div>

<div class="row-fluid" style="margin-bottom: 40px;">
    <?php echo $this->renderPartial('_shift_ticket_table', array(
        'shiftTickets' => $shiftTickets,
        'date' => $date
    )); ?>
</div>

<div class="row-fluid marginTop20">
    <a class="btn btn-primary" id="shiftTicketGridColumnsToggle" href="#">Columns</a>
    <a class="btn btn-primary" id="shiftTicketGridAdvSearchToggle" href="#">Advanced Search</a>
    <a class="btn btn-primary" href="<?php echo $this->createUrl('engShiftTicket/admin', array('shiftTicketGridReset' => 1)); ?>">Reset</a>
</div>

<?php

echo $this->renderPartial('_grid_columns_to_show', array('shiftTicketGridColumnsToShow' => $shiftTicketGridColumnsToShow, 'shiftTicketGridPageSize' => $shiftTicketGridPageSize,'shiftTicketGridSubColumnsToShow' => $shiftTicketGridSubColumnsToShow));
echo $this->renderPartial('_grid_advanced_search', array('gridShiftTickets' => $gridShiftTickets, 'shiftTicketGridAdvSearch' => $shiftTicketGridAdvSearch));

$shiftTicketGridColumnArray = array(
    array(
        'class' => 'bootstrap.widgets.TbButtonColumn',
        'template' => '{review}{view}',
        'buttons' => array(
            'review' => array(
                'label' => '<i class="icon-pencil"></i>',
                'url' => 'array("engShiftTicket/review", "id" => $data->id)',
                'options' => array('title' => 'Review')
            ),
            'view' => array(
                'url' => 'array("engShiftTicket/viewShiftTicketPDF", "ids" => json_encode(array($data->id)))',
                'options' => array('target' => '_blank')
            )
        )
    )
);

foreach ($shiftTicketGridColumnsToShow as $columnToShow)
{
    if ($columnToShow === 'date')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            //'filter' => false
        );
    }
    else if ($columnToShow === 'completedStatuses')
    {
        $shiftTicketStatuses = EngShiftTicketStatusType::model()->getAllActiveStatuses();
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'filter' => CHtml::activeDropDownList($gridShiftTickets, $columnToShow, CHtml::listData($shiftTicketStatuses, 'id', 'type'), array('empty' => '')),
            'value' => 'implode("<br>",$data->getCompletedStatuses())',
            'type' => 'html'
        );
    }
    else if ($columnToShow === 'submitted_by_user_id')
    {
        $users = User::model()->findAll('client_id IS NULL AND active = 1 AND (wds_staff = 1 OR alliance_id IS NOT NULL) ORDER BY name ASC');
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getSubmittedBy()',
            'filter' => CHtml::activeDropDownList($gridShiftTickets, $columnToShow, CHtml::listData($users, 'id', 'name'), array('empty' => '')),
        );
    }
    else if ($columnToShow === 'user_id')
    {
        $users = User::model()->findAll('client_id IS NULL AND active = 1 AND (wds_staff = 1 OR alliance_id IS NOT NULL) ORDER BY name ASC');
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getLastUpdatedBy()',
            'filter' => CHtml::activeDropDownList($gridShiftTickets, $columnToShow, CHtml::listData($users, 'id', 'name'), array('empty' => '')),
        );
    }
    else if ($columnToShow === 'eng_schedule_assignment')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'filter' => EngScheduling::model()->getEngineAssignments(),
        );
    }
    else if ($columnToShow === 'fire_name')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
        );
    }
    else if ($columnToShow === 'eng_schedule_ro')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'filter' => CHtml::activeNumberField($gridShiftTickets,'eng_schedule_ro',array('min'=>0, 'max' => 999999999999999))
        );
    }
    else if ($columnToShow === 'eng_schedule_clients')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getClientsHTMLList()',
            'type' => 'html',
            'filter' => CHtml::activeDropDownList($gridShiftTickets, 'eng_schedule_clients', CHtml::listData(EngScheduling::model()->getAvailibleFireClients(), 'id', 'name'), array('prompt' => ''))
        );
    }
    else if ($columnToShow === 'eng_schedule_crew')
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'id,first_name,last_name';
        $criteria->order = 'last_name asc';
        $crewDDOptions = CHtml::listData(EngCrewManagement::model()->findAll($criteria),'id', function($data) {
            return $data->last_name . ', ' . $data->first_name;
        });
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getCrewHTMLList()',
            'type' => 'html',
            'filter' => CHtml::activeDropDownList($gridShiftTickets, 'eng_schedule_crew', $crewDDOptions, array('prompt' => '')),
        );
    }
    else if ($columnToShow === 'eng_engine_name')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'filter' => CHtml::activeDropDownList($gridShiftTickets, $columnToShow, CHtml::listData(EngScheduling::model()->getAvailibleEngines(), 'engine_name', 'engine_name'), array('prompt' => ''))
        );
    }
    else if ($columnToShow === 'totalActivityTime')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getTotalActivityTime("'.$activityTypes.'")',
            'filter' => false
        );
    }
    else if ($columnToShow === 'totalMiles')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getTotalMiles()',
            'filter' => false
        );
    }
    else if ($columnToShow === 'activities')
    {
        $shiftTicketGridColumnArray[] = array(
            'name' => $columnToShow,
            'value' => '$data->getActivitiesHTMLList()',
            'type' => 'raw',
            'filter' => false
        );
    }
    else
    {
        $shiftTicketGridColumnArray[array_search($columnToShow,$shiftTicketGridColumnOrder)] = $columnToShow;
    }
}

$shiftTicketGridDataProvider = $gridShiftTickets->search($shiftTicketGridAdvSearch, $shiftTicketGridPageSize, $shiftTicketGridSort);

?>

<div class="row-fluid">
    <div class="table-responsive">
        <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
            'id' => 'shiftTicketGrid',
            'type' => 'striped bordered',
            'fixedHeader' => false,
            'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
            'dataProvider' => $shiftTicketGridDataProvider,
            'filter' => $gridShiftTickets,
            'columns' => $shiftTicketGridColumnArray
        )); ?>
    </div>
</div>

<?php

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id' => 'modal-container',
    'options' => array(
        'title' => 'Comment',
        'autoOpen' => false,
        'closeText' => false,
        'modal' => true,
        'buttons' => array(
            array(
                'text' => 'Close',
                'click' => new CJavaScriptExpression('function() { $(this).dialog("close"); }'),
            )
        ),
        'show' => array(
            'effect' => 'fadeIn',
            'duration' => 300,
            'direction' => 'up'
        ),
        'hide' => array(
            'effect' => 'fadeOut',
            'duration' => 300
        ),
        'width' => 500,
        'draggable' => true
    )
));

echo CHtml::tag('div', array('id' => 'modal-content'), true);

$this->endWidget('zii.widgets.jui.CJuiDialog');
