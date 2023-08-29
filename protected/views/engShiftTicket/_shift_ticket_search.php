<?php

/* @var $this EngShiftTicketController */
/* @var $filterData array|null */

?>

<div style="margin: 10px;">

    <div class="clearfix">
        <div class="floatLeft marginRight20">
            <h4>Clients</h4>
            <div class="control-group">
                <?php echo CHtml::checkBoxList("ShiftTicketFilter[clients]", ($filterData ? $filterData->clients : null), CHtml::listData(EngScheduling::model()->getAvailibleFireClients(), 'id', 'name'), array(
                    'class' => 'shift-tickets-clients-checkbox',
                    'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                    'labelOptions' => array('class' => 'checkbox'),
                    'separator' => ''
                )); ?>
            </div>
        </div>

        <div class="floatLeft marginRight20">
            <h4>Fires</h4>
            <div class="control-group">
                <?php echo CHtml::checkBoxList("ShiftTicketFilter[fires]", ($filterData ? $filterData->fires : null), CHtml::listData(EngScheduling::model()->getAvailibleFires(), 'Fire_ID', 'Name'), array(
                    'class' => 'shift-tickets-fires-checkbox',
                    'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                    'labelOptions' => array('class' => 'checkbox'),
                    'separator' => ''
                )); ?>
            </div>
        </div>

        <div class="floatLeft marginRight20">
            <h4>Submitted</h4>
            <div class="control-group">
                <?php echo CHtml::checkBoxList("ShiftTicketFilter[submitted]", ($filterData ? $filterData->submitted : null), array('1' => 'Yes', '0' => 'No'), array(
                    'class' => 'shift-tickets-submitted-checkbox',
                    'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                    'labelOptions' => array('class' => 'checkbox'),
                    'separator' => ''
                )); ?>
            </div>
        </div>

        <div class="floatLeft">
            <h4>Completed</h4>
            <div class="control-group"><?php echo CHtml::checkBoxList("ShiftTicketFilter[completed]", ($filterData ? $filterData->completed : null), array('1' => 'Yes', '0' => 'No'), array(
                    'class' => 'shift-tickets-completed-checkbox',
                    'template' => '{beginLabel}{input}{labelTitle}{endLabel}',
                    'labelOptions' => array('class' => 'checkbox'),
                    'separator' => ''
                )); ?>
            </div>
        </div>


    </div>

    <div class="paddingTop10">
        <?php echo CHtml::link('Clear Selections', '#', array('class' => 'clear-checked')); ?>
        <div class="marginTop20">
            <?php echo CHtml::button('Update View', array('name' => 'filter-submit', 'class' => 'submitButton')); ?>
            <?php echo CHtml::button('Close', array('id' => 'close-shift-ticket-filter', 'class' => 'submitButton')); ?>
        </div>
    </div>

</div>