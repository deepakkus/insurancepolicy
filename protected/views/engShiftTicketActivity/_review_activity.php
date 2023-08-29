<?php

/* @var $this EngShiftTicketActivityController */
/* @var $activity EngShiftTicketActivity */

$clientScript = Yii::app()->clientScript;
$clientScript->registerCssFile('/css/engShiftTicket/activity.css');

$form = $this->beginWidget('WDSActiveForm', array(
    'id' => 'shift-ticket-modal-form',
    'enableAjaxValidation' => true,
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    ),
    'clientOptions' => array(
        'validateOnSubmit' => true,
        'afterValidate' => new CJavaScriptExpression('function(form, data, hasError) {
            if (!hasError) {
                $.ajax({
                    "type": "POST",
                    "url": form.attr("action"),
                    "data": form.serialize(),
                    "success": function (data) {
                        var data = JSON.parse(data);
                        if (data.success === true) {
                           // Close jui dialog
                            $("#modal-container").dialog("close");
                            // Update form
                            $("#modal-container").find("#modal-content").html("");
                            // Reload Activities list
                            $.get("' . $this->createUrl('engShiftTicketActivity/renderReviewActivities', array('shiftTicketId' => $activity->eng_shift_ticket_id)) . '", function(data) {
                                $("#review-activities-container").html(data);
                                $(".alert-activities").fadeIn().delay(2000).fadeOut();
                            });
                        } else {
                            alert("Something went wrong");
                            console.log("Shift ticket activity saving error: " + data.error);
                        }
                    }
                });
            }
			return false;
        }')

    )
));

//Type Definition Tool Tip special CSS (to allow for HTML in the tooltip popup)
//This needs to be inline because it is loaded dynamically
echo CHtml::css('
/* Styles for the add/update ST activity modal popup */
.hover {
    top: 50px;
    left: 50px;
    width: 10px;
}

.tooltip {
    display: none;
    background-color: black;
    color: white;
    border-radius: 5px;
    opacity: 0;
    position: absolute;
    -webkit-transition: opacity 0.5s;
    -moz-transition: opacity 0.5s;
    -ms-transition: opacity 0.5s;
    -o-transition: opacity 0.5s;
    transition: opacity 0.5s;
}

.hover:hover .tooltip {
    opacity: 1;
    display: inline;
    width: 300px;
}
');

echo '<div class="row">';
// Activity Types Dropdown
echo '  <div class="span4">';
echo      $form->dropDownListRow($activity,
                'eng_shift_ticket_activity_type_id',
                CHtml::listData(EngShiftTicketActivityType::getAllTypes(), 'id', 'type'),
                array(
                    'prompt' => 'Select a type'
                )
          );
echo '  </div>';
// Activity Types Definitions ToolTip
echo '  <div class="span2" style="margin-left:35px">';
echo '    <div class="hover">
            <i class="icon icon-info-sign" id="defination-ico"></i>
            <div class="tooltip">'.EngShiftTicketActivityType::model()->getTypeDefList().'</div>
          </div>';
//echo '    <a data-toggle="tooltip" data-placement="right" data-animation="true" data-html="true" title="'.htmlentities(EngShiftTicketActivityType::model()->getTypeDefList()).'"><i class="icon icon-info-sign" id="defination-ico"></i></a>';
echo '  </div>';
echo '</div>';

//get policyholder visits for shiftTicket user and date
$shiftTicket = EngShiftTicket::model()->findByPk($activity->eng_shift_ticket_id);
$resPhVisits = $shiftTicket->getAvailiblePhVisitsList();

echo $form->dropDownListRow($activity, 'res_ph_visit_id', CHtml::listData($resPhVisits, 'id', function($data) {
    return $data->memberLastName.', '.$data->property->address_line_1.', '.date('H:m',strtotime($data->date_action));
}), array('prompt' => 'Select a PolicyHolder Visit'));

//script to hide/show phv dropdown and other functionality within it. Cant register it like normal because this view is loaded to a modal dynamically
echo CHtml::script('

$("#' . CHtml::activeId($activity, 'eng_shift_ticket_activity_type_id') . '").change(function () {
	if((($("#' . CHtml::activeId($activity, 'eng_shift_ticket_activity_type_id') . ' option:selected").text()).trim() == "Break"))
	{
		$("#' . CHtml::activeId($activity, 'billable') . '").prop("checked", false);
	}
	else
	{
		$("#' . CHtml::activeId($activity, 'billable') . '").prop("checked", true);
	}

	if($("#' . CHtml::activeId($activity, 'eng_shift_ticket_activity_type_id') . '").val() == 4)
	{
		$("#' . CHtml::activeId($activity, 'res_ph_visit_id') . '").parent().parent().show();
	}
	else
	{
		$("#' . CHtml::activeId($activity, 'res_ph_visit_id') . '").parent().parent().hide();
	}
	if((($("#' . CHtml::activeId($activity, 'eng_shift_ticket_activity_type_id') . ' option:selected").text()).trim() == "MOB/DEMOB"))
	{
		$(".tracking_location_activity").show();
		$(".tracking_location_activity_end").show();
	}
	else
	{
		$(".tracking_location_activity").hide();
		$(".tracking_location_activity_end").hide();
	}
	return false;
});

//also do it on initial load
if($("#' . CHtml::activeId($activity, 'eng_shift_ticket_activity_type_id') . '").val() == 4)
{
	$("#' . CHtml::activeId($activity, 'res_ph_visit_id') . '").parent().parent().show();
}
else
{
	$("#' . CHtml::activeId($activity, 'res_ph_visit_id') . '").parent().parent().hide();
}

if((($("#' . CHtml::activeId($activity, 'eng_shift_ticket_activity_type_id') . ' option:selected").text()).trim() == "MOB/DEMOB"))
{
	$(".tracking_location_activity").show();
	$(".tracking_location_activity").show();
}
else
{
	$(".tracking_location_activity").hide();
	$(".tracking_location_activity").hide();
}

');

echo '<div class="tracking_location_activity">';
echo $form->label($activity, 'Where to/from?', array('class'=>'activity_location'));
echo $form->textFieldRow($activity, 'tracking_location');
echo $form->textFieldRow($activity, 'tracking_location_end');
echo '</div>';

echo $form->timepickerRow($activity, 'start_time', array(
    'prepend' => '<i class="icon-time"></i>',
    'class' => 'input-small',
    'options' => array(
        'showMeridian' => true,
        'defaultTime' => false
    )
));

echo $form->timepickerRow($activity, 'end_time', array(
    'prepend' => '<i class="icon-time"></i>',
    'class' => 'input-small',
    'options' => array(
        'showMeridian' => true,
        'defaultTime' => false
    )
));

echo CHtml::script('

$("#' . CHtml::activeId($activity, 'start_time') . '").timepicker({"showMeridian":false,"defaultTime":false,"minuteStep": 5})
$("#' . CHtml::activeId($activity, 'end_time')   . '").timepicker({"showMeridian":false,"defaultTime":false,"minuteStep": 5});

');

echo $form->textAreaRow($activity, 'comment', array(
    'rows' => 5,
    'style' => 'width: 300px;'
));

echo $form->checkBoxRow($activity, 'billable');

if($activity->isNewRecord)
{
    CHtml::activeHiddenField($activity, 'eng_shift_ticket_id');
}

echo $form->generateFormJavscriptValidation();

$this->endWidget();
