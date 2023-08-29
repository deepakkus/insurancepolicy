<?php

/* @var $this EngShiftTicketController */
/* @var $shiftTicket EngShiftTicket */

$this->breadcrumbs = array(
    'Engines' => array('engEngines/index'),
    'Shift Tickets' => array('engShiftTicket/admin'),
    'Review',
);

$script = '

    // Load either the shift ticket activies or notes form

    $(document).on("click", ".review-shift-ticket-activity, .review-shift-ticket-note", function() {
        $("#modal-container").find("#modal-content").load(this.href, function() {
            if ($(this).hasClass("review-shift-ticket-activity")) {
                $("#modal-container").dialog("option", "title", "Shift Ticket Activity");
            } else {
                $("#modal-container").dialog("option", "title", "Shift Ticket Note");
            }
            $("#modal-container").dialog("open");
        }.bind(this));
        return false;
    });

';

$style = '
    .activity_location {
        float:left;
        width: 100%;
        margin-left: 80px;
    }
    table.additional-info td {
        padding: 4px !important;
    }
    table.additional-info td:first-child {
        font-weight: bold;
    }
';

Yii::app()->clientScript->registerScript('shift-ticket-modal-script', $script);
Yii::app()->clientScript->registerCss('shift-ticket-modal-css', $style);

// these are needed ahead of time for the asynchrously render forms in a modal popup
Yii::app()->clientScript->registerCoreScript('yiiactiveform');
Yii::app()->bootstrap->registerAssetCss('bootstrap-timepicker.css');
Yii::app()->bootstrap->registerAssetJs('bootstrap.timepicker.js');

?>

<h2>Review Shift Ticket</h2>

<a href="<?php echo $this->createUrl('engShiftTicket/viewShiftTicketPDF', array('ids' => json_encode(array($shiftTicket->id)))); ?>" target="_blank">View Shift Ticket</a>

<?php

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'shift-ticket-review-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

?>

<div class="row-fluid">
    <div class="span8">

        <?php

        echo $form->textFieldRow($shiftTicket, 'date', array(
            'value' => date('Y-m-d', strtotime($shiftTicket->date)),
            'disabled' => true
        ));

        echo $form->textFieldRow($shiftTicket, 'start_miles');

        echo $form->textFieldRow($shiftTicket, 'end_miles');

        ?>

        <div class="control-group ">
            <div class="controls">
                <p style="font-size: 1.2em;">
                    Total miles: <?php echo strval((int)$shiftTicket->end_miles - (int)$shiftTicket->start_miles); ?>
                </p>
            </div>
        </div>

        <?php

        echo $form->textFieldRow($shiftTicket, 'start_location');

        echo $form->textFieldRow($shiftTicket, 'end_location');

        echo $form->textAreaRow($shiftTicket, 'safety_meeting_comments', array(
            'rows' => 5,
            'style' => 'width: 300px;'
        ));

        echo $form->textAreaRow($shiftTicket, 'equipment_check', array(
            'rows' => 5,
            'style' => 'width: 300px;'
        ));

        echo '<div id="review-activities-container">';

        echo $this->renderPartial('//engShiftTicketActivity/_review_activities', array(
            'shiftTicketActivities' => $shiftTicketActivities,
            'shiftTicket' => $shiftTicket
        ));

        echo '</div>';

        echo '<div id="review-notes-container">';

        echo $this->renderPartial('//engShiftTicketNotes/_review_notes', array(
            'shiftTicketNotes' => $shiftTicketNotes,
            'shiftTicket' => $shiftTicket
        ));

        echo '</div>';

        ?>
    </div>
    <div class="span4">

        <?php

        $completedStatusIDs = $shiftTicket->getCompletedStatuses(true);
        $activeStatuses = EngShiftTicketStatusType::getAllActiveStatuses();

        ?>

        <div class="control-group">
            <p><strong>Reviewed</strong></p>
            <?php
            
            foreach($activeStatuses as $status)
            {
                echo '<label class="checkbox">
                    <input value="' . $status->id . '" type="checkbox" name="CompletedStatuses[status][]" ' . (in_array($status->id, $completedStatusIDs) ? 'checked' : null) . '>
                    ' . $status->type . '
                </label>';
            }

            ?>
            <!-- This hidden field forces a selection, even if nothing is selected -->
            <input type="hidden" value="-1" name="CompletedStatuses[status][]" />
        </div>

        <div>
            <?php //echo $this->renderPartial('_shift_ticket_history', array('shiftTicket' => $shiftTicket)); ?>
        </div>

        <p><strong>Additional Information</strong></p>

        <?php

        $clientNames = isset($shiftTicket->engScheduling->engineClient) ? array_map(function($engineClient) { return $engineClient->client_name; }, $shiftTicket->engScheduling->engineClient) : array();
        $assignment = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->assignment : '';
        $fireName = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->fire_name : '';
        $engineName = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->engine_name : '';
        $crew = isset($shiftTicket->engScheduling) ? implode(', ', $shiftTicket->engScheduling->crew_names) : '';
        $resourceOrder = isset($shiftTicket->engScheduling) ? $shiftTicket->engScheduling->resource_order_num : '';
        $alliance = isset($shiftTicket->engScheduling->engine->alliancepartner) ? $shiftTicket->engScheduling->engine->alliancepartner->name : '';

        ?>

        <table class="additional-info">
            <tr>
                <td>Company: </td>
                <td><?php echo $alliance; ?></td>
            </tr>
            <tr>
                <td>Engine: </td>
                <td><?php echo $engineName; ?></td>
            </tr>
            <tr>
                <td>Crew: </td>
                <td><?php echo $crew; ?></td>
            </tr>
            <tr>
                <td>Clients: </td>
                <td><?php echo join(',', $clientNames); ?></td>
            </tr>
            <tr>
                <td>Assignment: </td>
                <td><?php echo $assignment; ?></td>
            </tr>
            <?php if ($assignment === 'Response'): ?>
            <tr>
                <td>Fire: </td>
                <td><?php echo $fireName; ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>RO: </td>
                <td><?php echo $resourceOrder; ?></td>
            </tr>
        </table>

    </div>
</div>

<div class="marginTop20">
    <?php echo CHtml::submitButton('Save', array('class' => 'submit')); ?>
    <span class="paddingLeft10">
        <?php echo CHtml::link('Cancel', array('engShiftTicket/admin')); ?>
    </span>
</div>

<?php

$this->endWidget();

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id' => 'modal-container',
    'options' => array(
        'title' => '',
        'autoOpen' => false,
        'closeText' => false,
        'modal' => true,
        'buttons' => array(
            array(
                'text' => 'Save',
                'click' => new CJavaScriptExpression('function() { $("#shift-ticket-modal-form").submit(); }')
            ),
            array(
                'text' => 'Close',
                'click' => new CJavaScriptExpression('function() { $(this).dialog("close"); }'),
            )
        ),
        'show' => array(
            'effect' => 'drop',
            'duration' => 300,
            'direction' => 'up'
        ),
        'hide' => array(
            'effect' => 'fadeOut',
            'duration' => 300
        ),
        'width' => 800,
        'resizable' => false,
        'draggable' => true
    )
));

echo CHtml::tag('div', array('id' => 'modal-content'), true);

$this->endWidget('zii.widgets.jui.CJuiDialog');